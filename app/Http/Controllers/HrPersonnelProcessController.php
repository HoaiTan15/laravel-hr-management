<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentStatus;
use App\Enums\PersonnelProcessStatus;
use App\Enums\PersonnelProcessType;
use App\Models\Employee;
use App\Models\PersonnelProcess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HrPersonnelProcessController extends Controller
{
    public function create(string $type): View
    {
        $this->assertType($type);

        return view('hr.processes.form', [
            'process' => new PersonnelProcess(['type' => $type, 'status' => PersonnelProcessStatus::DRAFT]),
            'type' => $type,
            'employees' => Employee::with('department', 'position')->orderBy('full_name')->get(),
            'formAction' => route("hr.{$type}.store"),
            'formMethod' => 'POST',
            'title' => $type === PersonnelProcessType::RECRUITMENT->value ? 'Tạo quy trình tuyển dụng' : 'Tạo quy trình thôi việc',
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $this->assertType($type);
        $data = $this->validated($request, $type);
        $data['created_by'] = $request->user()->id;
        $data['type'] = $type;

        PersonnelProcess::create($data);

        return redirect()->route("hr.{$type}")->with('success', 'Đã tạo quy trình nhân sự.');
    }

    public function show(PersonnelProcess $process): View
    {
        $process->load('employee.department', 'employee.position', 'creator', 'completer');

        return view('hr.processes.show', [
            'process' => $process,
            'type' => $process->type->value,
            'title' => 'Chi tiết quy trình nhân sự',
            'topTitle' => 'Cổng nhân sự',
            'topSub' => 'Chi tiết quy trình',
            'active' => $process->type->value,
        ]);
    }

    public function edit(PersonnelProcess $process): View
    {
        $process->load('employee');

        return view('hr.processes.form', [
            'process' => $process,
            'type' => $process->type->value,
            'employees' => Employee::with('department', 'position')->orderBy('full_name')->get(),
            'formAction' => route('hr.processes.update', $process),
            'formMethod' => 'PATCH',
            'title' => 'Cập nhật quy trình nhân sự',
        ]);
    }

    public function update(Request $request, PersonnelProcess $process): RedirectResponse
    {
        if ($process->status === PersonnelProcessStatus::COMPLETED) {
            return back()->withErrors(['process' => 'Quy trình đã hoàn tất và không thể chỉnh sửa lại.']);
        }

        $data = $this->validated($request, $process->type->value);

        if ($data['status'] === PersonnelProcessStatus::COMPLETED->value && empty($data['employee_id']) && ! $process->employee_id) {
            return back()->withInput()->withErrors(['employee_id' => 'Phải chọn nhân viên trước khi hoàn tất quy trình.']);
        }

        DB::transaction(function () use ($data, $process, $request): void {
            $isCompleting = $data['status'] === PersonnelProcessStatus::COMPLETED->value;

            if ($isCompleting) {
                $employee = isset($data['employee_id']) ? Employee::with('user')->find($data['employee_id']) : $process->employee()->with('user')->first();

                abort_unless($employee, 422, 'Không tìm thấy nhân viên của quy trình.');

                if ($process->type === PersonnelProcessType::TERMINATION) {
                    $employee->update([
                        'employment_status' => EmploymentStatus::TERMINATED,
                        'termination_date' => $data['effective_date'] ?? today(),
                        'termination_reason' => $data['reason'] ?? null,
                    ]);
                    $employee->user?->update(['is_active' => false]);
                } else {
                    $employee->update([
                        'employment_status' => EmploymentStatus::ACTIVE,
                        'termination_date' => null,
                        'termination_reason' => null,
                    ]);
                    $employee->user?->update(['is_active' => true]);
                }

                $data['employee_id'] = $employee->id;
                $data['completed_by'] = $request->user()->id;
                $data['completed_at'] = now();
            } else {
                $data['completed_by'] = null;
                $data['completed_at'] = null;
            }

            $process->update($data);
        });

        return redirect()->route('hr.processes.show', $process)->with('success', 'Đã cập nhật quy trình nhân sự.');
    }

    private function validated(Request $request, string $type): array
    {
        return $request->validate([
            'employee_id' => [$type === PersonnelProcessType::TERMINATION->value ? 'required' : 'nullable', 'nullable', 'exists:employees,id'],
            'status' => ['required', Rule::in(array_column(PersonnelProcessStatus::cases(), 'value'))],
            'effective_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function assertType(string $type): void
    {
        abort_unless(in_array($type, [PersonnelProcessType::RECRUITMENT->value, PersonnelProcessType::TERMINATION->value], true), 404);
    }
}
