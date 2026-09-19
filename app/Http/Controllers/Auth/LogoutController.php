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

    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user && in_array($user->role, [UserRole::HR, UserRole::EMPLOYEE], true) && $this->attendanceService->hasOpenTodayAttendance($user)) {
            return redirect()->route('attendance.checkout.confirmation');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
