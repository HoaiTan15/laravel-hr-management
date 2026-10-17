<?php

namespace App\Services;

use App\Enums\LeaveDurationType;
use App\Enums\LeaveStatus;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\MonthlySalary;
use Carbon\CarbonImmutable;

class SalaryService
{
    public const DEFAULT_MONTHLY_SALARY = 15000000.0;

    public function __construct(private readonly LeaveService $leaveService) {}

    public function calculate(Employee $employee, int $year, int $month): MonthlySalary
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

        return MonthlySalary::updateOrCreate(
            ['employee_id' => $employee->id, 'year' => $year, 'month' => $month],
            [
                'base_salary' => $baseSalary,
                'working_days' => $workingDays,
                'worked_days' => $workedDays,
                'approved_leave_days' => $approvedLeaveDays,
                'paid_leave_days' => $paidLeaveDays,
                'unpaid_leave_days' => $unpaidLeaveDays,
                'daily_salary' => $dailySalary,
                'total_salary' => $totalSalary,
                'calculated_at' => now(),
            ],
        );
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
