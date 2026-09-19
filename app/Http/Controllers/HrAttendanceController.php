<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustAttendanceRequest;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrAttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService) {}

    public function index(Request $request): View
    {
        $attendances = Attendance::query()
            ->with(['employee.department', 'employee.position'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->whereHas('employee', fn ($employee) => $employee
                    ->where('employee_code', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%"));
            })
            ->when($request->filled('department_id'), fn ($query) => $query->whereHas('employee', fn ($employee) => $employee->where('department_id', $request->integer('department_id'))))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('work_date', $request->date('date')))
            ->latest('work_date')
            ->paginate(15)
            ->withQueryString();

        return view('hr.attendances.index', compact('attendances'));
    }

    public function show(Attendance $attendance): View
    {
        $attendance->load(['employee.department', 'employee.position', 'adjustedBy']);

        return view('hr.attendances.show', [
            'attendance' => $attendance,
            'status' => $this->attendanceService->status($attendance),
        ]);
    }

    public function update(AdjustAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        $attendance->update([
            'check_in_at' => $request->date('check_in_at'),
            'check_out_at' => $request->date('check_out_at'),
            'adjustment_reason' => $request->string('adjustment_reason')->toString(),
            'adjusted_by' => $request->user()->id,
        ]);

        return redirect()->route('hr.attendances.show', $attendance)->with('success', 'Đã điều chỉnh chấm công.');
    }
}
