<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Enums\EmploymentStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceCheckInController extends Controller
{
    public function create(): View
    {
        return view('employee.dashboard', ['role' => 'employee', 'active' => 'dashboard', 'show' => 'checkin', 'title' => 'Chấm công đầu ngày', 'topTitle' => 'Dashboard cá nhân', 'topSub' => 'Không gian làm việc cá nhân']);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);

        abort_unless($employee->employment_status === EmploymentStatus::ACTIVE, 403);

        if ($employee->attendances()->whereDate('work_date', today())->exists()) {
            return back()->withErrors(['attendance' => 'Bạn đã Check-in trong ngày hôm nay.']);
        }

        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => today(),
            'check_in_at' => now(),
        ]);

        return redirect()->route('attendance.check-in.success');
    }

    public function success(): View
    {
        return view('employee.dashboard', ['role' => 'employee', 'active' => 'dashboard', 'show' => 'checkin-success', 'title' => 'Check-in thành công', 'topTitle' => 'Dashboard cá nhân', 'topSub' => 'Không gian làm việc cá nhân']);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);
        $attendance = $employee->attendances()->whereDate('work_date', today())->firstOrFail();

        if ($attendance->check_out_at) {
            return back()->withErrors(['attendance' => 'Bạn đã Check-out trong ngày hôm nay.']);
        }

        if (now()->lt($attendance->check_in_at)) {
            return back()->withErrors(['attendance' => 'Thời gian Check-out phải sau Check-in.']);
        }

        $attendance->update(['check_out_at' => now()]);

        return redirect()->route('employee.home')->with('checkout_success', true);
    }

    public function checkoutSuccess(): RedirectResponse
    {
        return redirect()->route('employee.home')->with('checkout_success', true);
    }
}
