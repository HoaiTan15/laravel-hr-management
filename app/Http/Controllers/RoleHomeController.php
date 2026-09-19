<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Enums\TaskStatus;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Request as PersonnelRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\View\View;

class RoleHomeController extends Controller
{
    public function admin(): View
    {
        return view('role-home', [
            'role' => 'Admin',
            'stats' => [
                ['label' => 'Tổng tài khoản', 'value' => User::count()],
                ['label' => 'Tài khoản đang hoạt động', 'value' => User::where('is_active', true)->count()],
                ['label' => 'PYC chờ xử lý', 'value' => PersonnelRequest::where('status', RequestStatus::PENDING)->count()],
                ['label' => 'PYC đã xử lý', 'value' => PersonnelRequest::whereIn('status', [RequestStatus::APPROVED, RequestStatus::REJECTED, RequestStatus::COMPLETED])->count()],
            ],
        ]);
    }

    public function hr(): View
    {
        return view('role-home', [
            'role' => 'HR',
            'stats' => [
                ['label' => 'Tổng nhân viên', 'value' => Employee::count()],
                ['label' => 'Phòng ban', 'value' => Department::count()],
                ['label' => 'Chấm công hôm nay', 'value' => Attendance::whereDate('work_date', today())->count()],
                ['label' => 'Công việc đang thực hiện', 'value' => Task::where('status', TaskStatus::IN_PROGRESS)->count()],
            ],
        ]);
    }

    public function employee(): View
    {
        $employee = auth()->user()->employee;

        return view('role-home', [
            'role' => 'Employee',
            'stats' => [
                ['label' => 'Công việc đang thực hiện', 'value' => $employee?->tasks()->where('status', TaskStatus::IN_PROGRESS)->count() ?? 0],
                ['label' => 'Công việc hoàn thành', 'value' => $employee?->tasks()->where('status', TaskStatus::COMPLETED)->count() ?? 0],
                ['label' => 'PYC chờ xử lý', 'value' => PersonnelRequest::where('created_by', auth()->id())->where('status', RequestStatus::PENDING)->count()],
                ['label' => 'Chấm công hôm nay', 'value' => $employee?->attendances()->whereDate('work_date', today())->count() ?? 0],
            ],
        ]);
    }
}
