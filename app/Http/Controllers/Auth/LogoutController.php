<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService) {}

    public function __invoke(Request $request): RedirectResponse|\Illuminate\Http\Response
    {
        $user = $request->user();

        if ($user?->role === UserRole::EMPLOYEE && $this->attendanceService->hasOpenTodayAttendance($user)) {
            return redirect()->route('employee.home')->with('attendance_modal', 'checkout-confirmation');
        }

        if ($user && $user->role === UserRole::HR && $this->attendanceService->hasOpenTodayAttendance($user)) {
            return response()->view('attendance.check-out-confirmation', [
                'attendance' => $this->attendanceService->todayAttendance($user),
                'role' => strtolower($user->role->value),
                'active' => 'dashboard',
                'title' => 'Xác nhận Check-out',
                'topTitle' => 'Dashboard cá nhân',
                'topSub' => 'Xác nhận Check-out',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
