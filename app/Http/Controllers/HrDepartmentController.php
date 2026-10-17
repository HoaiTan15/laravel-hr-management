<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HrDepartmentController extends Controller
{
    public function create(): View
    {
        return view('hr.catalog.form', [
            'catalogType' => 'departments',
            'record' => new Department(['is_active' => true]),
            'formAction' => route('hr.departments.store'),
            'formMethod' => 'POST',
            'title' => 'Thêm phòng ban',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Department::create($this->validated($request));

        return redirect()->route('hr.departments')->with('success', 'Đã thêm phòng ban.');
    }

    public function show(Request $request, Department $department): View
    {
        $employees = $department->employees()
            ->with(['user', 'position'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($employee) use ($search): void {
                    $employee->where('employee_code', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('employment_status', $request->string('status')->toString()))
            ->orderBy('full_name')
            ->paginate(12)
            ->withQueryString();

        return view('hr.departments.show', [
            'department' => $department,
            'employees' => $employees,
            'totalEmployees' => $department->employees()->count(),
            'activeEmployees' => $department->employees()->where('employment_status', 'active')->count(),
            'terminatedEmployees' => $department->employees()->where('employment_status', 'terminated')->count(),
            'positionCount' => $department->employees()->distinct('position_id')->count('position_id'),
            'title' => 'Chi tiết phòng ban',
            'topTitle' => 'Cổng nhân sự',
            'topSub' => 'Quản lý nhân sự theo phòng ban',
            'active' => 'departments',
        ]);
    }

    public function edit(Department $department): View
    {
        return view('hr.catalog.form', [
            'catalogType' => 'departments',
            'record' => $department,
            'formAction' => route('hr.departments.update', $department),
            'formMethod' => 'PATCH',
            'title' => 'Cập nhật phòng ban',
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $department->update($this->validated($request, $department));

        return redirect()->route('hr.departments')->with('success', 'Đã cập nhật phòng ban.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->employees()->exists()) {
            return back()->withErrors(['department' => 'Không thể xóa phòng ban đang có nhân viên. Hãy khóa phòng ban thay vì xóa.']);
        }

        $department->delete();

        return redirect()->route('hr.departments')->with('success', 'Đã xóa phòng ban.');
    }

    private function validated(Request $request, ?Department $department = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
