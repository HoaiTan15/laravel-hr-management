<?php

namespace App\Services;

use App\Enums\LeaveDurationType;
use App\Enums\LeaveStatus;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class LeaveService
{
    public const ANNUAL_QUOTA_DAYS = 10.0;

    public function workingDaysBetween(CarbonInterface|string $start, CarbonInterface|string $end, LeaveDurationType|string $duration = LeaveDurationType::FULL_DAY): float
    {
        $startDate = CarbonImmutable::parse($start)->startOfDay();
        $endDate = CarbonImmutable::parse($end)->startOfDay();

        if ($duration instanceof LeaveDurationType && $duration === LeaveDurationType::HALF_DAY) {
            return $startDate->isWeekday() && $startDate->isSameDay($endDate) ? 0.5 : 0.0;
        }

        $days = 0.0;
        for ($date = $startDate; $date->lessThanOrEqualTo($endDate); $date = $date->addDay()) {
            if ($date->isWeekday()) {
                $days += 1.0;
            }
        }

        return $days;
    }

    public function quotaSummary(Employee $employee, int $year): array
    {
        $approved = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', LeaveStatus::APPROVED)
            ->whereYear('start_date', $year)
            ->get();
        $pending = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', LeaveStatus::PENDING)
            ->whereYear('start_date', $year)
            ->sum('requested_days');
        $used = (float) $approved->sum(fn (LeaveRequest $leave): float => (float) $leave->requested_days);

        return [
            'quota' => self::ANNUAL_QUOTA_DAYS,
            'used' => $used,
            'remaining' => max(self::ANNUAL_QUOTA_DAYS - $used, 0),
            'pending' => (float) $pending,
        ];
    }

    public function recalculateYearAllocations(Employee $employee, int $year): void
    {
        $approved = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', LeaveStatus::APPROVED)
            ->whereYear('start_date', $year)
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();
        $used = 0.0;

        foreach ($approved as $leave) {
            $requested = (float) $leave->requested_days;
            $paid = min($requested, max(self::ANNUAL_QUOTA_DAYS - $used, 0));
            $leave->update([
                'paid_leave_days' => $paid,
                'unpaid_leave_days' => max($requested - $paid, 0),
            ]);
            $used += $requested;
        }
    }

    public function hasConflictingRequest(Employee $employee, CarbonInterface|string $start, CarbonInterface|string $end): bool
    {
        $startDate = CarbonImmutable::parse($start)->toDateString();
        $endDate = CarbonImmutable::parse($end)->toDateString();

        return $employee->leaveRequests()
            ->where('status', '!=', LeaveStatus::REJECTED)
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->exists();
    }
}
