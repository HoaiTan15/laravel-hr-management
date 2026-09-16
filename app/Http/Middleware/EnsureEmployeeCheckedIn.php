<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeCheckedIn
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user || $user->role !== UserRole::EMPLOYEE) {
            return $next($request);
        }

        $employee = $user->employee;
        $hasCheckedIn = $employee?->attendances()
            ->whereDate('work_date', today())
            ->whereNotNull('check_in_at')
            ->exists();

        if (! $hasCheckedIn) {
            return redirect()->route('attendance.check-in');
        }

        return $next($request);
    }
}
