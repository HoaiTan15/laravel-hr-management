<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\CheckInRequest;
use App\Http\Requests\CheckOutRequest;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService) {}

    public function create(): View|RedirectResponse
    {
        if (request()->user()->role === UserRole::EMPLOYEE) {
            return redirect()->route('employee.home');
        }

        if (request()->user()->role === UserRole::HR) {
            return redirect()->route('hr.home');
        }

        return view('attendance.check-in', [
            'attendance' => $this->attendanceService->todayAttendance(request()->user()),
        ]);
    }

    public function store(CheckInRequest $request): RedirectResponse
    {
        $this->attendanceService->checkIn($request->user());

        if ($request->user()->role === UserRole::EMPLOYEE) {
            return redirect()->route('employee.home')->with('attendance_modal', 'checkin-success');
        }

        if ($request->user()->role === UserRole::HR) {
            return redirect()->route('hr.home')->with('attendance_modal', 'checkin-success');
        }

        return redirect()->route('attendance.check-in')->with('success', 'Check-in thành công.');
    }

    public function checkoutConfirmation(Request $request): View|RedirectResponse
    {
        if ($request->user()->role === UserRole::EMPLOYEE) {
            return redirect()->route('employee.home')->with('attendance_modal', 'checkout-confirmation');
        }

        if ($request->user()->role === UserRole::HR) {
            return redirect()->route('hr.home')->with('attendance_modal', 'checkout-confirmation');
        }

        return redirect()->route('login');
    }

    public function checkout(CheckOutRequest $request): RedirectResponse
    {
        $this->attendanceService->checkOut($request->user());

        if ($request->user()->role === UserRole::EMPLOYEE) {
            return redirect()->route('employee.home')->with('attendance_modal', 'checkout-success');
        }

        if ($request->user()->role === UserRole::HR) {
            return redirect()->route('hr.home')->with('attendance_modal', 'checkout-success');
        }

        return redirect()->route('login');
    }
}
