<?php

namespace App\Http\Controllers;

use App\Enums\LeaveDurationType;
use App\Enums\LeaveStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\AttendanceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalAttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService) {}

    public function index(Request $request): View
    {
        $employee = $request->user()->load('employee.department', 'employee.position')->employee;
        abort_unless($employee, 403);
        $data = $request->validate(['month' => ['sometimes', 'required', 'date_format:Y-m']]);
        $month = CarbonImmutable::createFromFormat('!Y-m', $data['month'] ?? now()->format('Y-m'));
        $end = $month->endOfMonth()->startOfDay();
        $attendances = $employee->attendances()
            ->whereBetween('work_date', [$month->toDateString(), $end->toDateString()])
            ->get()->keyBy(fn (Attendance $attendance): string => $attendance->work_date->toDateString());
        $leaves = $employee->leaveRequests()
            ->where('status', LeaveStatus::APPROVED)
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $month->toDateString())
            ->get();
        $days = [];
        $summary = ['on_time' => 0, 'late' => 0, 'leave' => 0.0, 'absent' => 0];

        for ($date = $month; $date->lte($end); $date = $date->addDay()) {
            $leave = $date->isWeekday()
                ? $leaves->first(fn (LeaveRequest $leave): bool => $date->betweenIncluded($leave->start_date, $leave->end_date))
                : null;
            $day = $this->calendarDay($employee, $date, $attendances->get($date->toDateString()), $leave);
            if (array_key_exists($day['status'], $summary) && $day['status'] !== 'leave') {
                $summary[$day['status']]++;
            }
            $summary['leave'] += $day['leave_days'];
            $days[] = $day;
        }

        $cells = array_merge(array_fill(0, $month->dayOfWeekIso - 1, null), $days);
        $cells = array_pad($cells, (int) (ceil(count($cells) / 7) * 7), null);
        $selectedDate = $month->isSameMonth(today()) ? today()->toDateString() : ($month->isFuture() ? $month : $end)->toDateString();
        $role = $request->user()->role->value;
        $isHr = $request->user()->role === UserRole::HR;

        return view('attendance.calendar', [
            'role' => $role, 'active' => $isHr ? 'personal-attendance' : 'attendance', 'title' => 'Chấm công của tôi',
            'topTitle' => $isHr ? 'Cổng nhân sự' : 'Dashboard cá nhân', 'topSub' => 'Chấm công của tôi',
            'layout' => 'layouts.'.$role,
            'calendarRoute' => $isHr ? 'hr.attendance.personal' : 'employee.attendance.index',
            'dashboardRoute' => $role.'.home', 'leaveRoute' => $role.'.leave.show',
            'employee' => $employee, 'month' => $month, 'days' => $days,
            'weeks' => array_chunk($cells, 7), 'summary' => $summary, 'selectedDate' => $selectedDate,
        ]);
    }

    /**
     * @return array{date: CarbonImmutable, attendance: ?Attendance, leave: ?LeaveRequest, status: string, label: string, leave_days: float}
     */
    private function calendarDay(Employee $employee, CarbonImmutable $date, ?Attendance $attendance, ?LeaveRequest $leave): array
    {
        $isEmployed = (! $employee->hire_date || $date->gte($employee->hire_date))
            && (! $employee->termination_date || $date->lte($employee->termination_date));

        if ($attendance) {
            $status = $this->attendanceService->isLate($attendance) ? 'late' : 'on_time';
            $label = $status === 'late' ? 'Đi trễ' : 'Đúng giờ';
        } elseif ($leave) {
            $status = 'leave';
            $label = $leave->duration_type === LeaveDurationType::HALF_DAY ? 'Nghỉ phép nửa ngày' : 'Nghỉ phép';
        } elseif (! $isEmployed) {
            $status = 'inactive';
            $label = 'Ngoài thời gian làm việc';
        } elseif ($date->isWeekend()) {
            $status = 'weekend';
            $label = 'Nghỉ cuối tuần';
        } elseif ($date->isFuture()) {
            $status = 'future';
            $label = 'Chưa tới ngày';
        } elseif ($date->isToday()) {
            $status = 'pending';
            $label = 'Chưa chấm công';
        } else {
            $status = 'absent';
            $label = 'Vắng';
        }

        return [
            'date' => $date, 'attendance' => $attendance, 'leave' => $leave,
            'status' => $status, 'label' => $label,
            'leave_days' => $leave ? ($leave->duration_type === LeaveDurationType::HALF_DAY ? 0.5 : 1.0) : 0.0,
        ];
    }
}
