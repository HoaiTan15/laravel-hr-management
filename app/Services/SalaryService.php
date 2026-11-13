<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Enums\SalaryStatus;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\MonthlySalary;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class SalaryService
{
    public const DEFAULT_MONTHLY_SALARY = 15000000.0;

    public function __construct(private readonly LeaveService $leaveService, private readonly PayrollAdjustmentService $adjustmentService) {}

    public function calculate(Employee $employee, int $year, int $month): MonthlySalary
    {
        return DB::transaction(function () use ($employee, $year, $month): MonthlySalary {
            $salary = MonthlySalary::firstOrCreate(['employee_id' => $employee->id, 'year' => $year, 'month' => $month]);
            $salary = MonthlySalary::whereKey($salary->id)->lockForUpdate()->firstOrFail();
            if ($salary->status !== SalaryStatus::DRAFT) {
                return $salary;
            }

            return $this->calculateDraft($employee, $year, $month, $salary);
        });
    }

    private function calculateDraft(Employee $employee, int $year, int $month, MonthlySalary $salary): MonthlySalary
    {
        $monthStart = CarbonImmutable::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->endOfMonth()->startOfDay();
        $workingDays = (int) $this->leaveService->workingDaysBetween($monthStart, $monthEnd);
        $approvedLeaveDays = 0.0;
        $paidLeaveDays = 0.0;
        $unpaidLeaveDays = 0.0;

        $leaves = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', LeaveStatus::APPROVED)
            ->whereDate('start_date', '<=', $monthEnd->toDateString())
            ->whereDate('end_date', '>=', $monthStart->toDateString())
            ->get();

        foreach ($leaves as $leave) {
            $overlapStart = max($leave->start_date->toDateString(), $monthStart->toDateString());
            $overlapEnd = min($leave->end_date->toDateString(), $monthEnd->toDateString());
            $overlapDays = $this->leaveService->workingDaysBetween($overlapStart, $overlapEnd, $leave->duration_type);
            $requestedDays = (float) $leave->requested_days;
            $ratio = $requestedDays > 0 ? $overlapDays / $requestedDays : 0;

            $approvedLeaveDays += $overlapDays;
            $paidLeaveDays += (float) $leave->paid_leave_days * $ratio;
            $unpaidLeaveDays += (float) $leave->unpaid_leave_days * $ratio;
        }

        $baseSalary = self::DEFAULT_MONTHLY_SALARY;
        $dailySalary = $workingDays > 0 ? $baseSalary / $workingDays : 0.0;
        $workedDays = max($workingDays - $approvedLeaveDays, 0);
        $totalSalary = max($baseSalary - ($unpaidLeaveDays * $dailySalary), 0);
        $adjustments = $this->adjustmentService->calculate($employee, $monthStart, $monthEnd, $leaves, $salary->manual_penalties ?? []);
        $allowanceTotal = array_sum(array_column($adjustments['allowances'], 'total'));
        $penaltyTotal = array_sum(array_column($adjustments['penalties'], 'total'));

        $salary->fill(
            [
                'base_salary' => $baseSalary,
                'working_days' => $workingDays,
                'worked_days' => $workedDays,
                'approved_leave_days' => $approvedLeaveDays,
                'paid_leave_days' => $paidLeaveDays,
                'unpaid_leave_days' => $unpaidLeaveDays,
                'daily_salary' => $dailySalary,
                'salary_before_adjustments' => round($totalSalary, 2),
                'actual_worked_days' => $adjustments['actual_days'],
                'allowance_total' => $allowanceTotal,
                'penalty_total' => $penaltyTotal,
                'adjustment_details' => $adjustments,
                'total_salary' => round(max($totalSalary + $allowanceTotal - $penaltyTotal, 0), 2),
                'calculated_at' => now(),
            ],
        )->save();

        return $salary;
    }

    public function calculateForEmployees(iterable $employees, int $year, int $month): array
    {
        $salaries = [];
        foreach ($employees as $employee) {
            $salaries[] = $this->calculate($employee, $year, $month);
        }

        return $salaries;
    }
}
