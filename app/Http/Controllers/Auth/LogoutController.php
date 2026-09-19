<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LogoutController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|View
    {
        $user = $request->user();

        if ($user?->role === UserRole::EMPLOYEE) {
            $attendance = $user->employee?->attendances()
                ->whereDate('work_date', today())
                ->first();

            if ($attendance?->check_in_at && ! $attendance->check_out_at) {
                return view('attendance.check-out-confirmation', [
                    'role' => 'employee',
                    'active' => 'dashboard',
                    'title' => 'Xác nhận Check-out',
                    'topTitle' => 'Dashboard cá nhân',
                    'topSub' => 'Xác nhận Check-out',
                    'attendance' => $attendance,
                ]);
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
