<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentStatus;
use App\Enums\PersonnelProcessType;
use App\Enums\RequestStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PersonnelProcess;
use App\Models\Position;
use App\Models\Request as EmployeeRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrManagementController extends Controller
{
    public function dashboard(): View
    {
        $todayAttendances = \App\Models\Attendance::query()
            ->with(['employee.department', 'employee.position'])
            ->whereDate('work_date', today())
            ->whereNotNull('check_in_at')
            ->latest('check_in_at')
            ->get();

        $activeEmployees = Employee::where('employment_status', EmploymentStatus::ACTIVE)->count();
        $totalEmployees = Employee::count();
        $pendingRequests = EmployeeRequest::where('status', RequestStatus::PENDING)->count();
        $openRecruitment = PersonnelProcess::where('type', PersonnelProcessType::RECRUITMENT)
            ->whereIn('status', ['draft', 'in_progress'])
            ->count();
        $lateCount = $todayAttendances->filter(fn ($attendance) => $attendance->check_in_at?->format('H:i:s') > '08:00:00')->count();
        $notCheckedOut = $todayAttendances->filter(fn ($attendance) => ! $attendance->check_out_at)->count();

        return view('hr.dashboard', [
            'role' => 'hr',
            'active' => 'dashboard',
            'title' => 'Dashboard HR',
            'topTitle' => 'Cổng nhân sự',
            'topSub' => 'Dashboard tổng quan',
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'pendingRequests' => $pendingRequests,
            'openRecruitment' => $openRecruitment,
            'lateCount' => $lateCount,
            'notCheckedOut' => $notCheckedOut,
            'todayAttendances' => $todayAttendances,
            'recentTasks' => Task::with('assignee.user')->latest()->limit(5)->get(),
        ]);
    }

    public function employees(Request $request): View
    {
        $employees = Employee::query()
            ->with(['user', 'department', 'position'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($employee) use ($search): void {
                    $employee->where('employee_code', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('department_id'), fn ($query) => $query->where('department_id', $request->integer('department_id')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return $this->module('employees', 'Quản lý nhân viên', 'Danh sách nhân sự đang làm việc và thông tin phân bổ theo đơn vị.', 'groups', [
            'employees' => $employees,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'totalEmployees' => Employee::count(),
            'activeEmployees' => Employee::where('employment_status', EmploymentStatus::ACTIVE)->count(),
            'departmentCount' => Department::where('is_active', true)->count(),
            'pendingProfiles' => Employee::where('employment_status', EmploymentStatus::ACTIVE)->whereNull('phone')->count(),
        ]);
    }

    public function departments(): View
    {
        return $this->module('departments', 'Quản lý phòng ban', 'Cơ cấu tổ chức và nhân sự theo từng đơn vị.', 'corporate_fare', [
            'departments' => Department::withCount('employees')->latest()->get(),
            'departmentCount' => Department::count(),
            'activeDepartments' => Department::where('is_active', true)->count(),
            'assignedEmployees' => Employee::where('employment_status', EmploymentStatus::ACTIVE)->count(),
            'emptyDepartments' => Department::doesntHave('employees')->count(),
        ]);
    }

    public function positions(): View
    {
        return $this->module('positions', 'Quản lý chức vụ', 'Danh mục chức vụ và số lượng nhân sự đang đảm nhiệm.', 'badge', [
            'positions' => Position::withCount('employees')->latest()->get(),
            'positionCount' => Position::count(),
            'usedPositions' => Position::has('employees')->count(),
            'vacantPositions' => Position::doesntHave('employees')->count(),
            'activePositions' => Position::where('is_active', true)->count(),
        ]);
    }

    public function tasks(): View
    {
        $tasks = Task::with(['assignee.user', 'creator'])->latest()->get();

        return $this->module('tasks', 'Công việc', 'Theo dõi nhiệm vụ HR giao cho nhân sự.', 'checklist', [
            'tasks' => $tasks,
            'taskCount' => $tasks->count(),
            'inProgressTasks' => $tasks->where('status.value', 'in_progress')->count(),
            'completedTasks' => $tasks->where('status.value', 'completed')->count(),
            'overdueTasks' => $tasks->filter(fn ($task) => $task->due_at?->isPast() && $task->status->value !== 'completed')->count(),
        ]);
    }

    public function requests(): View
    {
        $requests = EmployeeRequest::with('creator')->latest()->get();

        return $this->module('requests', 'Phiếu yêu cầu', 'Danh sách yêu cầu của nhân sự cần HR theo dõi.', 'receipt_long', [
            'requests' => $requests,
            'pendingRequests' => $requests->where('status.value', 'pending')->count(),
            'completedRequests' => $requests->where('status.value', 'completed')->count(),
            'rejectedRequests' => $requests->where('status.value', 'rejected')->count(),
            'averageProcessing' => '—',
        ]);
    }

    public function recruitment(): View
    {
        $processes = PersonnelProcess::with(['employee.department', 'employee.position', 'creator'])
            ->where('type', PersonnelProcessType::RECRUITMENT)
            ->latest('effective_date')
            ->get();

        return $this->module('recruitment', 'Tuyển dụng', 'Theo dõi quy trình tuyển dụng từ hồ sơ đến tiếp nhận.', 'person_add', [
            'processes' => $processes,
            'openRecruitment' => $processes->whereIn('status.value', ['draft', 'in_progress'])->count(),
            'newCandidates' => $processes->count(),
            'interviews' => $processes->where('status.value', 'in_progress')->count(),
            'hiredCandidates' => $processes->where('status.value', 'completed')->count(),
        ]);
    }

    public function termination(): View
    {
        $processes = PersonnelProcess::with(['employee.department', 'employee.position', 'creator'])
            ->where('type', PersonnelProcessType::TERMINATION)
            ->latest('effective_date')
            ->get();

        return $this->module('termination', 'Thôi việc', 'Quản lý hồ sơ bàn giao và tiến độ hoàn tất nghỉ việc.', 'person_remove', [
            'processes' => $processes,
            'processingTerminations' => $processes->whereIn('status.value', ['draft', 'in_progress'])->count(),
            'upcomingTerminations' => $processes->filter(fn ($process) => $process->effective_date?->isFuture())->count(),
            'completedTerminations' => $processes->where('status.value', 'completed')->count(),
            'waitingHandover' => $processes->where('status.value', 'in_progress')->count(),
        ]);
    }

    public function profile(Request $request): View
    {
        $user = $request->user()->load('employee.department', 'employee.position');

        return $this->module('profile', 'Hồ sơ cá nhân', 'Thông tin tài khoản và hồ sơ công tác của HR.', 'account_box', [
            'user' => $user,
            'employee' => $user->employee,
        ]);
    }

    private function module(string $active, string $title, string $description, string $icon, array $data): View
    {
        return view('ui.module', array_merge([
            'role' => 'hr',
            'active' => $active,
            'title' => $title,
            'description' => $description,
            'sectionTitle' => $title,
            'icon' => $icon,
        ], $data));
    }
}
