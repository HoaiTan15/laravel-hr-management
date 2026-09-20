<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Enums\TaskStatus;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Request as EmployeeRequest;
use App\Models\Request as PersonnelRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleHomeController extends Controller
{
    public function admin(): View
    {
        return view('admin.dashboard', [
            'role' => 'Admin',
            'title' => 'Dashboard Admin',
            'topTitle' => 'Cổng quản trị',
            'topSub' => 'Tổng quan hệ thống HRMS',
            'stats' => [
                ['label' => 'Tổng tài khoản', 'value' => User::count()],
                ['label' => 'Tài khoản đang hoạt động', 'value' => User::where('is_active', true)->count()],
                ['label' => 'PYC chờ xử lý', 'value' => PersonnelRequest::where('status', RequestStatus::PENDING)->count()],
                ['label' => 'PYC đã xử lý', 'value' => PersonnelRequest::whereIn('status', [RequestStatus::REJECTED, RequestStatus::COMPLETED])->count()],
            ],
            'latestRequests' => PersonnelRequest::with('creator')->latest()->limit(5)->get(),
            'latestUsers' => User::latest()->limit(5)->get(),
        ]);
    }

    public function hr(): View
    {
        return view('hr.dashboard', [
            'role' => 'hr',
            'active' => 'dashboard',
            'title' => 'Dashboard HR',
            'topTitle' => 'Cổng nhân sự',
            'topSub' => 'Dashboard tổng quan',
        ]);
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

        $attendanceModal = $request->session()->get('attendance_modal');

        return view('employee.dashboard', [
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
            'show' => $attendanceModal ?? ($attendance ? null : 'checkin'),
            'weekdayLabels' => ['Chủ nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'],
            'requestTypeLabels' => ['hardware' => 'Phần cứng', 'software' => 'Phần mềm', 'account' => 'Account', 'other' => 'Khác', 'profile_change' => 'Thay đổi hồ sơ'],
            'pendingProfileChange' => null,
        ]);
    }
}
