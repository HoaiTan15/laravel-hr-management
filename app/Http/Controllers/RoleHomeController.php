<?php

namespace App\Http\Controllers;

use App\Models\Request as EmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleHomeController extends Controller
{
    public function admin(): View
    {
        return view('role-home', ['role' => 'Admin']);
    }

    public function hr(): View
    {
        return view('hr-dashboard', ['role' => 'hr', 'active' => 'dashboard', 'title' => 'Dashboard HR', 'topTitle' => 'Cổng nhân sự', 'topSub' => 'Dashboard tổng quan']);
    }

    public function employee(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);
        $employee->load('department', 'position', 'user');
        $attendance = $employee->attendances()->whereDate('work_date', today())->first();
        $tasks = $employee->tasks()
            ->with('creator')
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->limit(4)
            ->get();
        $taskCount = $employee->tasks()->count();
        $tasksDueToday = $employee->tasks()->whereDate('due_at', today())->count();
        $requests = EmployeeRequest::where('created_by', $employee->user_id)
            ->latest()
            ->limit(3)
            ->get();
        $pendingRequestCount = EmployeeRequest::where('created_by', $employee->user_id)
            ->where('status', 'pending')
            ->count();

        return view('employee-dashboard', [
            'role' => 'employee',
            'active' => 'dashboard',
            'title' => 'Dashboard cá nhân',
            'topTitle' => 'Dashboard cá nhân',
            'topSub' => 'Không gian làm việc cá nhân',
            'employee' => $employee,
            'attendance' => $attendance,
            'tasks' => $tasks,
            'taskCount' => $taskCount,
            'tasksDueToday' => $tasksDueToday,
            'requests' => $requests,
            'pendingRequestCount' => $pendingRequestCount,
            'show' => $attendance
                ? ($request->session()->pull('checkout_success') ? 'checkout-success' : null)
                : 'checkin',
        ]);
    }
}
