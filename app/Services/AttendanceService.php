<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    private const WORKDAY_START = '08:00';

    private const LATE_AFTER = '08:30';

    public function employeeFor(User $user): Employee
    {
        if (! in_array($user->role, [UserRole::HR, UserRole::EMPLOYEE], true) || ! $user->employee) {
            throw ValidationException::withMessages([
                'attendance' => 'Tài khoản này không có hồ sơ nhân viên hợp lệ.',
            ]);
        }

        return $user->employee;
    }

    public function hasEmployee(User $user): bool
    {
        return in_array($user->role, [UserRole::HR, UserRole::EMPLOYEE], true)
            && $user->employee !== null;
    }

    public function todayAttendance(User $user): ?Attendance
    {
        $employee = $this->employeeFor($user);

        return $employee->attendances()->whereDate('work_date', today())->first();
    }

    public function checkIn(User $user): Attendance
    {
        $employee = $this->employeeFor($user);
        $today = today();

        if ($employee->attendances()->whereDate('work_date', $today)->exists()) {
            throw ValidationException::withMessages([
                'attendance' => 'Bạn đã check-in trong ngày hôm nay.',
            ]);
        }

        return $employee->attendances()->create([
            'work_date' => $today,
            'check_in_at' => now(),
        ]);
    }

    public function checkOut(User $user): Attendance
    {
        $attendance = $this->todayAttendance($user);

        if (! $attendance) {
            throw ValidationException::withMessages([
                'attendance' => 'Bạn chưa check-in hôm nay.',
            ]);
        }

        if ($attendance->check_out_at !== null) {
            throw ValidationException::withMessages([
                'attendance' => 'Bạn đã check-out hôm nay.',
            ]);
        }

        $checkOutAt = now();

        if ($checkOutAt->lt($attendance->check_in_at)) {
            throw ValidationException::withMessages([
                'attendance' => 'Thời gian check-out không thể trước thời gian check-in.',
            ]);
        }

        $attendance->update(['check_out_at' => $checkOutAt]);

        return $attendance->refresh();
    }

    public function hasOpenTodayAttendance(User $user): bool
    {
        if (! $this->hasEmployee($user)) {
            return false;
        }

        $attendance = $this->todayAttendance($user);

        return $attendance !== null && $attendance->check_out_at === null;
    }

    public function status(Attendance $attendance): string
    {
        if ($attendance->check_out_at === null) {
            return 'not_checked_out';
        }

        return $attendance->check_in_at->format('H:i') > self::LATE_AFTER ? 'late' : 'on_time';
    }
}
