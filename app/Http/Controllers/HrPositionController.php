<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HrPositionController extends Controller
{
    public function create(): View
    {
        return view('hr.catalog.form', [
            'catalogType' => 'positions',
            'record' => new Position(['is_active' => true]),
            'formAction' => route('hr.positions.store'),
            'formMethod' => 'POST',
            'title' => 'Thêm chức vụ',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Position::create($this->validated($request));

        return redirect()->route('hr.positions')->with('success', 'Đã thêm chức vụ.');
    }

    public function show(Request $request, Position $position): View
    {
        $employees = $position->employees()
            ->with(['user', 'department'])
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

        return view('hr.positions.show', [
            'position' => $position,
            'employees' => $employees,
            'totalEmployees' => $position->employees()->count(),
            'activeEmployees' => $position->employees()->where('employment_status', 'active')->count(),
            'terminatedEmployees' => $position->employees()->where('employment_status', 'terminated')->count(),
            'departmentCount' => $position->employees()->distinct('department_id')->count('department_id'),
            'title' => 'Chi tiết chức vụ',
            'topTitle' => 'Cổng nhân sự',
            'topSub' => 'Quản lý nhân sự theo chức vụ',
            'active' => 'positions',
        ]);
    }

    public function edit(Position $position): View
    {
        return view('hr.catalog.form', [
            'catalogType' => 'positions',
            'record' => $position,
            'formAction' => route('hr.positions.update', $position),
            'formMethod' => 'PATCH',
            'title' => 'Cập nhật chức vụ',
        ]);
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $position->update($this->validated($request, $position));

        return redirect()->route('hr.positions')->with('success', 'Đã cập nhật chức vụ.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        if ($position->employees()->exists()) {
            return back()->withErrors(['position' => 'Không thể xóa chức vụ đang có nhân viên. Hãy khóa chức vụ thay vì xóa.']);
        }

        $position->delete();

        return redirect()->route('hr.positions')->with('success', 'Đã xóa chức vụ.');
    }

    private function validated(Request $request, ?Position $position = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('positions', 'name')->ignore($position?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
