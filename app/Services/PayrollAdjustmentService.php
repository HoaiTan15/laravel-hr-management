<?php

namespace App\Services;

use App\Enums\LeaveDurationType;
use App\Models\AllowanceRule;
use App\Models\Employee;
use App\Models\PenaltyRule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PayrollAdjustmentService
{
    public const MANUAL_TYPES = ['unexcused_absence' => 'Nghỉ không phép', 'work_rules' => 'Vi phạm nội quy', 'other' => 'Vi phạm khác'];

    public function __construct(private readonly AttendanceService $attendanceService) {}

    /**
     * @param  array<string, array{count: int, reason: string|null}>  $manualPenalties
     * @return array{actual_days: float, allowances: array, penalties: array, warnings: array<string>}
     */
    public function calculate(Employee $employee, CarbonImmutable $start, CarbonImmutable $end, Collection $leaves, array $manualPenalties): array
    {
        $leaveDays = [];
        foreach ($leaves as $leave) {
            for ($date = max($start, CarbonImmutable::parse($leave->start_date)); $date->lte(min($end, CarbonImmutable::parse($leave->end_date))); $date = $date->addDay()) {
                $key = $date->toDateString();
                $leaveDays[$key] = min(1, ($leaveDays[$key] ?? 0) + ($leave->duration_type === LeaveDurationType::HALF_DAY ? 0.5 : 1));
            }
        }
        $actualDays = 0.0;
        $counts = ['late' => 0, 'early_departure' => 0, 'missing_checkout' => 0];
        $periodStart = max($start, CarbonImmutable::parse($employee->hire_date)->startOfDay());
        $periodEnd = min($end, CarbonImmutable::today(), $employee->termination_date ? CarbonImmutable::parse($employee->termination_date) : $end);
        $records = $employee->attendances()->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])->get();
        foreach ($records as $record) {
            $date = $record->work_date;
            if (! $date->isWeekday() || $date->lt($periodStart) || $date->gt($periodEnd)) {
                continue;
            }
            $leaveFraction = $leaveDays[$date->toDateString()] ?? 0;
            if ($record->check_out_at && $record->check_out_at->gt($record->check_in_at) && $record->check_out_at->lte(now())) {
                $actualDays += max(0, 1 - $leaveFraction);
            }
            if ($leaveFraction > 0 || $record->check_in_at->gt(now())) {
                continue;
            }
            if ($this->attendanceService->isLate($record)) {
                $counts['late']++;
            }
            if ($record->check_out_at && $record->check_out_at->gte($record->check_in_at) && $record->check_out_at->lte(now()) && $record->check_out_at->lt($date->copy()->setTimeFromTimeString(config('payroll.workday_end')))) {
                $counts['early_departure']++;
            }
            if (! $record->check_out_at && $date->lt(today())) {
                $counts['missing_checkout']++;
            }
        }
        $allowances = [];
        foreach ($this->applicable(AllowanceRule::class, $employee) as $rule) {
            $quantity = $rule->calculation_basis === 'working_day' ? $actualDays : ($periodStart->lte($periodEnd) ? 1 : 0);
            if ($quantity > 0) {
                $allowances[] = $this->line($rule, $quantity, $rule->calculation_basis === 'monthly' ? 'tháng' : 'ngày công');
            }
        }
        $penalties = [];
        $warnings = [];
        $rules = $this->applicable(PenaltyRule::class, $employee)->keyBy('type');
        foreach (self::MANUAL_TYPES as $type => $label) {
            $counts[$type] = (int) ($manualPenalties[$type]['count'] ?? 0);
            if ($counts[$type] > 0 && ! $rules->has($type)) {
                $warnings[] = $label.': chưa có cấu hình đang bật áp dụng cho nhân viên, chưa tính tiền.';
            }
        }
        foreach ($rules as $type => $rule) {
            if (($counts[$type] ?? 0) > 0) {
                $line = $this->line($rule, $counts[$type], 'lần');
                $line['source'] = isset(self::MANUAL_TYPES[$type]) ? 'HR ghi nhận' : 'Chấm công';
                $line['reason'] = $manualPenalties[$type]['reason'] ?? null;
                $penalties[] = $line;
            }
        }

        return ['actual_days' => $actualDays, 'allowances' => $allowances, 'penalties' => $penalties, 'warnings' => $warnings];
    }

    /** @param class-string<AllowanceRule|PenaltyRule> $model */
    private function applicable(string $model, Employee $employee): Collection
    {
        return $model::with(['departments', 'employees'])->where('is_active', true)->orderByDesc('id')->get()
            ->filter(fn (Model $rule): bool => $rule->scope === 'company'
                || ($rule->scope === 'departments' && $rule->departments->contains('id', $employee->department_id))
                || ($rule->scope === 'employees' && $rule->employees->contains('id', $employee->id)))
            ->sortByDesc(fn (Model $rule): int => ['company' => 1, 'departments' => 2, 'employees' => 3][$rule->scope])
            ->unique('type')->values();
    }

    /** @return array{rule_id: int, name: string, type: string, amount: int, quantity: float, unit: string, total: float} */
    private function line(Model $rule, float $quantity, string $unit): array
    {
        return ['rule_id' => $rule->id, 'name' => $rule->name, 'type' => $rule->type, 'amount' => (int) $rule->amount, 'quantity' => $quantity, 'unit' => $unit, 'total' => round((int) $rule->amount * $quantity, 2)];
    }
}
