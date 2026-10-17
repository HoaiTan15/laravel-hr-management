<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HrEmployeeController extends Controller
{
    public function create(): View
    {
        return view('hr.employees.form', [
            'employee' => new Employee(['employment_status' => EmploymentStatus::ACTIVE]),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'positions' => Position::where('is_active', true)->orderBy('name')->get(),
            'formAction' => route('hr.employees.store'),
            'formMethod' => 'POST',
            'title' => 'Thêm nhân viên',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data): void {
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => UserRole::EMPLOYEE,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            Employee::create([
                ...$this->employeeData($data),
                'user_id' => $user->id,
            ]);
        });

        return redirect()->route('hr.employees')->with('success', 'Đã thêm nhân viên và tạo tài khoản đăng nhập.');
    }

    public function show(Employee $employee): View
    {
        $employee->load([
            'user',
            'department',
            'position',
            'attendances' => fn ($query) => $query->latest('work_date')->limit(8),
            'tasks' => fn ($query) => $query->with('creator')->latest()->limit(8),
            'personnelProcesses' => fn ($query) => $query->latest(),
        ]);

        return view('hr.employees.show', [
            'employee' => $employee,
            'title' => 'Chi tiết nhân viên',
            'topTitle' => 'Cổng nhân sự',
            'topSub' => 'Chi tiết nhân viên',
            'active' => 'employees',
        ]);
    }

    public function edit(Employee $employee): View
    {
        $employee->load('user');

        return view('hr.employees.form', [
            'employee' => $employee,
            'departments' => Department::where('is_active', true)->orWhereKey($employee->department_id)->orderBy('name')->get(),
            'positions' => Position::where('is_active', true)->orWhereKey($employee->position_id)->orderBy('name')->get(),
            'formAction' => route('hr.employees.update', $employee),
            'formMethod' => 'PATCH',
            'title' => 'Cập nhật nhân viên',
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $data = $this->validated($request, $employee);

        DB::transaction(function () use ($data, $employee): void {
            $employee->user()->update([
                'name' => $data['full_name'],
                'email' => $data['email'],
            ]);

            $employee->update($this->employeeData($data));

            if ($employee->employment_status === EmploymentStatus::TERMINATED) {
                $employee->user()->update(['is_active' => false]);
            } else {
                $employee->user()->update(['is_active' => true]);
            }
        });

        return redirect()->route('hr.employees.show', $employee)->with('success', 'Đã cập nhật hồ sơ nhân viên.');
    }

    private function validated(Request $request, ?Employee $employee = null): array
    {
        $userId = $employee?->user_id;

        return $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [$employee ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'employee_code' => ['required', 'string', 'max:50', Rule::unique('employees', 'employee_code')->ignore($employee?->id)],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'max:20'],
            'cccd' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'hire_date' => ['required', 'date'],
            'employment_status' => ['required', Rule::in(array_column(EmploymentStatus::cases(), 'value'))],
            'termination_date' => ['nullable', 'date'],
            'termination_reason' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function employeeData(array $data): array
    {
        return collect($data)->except(['password', 'password_confirmation'])->all() + [
            'email' => $data['email'],
        ];
    }
}
