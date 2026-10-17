<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustAttendanceRequest;
use App\Models\Attendance;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceManagementController extends Controller
{
    public function index(Request $request): View
    {
        $records = Attendance::with('employee.department')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->whereHas('employee', fn ($employee) => $employee
                    ->where('employee_code', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%"));
            })
            ->when($request->filled('work_date'), fn ($query) => $query->whereDate('work_date', $request->date('work_date')))
            ->when($request->filled('department_id'), fn ($query) => $query->whereHas('employee', fn ($employee) => $employee->where('department_id', $request->integer('department_id'))))
            ->when($request->input('status') === 'open', fn ($query) => $query->whereNull('check_out_at'))
            ->when($request->input('status') === 'completed', fn ($query) => $query->whereNotNull('check_out_at'))
            ->latest('work_date')->latest('check_in_at')->get();

        return view('attendance.index', [
            'role' => 'hr', 'active' => 'attendance', 'title' => 'Quản lý chấm công',
            'topTitle' => 'Cổng nhân sự', 'topSub' => 'Quản lý chấm công', 'records' => $records,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'totalRecords' => $records->count(),
            'checkedOutRecords' => $records->whereNotNull('check_out_at')->count(),
            'openRecords' => $records->whereNull('check_out_at')->count(),
            'lateRecords' => $records->filter(fn ($record) => $record->check_in_at?->format('H:i:s') > '08:30:00')->count(),
        ]);
    }

    public function show(Attendance $attendance): View
    {
        $attendance->load('employee.department', 'employee.position', 'adjustedBy');
        return view('attendance.show', ['role' => 'hr', 'active' => 'attendance', 'title' => 'Chi tiết bản ghi chấm công', 'topTitle' => 'Cổng nhân sự', 'topSub' => 'Chi tiết bản ghi chấm công', 'attendance' => $attendance]);
    }

    public function update(AdjustAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        $data = $request->validated();
        $data['adjusted_by'] = $request->user()->id;
        $attendance->update($data);
        return redirect()->route('hr.attendances.show', $attendance)->with('status', 'Đã điều chỉnh dữ liệu chấm công.');
    }
}
