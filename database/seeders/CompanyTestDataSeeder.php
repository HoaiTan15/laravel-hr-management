<?php

namespace Database\Seeders;

use App\Models\AllowanceRule;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\MonthlySalary;
use App\Models\ParkingMonthlyCost;
use App\Models\ParkingPermit;
use App\Models\ParkingRequest;
use App\Models\PenaltyRule;
use App\Models\PersonnelProcess;
use App\Models\Position;
use App\Models\Request as EmployeeRequest;
use App\Models\Task;
use App\Models\User;
use App\Services\SalaryService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class CompanyTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Bộ dữ liệu TEST chỉ được chạy trong môi trường local/testing.');
        }

        DB::transaction(function (): void {
            $departments = collect(['Nhân sự', 'Kỹ thuật', 'Kế toán', 'Phòng trống'])->mapWithKeys(fn (string $name): array => [$name => Department::firstOrCreate(['name' => ''.$name], ['description' => 'Dữ liệu giả lập để thử chức năng.', 'is_active' => true])]);
            $position = Position::firstOrCreate(['name' => 'Chuyên viên'], ['description' => 'Chức vụ kiểm thử', 'is_active' => true]);
            Position::firstOrCreate(['name' => 'Chức vụ trống'], ['is_active' => true]);
            $password = Hash::make('10giokem7');
            $admin = User::firstOrCreate(['email' => 'test.admin@example.test'], ['name' => 'Quản trị viên', 'role' => 'admin', 'password' => $password, 'is_active' => true]);
            $employees = [];
            foreach ([
                ['hr', 'Nhân sự', 'Nhân sự', 'hr', true],
                ['employee', 'Nguyễn An', 'Kỹ thuật', 'employee', true],
                ['employee2', 'Trần Bình', 'Kỹ thuật', 'employee', true],
                ['employee3', 'Lê Chi', 'Kế toán', 'employee', true],
                ['new', 'Nhân viên mới', 'Kế toán', 'employee', true],
                ['retired', 'Nhân viên đã nghỉ', 'Kỹ thuật', 'employee', false],
                ['locked', 'Tài khoản bị khóa', 'Kế toán', 'employee', false],
            ] as $index => [$key, $name, $department, $role, $active]) {
                $user = User::firstOrCreate(['email' => 'test.'.$key.'@example.test'], ['name' => ''.$name, 'role' => $role, 'password' => $password, 'is_active' => $active]);
                $employees[$key] = Employee::firstOrCreate(['employee_code' => 'TEST-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)], [
                    'user_id' => $user->id, 'full_name' => ''.$name, 'department_id' => $departments[$department]->id,
                    'position_id' => $position->id, 'email' => $user->email, 'gender' => $index % 2 ? 'male' : 'female',
                    'date_of_birth' => '1998-01-15', 'address' => 'Địa chỉ giả lập phục vụ kiểm thử',
                    'hire_date' => today()->subYear()->startOfYear(), 'employment_status' => $key === 'retired' ? 'terminated' : 'active',
                    'termination_date' => $key === 'retired' ? today()->subMonth()->endOfMonth() : null,
                    'termination_reason' => $key === 'retired' ? 'Hoàn tất bàn giao' : null,
                ]);
            }
            $hr = $employees['hr']->user;
            $current = CarbonImmutable::today()->startOfMonth();
            $previous = $current->subMonth();
            $this->rules($departments->except('Phòng trống')->pluck('id')->all(), $employees['employee']);
            foreach (['hr', 'employee', 'employee2', 'employee3'] as $offset => $key) {
                $employee = $employees[$key];
                foreach ([$previous, $current] as $period) {
                    $this->attendanceAndLeave($employee, $hr, $period, $offset);
                }
                if ($key !== 'employee2') {
                    $now = now();
                    $employee->attendances()->whereDate('work_date', today())->firstOrCreate([], ['work_date' => today(),
                        'check_in_at' => $now->copy()->subMinutes(60), 'check_out_at' => $now->copy()->subMinutes(1),
                        'adjustment_reason' => 'Đã chấm công để truy cập ngay các chức năng.', 'adjusted_by' => $hr->id,
                    ]);
                } else {
                    $employee->attendances()->whereDate('work_date', today())->firstOrCreate([], ['work_date' => today(), 'check_in_at' => now()->subMinutes(60), 'adjustment_reason' => 'Đang làm việc, có thể thử check-out.', 'adjusted_by' => $hr->id]);
                }
                $this->tasksAndRequests($employee, $hr, $admin);
                foreach ([$previous, $current] as $period) {
                    if (MonthlySalary::where('employee_id', $employee->id)->where('year', $period->year)->where('month', $period->month)->exists()) {
                        continue;
                    }
                    $salary = app(SalaryService::class)->calculate($employee, $period->year, $period->month);
                    if ($key === 'employee') {
                        $salary->update(['manual_penalties' => ['work_rules' => ['count' => 1, 'reason' => 'Vi phạm nội quy đã xác nhận']]]);
                        $salary = app(SalaryService::class)->calculate($employee, $period->year, $period->month);
                    }
                    if ($period->isSameMonth($previous) && in_array($key, ['hr', 'employee3'], true)) {
                        $salary->update(['status' => $key === 'hr' ? 'confirmed' : 'paid']);
                    }
                }
            }
            $this->parking($employees, $hr, $previous);
            foreach (['draft', 'in_progress', 'completed', 'rejected'] as $index => $status) {
                foreach (['recruitment', 'termination'] as $type) {
                    $reason = ''.$type.' / '.$status;
                    PersonnelProcess::firstOrCreate(['reason' => $reason], [
                        'employee_id' => $type === 'termination' && $status === 'completed' ? $employees['retired']->id : ($type === 'recruitment' && $status !== 'completed' ? null : $employees['employee3']->id),
                        'created_by' => $hr->id, 'type' => $type, 'status' => $status,
                        'effective_date' => $status === 'completed' ? $previous->endOfMonth() : $current->addMonth()->day(15),
                        'completed_by' => $status === 'completed' ? $hr->id : null,
                        'completed_at' => $status === 'completed' ? $previous->endOfMonth() : null,
                    ]);
                }
            }
            $future = CarbonImmutable::today()->addWeek()->nextWeekday();
            foreach (['pending', 'rejected'] as $index => $status) {
                $date = $future->addWeekdays($index * 2);
                LeaveRequest::firstOrCreate(['employee_id' => $employees['employee']->id, 'reason' => 'Nghỉ phép '.$status], [
                    'requested_by' => $employees['employee']->user_id, 'start_date' => $date, 'end_date' => $date,
                    'duration_type' => 'half_day', 'half_day_period' => 'afternoon', 'requested_days' => 0.5, 'status' => $status,
                    'reviewed_by' => $status === 'rejected' ? $hr->id : null, 'reviewed_at' => $status === 'rejected' ? now() : null,
                ]);
            }
        });

        $this->command?->info('Đã bổ sung dữ liệu . Mật khẩu tài khoản test.*@example.test: 10giokem7. Thử bảng lương tháng hiện tại và tháng trước.');
    }

    /** @param list<int> $departmentIds */
    private function rules(array $departmentIds, Employee $employee): void
    {
        foreach ([['parking', 200000, 'monthly'], ['meal', 35000, 'working_day'], ['transport', 300000, 'monthly'], ['phone', 150000, 'monthly'], ['responsibility', 500000, 'monthly'], ['other', 100000, 'monthly']] as [$type, $amount, $basis]) {
            $rule = AllowanceRule::firstOrCreate(['name' => ''.AllowanceRule::TYPES[$type]], ['type' => $type, 'amount' => $amount, 'calculation_basis' => $basis, 'scope' => 'departments', 'is_active' => $type !== 'other', 'description' => 'Áp dụng cho các phòng ban đã chọn.']);
            $rule->departments()->syncWithoutDetaching($departmentIds);
        }
        $own = AllowanceRule::firstOrCreate(['name' => 'Tiền ăn riêng Nguyễn An'], ['type' => 'meal', 'amount' => 40000, 'calculation_basis' => 'working_day', 'scope' => 'employees', 'is_active' => true]);
        $own->employees()->syncWithoutDetaching([$employee->id]);
        foreach (['late' => 50000, 'early_departure' => 30000, 'missing_checkout' => 20000, 'unexcused_absence' => 100000, 'work_rules' => 50000, 'other' => 25000] as $type => $amount) {
            $rule = PenaltyRule::firstOrCreate(['name' => ''.PenaltyRule::TYPES[$type]], ['type' => $type, 'amount' => $amount, 'scope' => 'departments', 'is_active' => true, 'description' => 'Áp dụng cho các phòng ban đã chọn.']);
            $rule->departments()->syncWithoutDetaching($departmentIds);
        }
    }

    private function attendanceAndLeave(Employee $employee, User $hr, CarbonImmutable $period, int $offset): void
    {
        $days = [];
        for ($date = $period; $date->lt($period->addMonth()) && $date->lt(CarbonImmutable::today()); $date = $date->addDay()) {
            if ($date->isWeekday()) {
                $days[] = $date;
            }
        }
        foreach ($days as $index => $date) {
            if (in_array($index, [4, 8], true)) {
                LeaveRequest::firstOrCreate(['employee_id' => $employee->id, 'reason' => ''.($index === 4 ? 'Nghỉ có lương ' : 'Nghỉ không lương ').$period->format('Y-m')], [
                    'requested_by' => $employee->user_id, 'start_date' => $date, 'end_date' => $date, 'duration_type' => 'full_day',
                    'requested_days' => 1, 'paid_leave_days' => $index === 4 ? 1 : 0, 'unpaid_leave_days' => $index === 8 ? 1 : 0,
                    'status' => 'approved', 'reviewed_by' => $hr->id, 'reviewed_at' => $date->setTime(8, 0),
                    'review_note' => 'Mẫu có lương / không lương để so sánh bảng lương.',
                ]);

                continue;
            }
            $scenario = ($index + $offset) % 7;
            $employee->attendances()->whereDate('work_date', $date)->firstOrCreate([], ['work_date' => $date,
                'check_in_at' => $date->setTime($scenario === 1 ? 9 : 8, 0),
                'check_out_at' => $scenario === 3 ? null : $date->setTime($scenario === 2 ? 16 : 17, 30),
                'adjusted_by' => $hr->id, 'adjustment_reason' => 'Chấm công giả lập đúng giờ / trễ / về sớm / quên ra.',
            ]);
        }
    }

    private function tasksAndRequests(Employee $employee, User $hr, User $admin): void
    {
        foreach (['pending', 'in_progress', 'completed', 'stopped'] as $index => $status) {
            Task::firstOrCreate(['assigned_to' => $employee->id, 'title' => 'Công việc '.$status], ['created_by' => $hr->id, 'description' => 'Thử xem chi tiết, cập nhật tiến độ và bộ lọc.', 'status' => $status, 'due_at' => now()->addDays($index - 1), 'notes' => 'Dữ liệu giả lập.']);
        }
        foreach (['hardware', 'software', 'account', 'other', 'profile_change'] as $index => $type) {
            foreach (['pending', 'completed', 'rejected'] as $status) {
                $title = ''.$type.' / '.$status;
                if (EmployeeRequest::where('created_by', $employee->user_id)->where('payload->title', $title)->exists()) {
                    continue;
                }
                EmployeeRequest::create(['created_by' => $employee->user_id, 'type' => $type, 'payload' => ['title' => $title, 'content' => 'Nội dung giả lập để thử tạo/sửa/duyệt phiếu.', 'reason' => 'Kiểm thử chức năng', ...($type === 'profile_change' ? ['changes' => ['address' => 'Địa chỉ mới']] : [])], 'status' => $status, 'processed_by' => $status !== 'pending' ? $admin->id : null, 'processed_at' => $status !== 'pending' ? now()->subDay() : null, 'processing_note' => $status !== 'pending' ? 'Kết quả xử lý mẫu.' : null]);
            }
        }
    }

    /** @param array<string, Employee> $employees */
    private function parking(array $employees, User $hr, CarbonImmutable $previous): void
    {
        foreach (['hr', 'employee', 'employee3', 'retired'] as $index => $key) {
            $employee = $employees[$key];
            $closed = $key === 'retired';
            $plate = '59TEST'.(10001 + $index);
            $permit = ParkingPermit::firstOrCreate(['employee_id' => $employee->id, 'plate' => $plate], ['active_employee_id' => $closed ? null : $employee->id, 'active_plate' => $closed ? null : $plate, 'vehicle_type' => 'motorbike', 'vehicle_description' => 'Xe máy giả lập', 'monthly_cost' => 200000, 'starts_at' => $previous, 'ends_at' => $closed ? $previous->endOfMonth() : null, 'end_reason' => $closed ? 'Đã thôi việc' : null]);
            foreach ([$previous, $previous->addMonth()] as $period) {
                if ($closed && $period->gt($previous)) {
                    continue;
                }
                ParkingMonthlyCost::firstOrCreate(['employee_id' => $employee->id, 'year' => $period->year, 'month' => $period->month], ['permit_id' => $permit->id, 'amount' => 200000]);
            }
            ParkingRequest::firstOrCreate(['employee_id' => $employee->id, 'reason' => 'Đăng ký xe đã duyệt'], ['type' => 'register', 'status' => 'approved', 'permit_id' => $permit->id, 'plate' => $plate, 'vehicle_type' => 'motorbike', 'vehicle_description' => 'Xe máy giả lập', 'reviewed_by' => $hr->id, 'reviewed_at' => $previous]);
            if (in_array($key, ['employee', 'employee3'], true) && ! ParkingRequest::where('pending_employee_id', $employee->id)->exists()) {
                $type = $key === 'employee' ? 'change' : 'stop';
                ParkingRequest::firstOrCreate(['employee_id' => $employee->id, 'reason' => 'Yêu cầu '.$type.' chờ duyệt'], ['pending_employee_id' => $employee->id, 'permit_id' => $permit->id, 'type' => $type, 'status' => 'pending', 'plate' => $type === 'change' ? '59TEST99999' : $plate, 'vehicle_type' => 'motorbike', 'vehicle_description' => 'Xe thay đổi']);
            }
        }
        $employee = $employees['employee2'];
        foreach (['pending', 'rejected', 'cancelled'] as $status) {
            if ($status === 'pending' && ParkingRequest::where('pending_employee_id', $employee->id)->exists()) {
                continue;
            }
            ParkingRequest::firstOrCreate(['employee_id' => $employee->id, 'reason' => 'Đăng ký xe '.$status], ['pending_employee_id' => $status === 'pending' ? $employee->id : null, 'type' => 'register', 'status' => $status, 'plate' => '59TEST88888', 'vehicle_type' => 'motorbike', 'vehicle_description' => 'Xe đăng ký mới', 'reviewed_by' => $status === 'rejected' ? $hr->id : null, 'reviewed_at' => $status === 'rejected' ? now() : null, 'review_note' => $status === 'rejected' ? 'Mẫu bị từ chối' : null]);
        }
    }
}
