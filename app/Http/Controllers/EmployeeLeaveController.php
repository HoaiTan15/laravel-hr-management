<?php

namespace App\Http\Controllers;

use App\Enums\LeaveDurationType;
use App\Enums\LeaveStatus;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeLeaveController extends Controller
{
    public function __construct(private readonly LeaveService $leaveService) {}

    public function index(Request $request): View
    {
        $employee = $this->employee($request);
        $year = (int) $request->integer('year', now()->year);
        $summary = $this->leaveService->quotaSummary($employee, $year);
        $leaves = $employee->leaveRequests()
            ->with('reviewer')
            ->whereYear('start_date', $year)
            ->latest()
            ->get();

        return view('employee.leave.index', [
            'role' => 'employee', 'active' => 'leave', 'title' => 'Nghỉ phép',
            'topTitle' => 'Dashboard cá nhân', 'topSub' => 'Lịch sử nghỉ phép',
            'employee' => $employee, 'leaves' => $leaves, 'summary' => $summary, 'year' => $year,
        ]);
    }

    public function create(Request $request): View
    {
        $employee = $this->employee($request);
        $year = (int) $request->integer('year', now()->year);

        return view('employee.leave.form', [
            'role' => 'employee', 'active' => 'leave', 'title' => 'Gửi đơn nghỉ phép',
            'topTitle' => 'Dashboard cá nhân', 'topSub' => 'Gửi đơn nghỉ phép',
            'employee' => $employee, 'summary' => $this->leaveService->quotaSummary($employee, $year),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $this->employee($request);
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'duration_type' => ['required', 'in:full_day,half_day'],
            'half_day_period' => ['nullable', 'required_if:duration_type,half_day', 'in:morning,afternoon'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $start = now()->parse($data['start_date'])->startOfDay();
        $end = now()->parse($data['end_date'])->startOfDay();
        $duration = LeaveDurationType::from($data['duration_type']);

        if ($duration === LeaveDurationType::HALF_DAY && ! $start->isSameDay($end)) {
            return back()->withErrors(['end_date' => 'Nghỉ nửa ngày chỉ được chọn trong cùng một ngày.'])->withInput();
        }

        if ($start->year !== $end->year) {
            return back()->withErrors(['start_date' => 'Đơn nghỉ phép chỉ được nằm trong cùng một năm để tính ngày phép chính xác.'])->withInput();
        }

        $requestedDays = $this->leaveService->workingDaysBetween($start, $end, $duration);
        if ($requestedDays <= 0) {
            return back()->withErrors(['start_date' => 'Khoảng thời gian nghỉ phải có ít nhất một ngày làm việc từ Thứ Hai đến Thứ Sáu.'])->withInput();
        }

        if ($this->leaveService->hasConflictingRequest($employee, $start, $end)) {
            return back()->withErrors(['start_date' => 'Khoảng thời gian này đã có đơn nghỉ phép đang chờ hoặc đã được duyệt.'])->withInput();
        }

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'requested_by' => $request->user()->id,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'duration_type' => $duration,
            'half_day_period' => $duration === LeaveDurationType::HALF_DAY ? $data['half_day_period'] : null,
            'requested_days' => $requestedDays,
            'reason' => $data['reason'],
            'status' => LeaveStatus::PENDING,
        ]);

        $summary = $this->leaveService->quotaSummary($employee, $start->year);
        $message = 'Đã gửi đơn nghỉ phép. HR sẽ duyệt hoặc từ chối đơn của bạn.';
        if ($requestedDays > $summary['remaining']) {
            return redirect()->route('employee.leave.show', $leave)
                ->with('success', $message)
                ->with('warning', 'Số ngày phép còn lại không đủ cho đơn này. Nếu HR duyệt, phần vượt sẽ không hưởng lương.');
        }

        return redirect()->route('employee.leave.show', $leave)->with('success', $message);
    }

    public function show(Request $request, LeaveRequest $leaveRequest): View
    {
        $employee = $this->employee($request);
        abort_unless($leaveRequest->employee_id === $employee->id, 403);

        return view('employee.leave.show', [
            'role' => 'employee', 'active' => 'leave', 'title' => 'Chi tiết đơn nghỉ phép',
            'topTitle' => 'Dashboard cá nhân', 'topSub' => 'Chi tiết đơn nghỉ phép',
            'employee' => $employee, 'leave' => $leaveRequest->load('reviewer'),
        ]);
    }

    private function employee(Request $request): Employee
    {
        $employee = $request->user()->load('employee.department', 'employee.position')->employee;
        abort_unless($employee, 403);

        return $employee;
    }
}
