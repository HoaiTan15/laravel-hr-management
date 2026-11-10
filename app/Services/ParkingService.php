<?php

namespace App\Services;

use App\Enums\EmploymentStatus;
use App\Models\Employee;
use App\Models\ParkingMonthlyCost;
use App\Models\ParkingPermit;
use App\Models\ParkingRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ParkingService
{
    public function submit(Employee $employee, array $data): ParkingRequest
    {
        return DB::transaction(function () use ($employee, $data): ParkingRequest {
            $employee = Employee::lockForUpdate()->findOrFail($employee->id);
            $this->assertEligible($employee);
            $this->ensure(! ParkingRequest::where('pending_employee_id', $employee->id)->exists(), 'Bạn đã có một yêu cầu đang chờ HR xử lý.');
            $permit = ParkingPermit::where('active_employee_id', $employee->id)->first();
            $this->ensure($data['type'] === 'register' ? ! $permit : (bool) $permit, 'Trạng thái xe đã thay đổi. Hãy tải lại trang và chọn yêu cầu phù hợp.');
            $plate = $data['type'] === 'stop' ? $permit->plate : $this->normalizePlate($data['plate']);
            if ($data['type'] !== 'stop') {
                $this->ensure(! ParkingPermit::where('active_plate', $plate)->exists(), 'Biển số này đã có đăng ký đang hoạt động.');
            }

            return ParkingRequest::create([
                'employee_id' => $employee->id, 'pending_employee_id' => $employee->id,
                'permit_id' => $permit?->id, 'type' => $data['type'], 'plate' => $plate,
                'vehicle_type' => $data['type'] === 'stop' ? $permit->vehicle_type : $data['vehicle_type'],
                'vehicle_description' => $data['type'] === 'stop' ? $permit->vehicle_description : $data['vehicle_description'],
                'reason' => $data['reason'], 'status' => 'pending',
            ]);
        });
    }

    public function review(ParkingRequest $record, User $reviewer, array $data): void
    {
        try {
            DB::transaction(function () use ($record, $reviewer, $data): void {
                $employee = Employee::lockForUpdate()->findOrFail($record->employee_id);
                $record = ParkingRequest::lockForUpdate()->findOrFail($record->id);
                $this->ensure($record->status === 'pending', 'Yêu cầu này đã được xử lý.');
                if ($data['status'] === 'approved') {
                    $this->assertEligible($employee);
                    $permit = ParkingPermit::where('active_employee_id', $employee->id)->first();
                    $this->ensure($record->type === 'register' ? ! $permit : $permit?->id === $record->permit_id, 'Xe hiện tại đã thay đổi. Hãy từ chối yêu cầu cũ.');
                    if ($record->type !== 'stop') {
                        $this->ensure(! ParkingPermit::where('active_plate', $record->plate)->exists(), 'Biển số này vừa được đăng ký bởi người khác.');
                    }
                    if ($permit) {
                        $this->closePermit($permit, $record->reason);
                    }
                    if ($record->type !== 'stop') {
                        $newPermit = ParkingPermit::create([
                            'employee_id' => $employee->id, 'active_employee_id' => $employee->id,
                            'plate' => $record->plate, 'active_plate' => $record->plate,
                            'vehicle_type' => $record->vehicle_type, 'vehicle_description' => $record->vehicle_description,
                            'monthly_cost' => $data['monthly_cost'], 'starts_at' => now(),
                        ]);
                        $this->recordMonth($newPermit, CarbonImmutable::now());
                    }
                }
                $record->update([
                    'status' => $data['status'], 'pending_employee_id' => null,
                    'reviewed_by' => $reviewer->id, 'reviewed_at' => now(), 'review_note' => $data['review_note'] ?? null,
                ]);
            });
        } catch (QueryException $error) {
            if (in_array((string) $error->getCode(), ['23000', '23505'], true)) {
                throw ValidationException::withMessages(['parking' => 'Xe hoặc nhân viên vừa có đăng ký khác. Hãy tải lại trang.']);
            }
            throw $error;
        }
    }

    public function cancel(ParkingRequest $record): void
    {
        $updated = ParkingRequest::whereKey($record->id)->where('status', 'pending')->update(['status' => 'cancelled', 'pending_employee_id' => null]);
        $this->ensure((bool) $updated, 'Chỉ được hủy yêu cầu đang chờ duyệt.');
    }

    public function endForEmployee(Employee $employee, User $reviewer, string $reason, ?int $expectedPermitId = null): void
    {
        DB::transaction(function () use ($employee, $reviewer, $reason, $expectedPermitId): void {
            Employee::lockForUpdate()->findOrFail($employee->id);
            $permit = ParkingPermit::where('active_employee_id', $employee->id)->first();
            $this->ensure($expectedPermitId === null || $permit?->id === $expectedPermitId, 'Xe hiện tại đã thay đổi. Hãy tải lại trang trước khi kết thúc quyền gửi xe.');
            ParkingRequest::where('pending_employee_id', $employee->id)->update([
                'status' => 'rejected', 'pending_employee_id' => null, 'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(), 'review_note' => $reason,
            ]);
            if ($permit) {
                $this->closePermit($permit, $reason);
                ParkingRequest::create([
                    'employee_id' => $employee->id, 'permit_id' => $permit->id, 'type' => 'stop',
                    'plate' => $permit->plate, 'vehicle_type' => $permit->vehicle_type,
                    'vehicle_description' => $permit->vehicle_description, 'reason' => $reason,
                    'status' => 'approved', 'reviewed_by' => $reviewer->id, 'reviewed_at' => now(), 'review_note' => $reason,
                ]);
            }
        });
    }

    public function prepareMonth(int $year, int $month): void
    {
        $start = CarbonImmutable::create($year, $month, 1)->startOfDay();
        $this->ensure($start <= CarbonImmutable::now(), 'Chỉ xem chi phí của tháng hiện tại hoặc trước đó.');
        ParkingPermit::where('starts_at', '<=', $start->endOfMonth())
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $start))
            ->orderBy('starts_at')->orderBy('id')->each(fn (ParkingPermit $permit) => $this->recordMonth($permit, $start));
    }

    private function recordMonth(ParkingPermit $permit, CarbonImmutable $month): void
    {
        ParkingMonthlyCost::firstOrCreate(
            ['employee_id' => $permit->employee_id, 'year' => $month->year, 'month' => $month->month],
            ['permit_id' => $permit->id, 'amount' => $permit->monthly_cost],
        );
    }

    private function closePermit(ParkingPermit $permit, string $reason): void
    {
        $this->recordMonth($permit, CarbonImmutable::now());
        $permit->update(['active_employee_id' => null, 'active_plate' => null, 'ends_at' => now(), 'end_reason' => $reason]);
    }

    private function assertEligible(Employee $employee): void
    {
        $this->ensure($employee->employment_status === EmploymentStatus::ACTIVE && $employee->user?->is_active, 'Nhân viên phải đang làm việc và có tài khoản hoạt động.');
    }

    private function normalizePlate(string $plate): string
    {
        $plate = preg_replace('/[^A-Z0-9]/', '', strtoupper($plate));
        $this->ensure(strlen($plate) >= 5 && strlen($plate) <= 15, 'Biển số cần từ 5 đến 15 chữ cái hoặc chữ số.');

        return $plate;
    }

    private function ensure(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['parking' => $message]);
        }
    }
}
