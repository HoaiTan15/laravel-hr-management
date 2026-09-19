<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInRequest;
use App\Http\Requests\CheckOutRequest;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService) {}

    public function create(): View
    {
        return view('attendance.check-in', [
            'attendance' => $this->attendanceService->todayAttendance(request()->user()),
        ]);
    }

    public function store(CheckInRequest $request): RedirectResponse
    {
        $this->attendanceService->checkIn($request->user());

        return redirect()->route('attendance.check-in')->with('success', 'Check-in thành công.');
    }

    public function checkoutConfirmation(Request $request): View|RedirectResponse
    {
        $attendance = $this->attendanceService->todayAttendance($request->user());

        if (! $attendance || $attendance->check_out_at !== null) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        return view('attendance.check-out', compact('attendance'));
    }

    public function checkout(CheckOutRequest $request): RedirectResponse
    {
        $this->attendanceService->checkOut($request->user());
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Check-out thành công.');
    }
}
