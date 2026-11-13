<?php

namespace App\Http\Controllers;

use App\Models\AllowanceRule;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HrAllowanceRuleController extends Controller
{
    public function index(Request $request): View
    {
        return $this->listing($request);
    }

    public function create(Request $request): View
    {
        return $this->listing($request, new AllowanceRule(['scope' => 'company', 'type' => 'meal', 'calculation_basis' => 'working_day', 'is_active' => true]));
    }

    public function edit(Request $request, AllowanceRule $allowanceRule): View
    {
        return $this->listing($request, $allowanceRule->load(['departments', 'employees']));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->save($request, new AllowanceRule);

        return redirect()->route('hr.allowance-rules.index')->with('success', 'Đã thêm cấu hình phụ cấp.');
    }

    public function update(Request $request, AllowanceRule $allowanceRule): RedirectResponse
    {
        $this->save($request, $allowanceRule);

        return redirect()->route('hr.allowance-rules.index')->with('success', 'Đã cập nhật cấu hình phụ cấp.');
    }

    public function destroy(AllowanceRule $allowanceRule): RedirectResponse
    {
        $allowanceRule->delete();

        return redirect()->route('hr.allowance-rules.index')->with('success', 'Đã xóa cấu hình phụ cấp.');
    }

    private function listing(Request $request, ?AllowanceRule $editingRule = null): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', Rule::in(array_keys(AllowanceRule::TYPES))],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'per_page' => ['nullable', Rule::in(['10', '20', '50'])],
        ]);

        $rules = AllowanceRule::query()->with(['departments', 'employees'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where('name', 'like', '%'.trim($search).'%'))
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['department_id'] ?? null, function (Builder $query, string $departmentId): void {
                $query->where(function (Builder $query) use ($departmentId): void {
                    $query->where('scope', 'company')
                        ->orWhereHas('departments', fn (Builder $query) => $query->where('departments.id', $departmentId))
                        ->orWhereHas('employees', fn (Builder $query) => $query->where('department_id', $departmentId));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('is_active', $status === 'active'))
            ->latest('id')->paginate((int) ($filters['per_page'] ?? 20))->withQueryString();

        return view('hr.allowance-rules.index', [
            'title' => 'Cấu hình phụ cấp', 'active' => 'allowance-rules',
            'topTitle' => 'Cổng nhân sự', 'topSub' => 'Cấu hình tính lương',
            'rules' => $rules, 'editingRule' => $editingRule,
            'types' => AllowanceRule::TYPES, 'scopes' => AllowanceRule::SCOPES,
            'calculationBases' => AllowanceRule::CALCULATION_BASES,
            'departments' => Department::orderBy('name')->get(),
            'employees' => $editingRule ? Employee::with('department')->orderBy('full_name')->get() : collect(),
            'totalRules' => AllowanceRule::count(),
            'activeRules' => AllowanceRule::where('is_active', true)->count(),
            'companyRules' => AllowanceRule::where('scope', 'company')->count(),
        ]);
    }

    private function save(Request $request, AllowanceRule $allowanceRule): void
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(array_keys(AllowanceRule::TYPES))],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999'],
            'calculation_basis' => ['required', Rule::in(array_keys(AllowanceRule::CALCULATION_BASES))],
            'scope' => ['required', Rule::in(array_keys(AllowanceRule::SCOPES))],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
            'department_ids' => ['exclude_unless:scope,departments', 'required', 'array', 'min:1'],
            'department_ids.*' => ['required', 'integer', 'distinct', 'exists:departments,id'],
            'employee_ids' => ['exclude_unless:scope,employees', 'required', 'array', 'min:1'],
            'employee_ids.*' => ['required', 'integer', 'distinct', 'exists:employees,id'],
        ], [
            'name.required' => 'Vui lòng nhập tên cấu hình.',
            'amount.required' => 'Vui lòng nhập mức phụ cấp.',
            'amount.integer' => 'Mức tiền phải là số nguyên (VNĐ).',
            'amount.min' => 'Mức tiền phải lớn hơn 0.',
            'amount.max' => 'Mức tiền tối đa là 999.999.999 VNĐ.',
            'department_ids.required' => 'Vui lòng chọn ít nhất một phòng ban.',
            'employee_ids.required' => 'Vui lòng chọn ít nhất một nhân viên.',
        ], [
            'name' => 'tên cấu hình', 'type' => 'loại phụ cấp', 'scope' => 'phạm vi áp dụng',
            'description' => 'ghi chú', 'is_active' => 'trạng thái',
            'calculation_basis' => 'cách tính phụ cấp',
            'department_ids.*' => 'phòng ban', 'employee_ids.*' => 'nhân viên',
        ]);

        DB::transaction(function () use ($data, $allowanceRule): void {
            $allowanceRule->fill(collect($data)->except(['department_ids', 'employee_ids'])->all())->save();
            $allowanceRule->departments()->sync($data['scope'] === 'departments' ? $data['department_ids'] : []);
            $allowanceRule->employees()->sync($data['scope'] === 'employees' ? $data['employee_ids'] : []);
        });
    }
}
