<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceManagementController extends Controller
{
    public function index(Request $request): View
    {
        $records = Attendance::with('employee')->latest('work_date')->latest('check_in_at')->get();
        return view('attendance.index', ['role' => 'hr', 'active' => 'attendance', 'title' => 'Quản lý chấm công', 'topTitle' => 'Cổng nhân sự', 'topSub' => 'Quản lý chấm công', 'records' => $records]);
    }

    public function show(Attendance $attendance): View
    {
        $attendance->load('employee');
        return view('attendance.show', ['role' => 'hr', 'active' => 'attendance', 'title' => 'Chi tiết bản ghi chấm công', 'topTitle' => 'Cổng nhân sự', 'topSub' => 'Chi tiết bản ghi chấm công', 'attendance' => $attendance]);
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $data = $request->validate(['check_in_at' => ['required','date'], 'check_out_at' => ['nullable','date','after_or_equal:check_in_at'], 'adjustment_reason' => ['required','string','max:1000']]);
        $data['adjusted_by'] = $request->user()->id;
        $attendance->update($data);
        return redirect()->route('hr.attendance.show', $attendance)->with('status', 'Đã điều chỉnh dữ liệu chấm công.');
    }
}
