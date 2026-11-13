<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentStatus;
use App\Enums\SalaryStatus;
use App\Models\Employee;
use App\Models\MonthlySalary;
use App\Services\ExcelExportService;
use App\Services\PayrollAdjustmentService;
use App\Services\SalaryService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HrSalaryController extends Controller
{
    public function __construct(private readonly SalaryService $salaryService) {}

    public function index(Request $request): View|StreamedResponse
    {
        $request->validate(['year' => ['nullable', 'integer', 'between:2000,2100'], 'month' => ['nullable', 'integer', 'between:1,12']]);
        $year = (int) $request->integer('year', now()->year);
        $month = max(1, min(12, (int) $request->integer('month', now()->month)));
        $employees = Employee::query()
            ->with(['department', 'position'])
            ->where('employment_status', EmploymentStatus::ACTIVE)
            ->orderBy('employee_code')
            ->get();
        $salaries = new Collection($this->salaryService->calculateForEmployees($employees, $year, $month));
        $salaries->load('employee.department');

        if ($request->input('export') === 'xlsx') {
            return app(ExcelExportService::class)->download(sprintf('bang-luong-%04d-%02d.xlsx', $year, $month),
                ['Mã NV', 'Họ tên', 'Phòng ban', 'Năm', 'Tháng', 'Ngày công tháng', 'Ngày làm/kế hoạch', 'Ngày nghỉ duyệt', 'Nghỉ có lương', 'Nghỉ không lương', 'Lương cứng', 'Lương ngày', 'Lương trước cộng trừ', 'Ngày công thực tế', 'Phụ cấp', 'Tiền phạt', 'Thực nhận', 'Trạng thái'],
                $salaries->map(fn (MonthlySalary $salary): array => [
                    $salary->employee?->employee_code, $salary->employee?->full_name, $salary->employee?->department?->name,
                    $year, $month, (int) $salary->working_days, (float) $salary->worked_days,
                    (float) $salary->approved_leave_days, (float) $salary->paid_leave_days, (float) $salary->unpaid_leave_days,
                    (float) $salary->base_salary, (float) $salary->daily_salary, (float) $salary->salary_before_adjustments,
                    (float) $salary->actual_worked_days, (float) $salary->allowance_total, (float) $salary->penalty_total,
                    (float) $salary->total_salary, $salary->status->value,
                ]));
        }

        return view('hr.salary.index', [
            'role' => 'hr', 'active' => 'salary', 'title' => 'Quản lý lương',
            'topTitle' => 'Cổng nhân sự', 'topSub' => 'Tính lương theo ngày công và nghỉ phép',
            'salaries' => $salaries, 'year' => $year, 'month' => $month,
            'employeeCount' => $employees->count(),
            'totalSalary' => $salaries->sum(fn ($salary): float => (float) $salary->total_salary),
            'totalUnpaidDays' => $salaries->sum(fn ($salary): float => (float) $salary->unpaid_leave_days),
        ]);
    }

    public function show(Request $request, Employee $employee): View
    {
        $period = $this->period($request);

        return view('hr.salary.show', [
            'title' => 'Chi tiết lương', 'active' => 'salary',
            'employee' => $employee->load('department'),
            'salary' => $this->salaryService->calculate($employee, $period['year'], $period['month']),
            'manualTypes' => PayrollAdjustmentService::MANUAL_TYPES,
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $period = $this->period($request);
        $request->validate(['action' => ['required', Rule::in(['manual', 'confirm', 'reopen'])]]);
        $data = [];
        if ($request->input('action') === 'manual') {
            $rules = ['penalties' => ['required', 'array:'.implode(',', array_keys(PayrollAdjustmentService::MANUAL_TYPES))]];
            foreach (PayrollAdjustmentService::MANUAL_TYPES as $type => $label) {
                $rules['penalties.'.$type.'.count'] = ['required', 'integer', 'between:0,1000'];
                $rules['penalties.'.$type.'.reason'] = [Rule::requiredIf(fn (): bool => $request->integer('penalties.'.$type.'.count') > 0), 'nullable', 'string', 'max:1000'];
            }
            $data = $request->validate($rules)['penalties'];
        }

        DB::transaction(function () use ($request, $employee, $period, $data): void {
            $salary = $this->salaryService->calculate($employee, $period['year'], $period['month']);
            if ($request->input('action') === 'reopen') {
                if ($salary->status === SalaryStatus::PAID) {
                    throw ValidationException::withMessages(['salary' => 'Không thể mở lại bảng lương đã thanh toán.']);
                }
                $salary->update(['status' => SalaryStatus::DRAFT]);

                return;
            }
            if ($salary->status !== SalaryStatus::DRAFT) {
                throw ValidationException::withMessages(['salary' => 'Bảng lương đã chốt. Cần mở lại trước khi thay đổi.']);
            }
            if ($request->input('action') === 'manual') {
                $salary->update(['manual_penalties' => $data]);
                $this->salaryService->calculate($employee, $period['year'], $period['month']);
            } else {
                if (CarbonImmutable::create($period['year'], $period['month'], 1)->endOfMonth()->gte(now())) {
                    throw ValidationException::withMessages(['salary' => 'Chỉ chốt bảng lương khi tháng đã kết thúc.']);
                }
                if (! empty($salary->adjustment_details['warnings'])) {
                    throw ValidationException::withMessages(['salary' => 'Cần bổ sung cấu hình tiền phạt cho các vi phạm đã ghi nhận trước khi chốt.']);
                }
                $salary->update(['status' => SalaryStatus::CONFIRMED]);
            }
        });

        return redirect()->route('hr.salary.show', ['employee' => $employee, ...$period])->with('success', 'Đã cập nhật bảng lương.');
    }

    /** @return array{year: int, month: int} */
    private function period(Request $request): array
    {
        $data = $request->validate(['year' => ['required', 'integer', 'between:2000,2100'], 'month' => ['required', 'integer', 'between:1,12']]);

        return ['year' => (int) $data['year'], 'month' => (int) $data['month']];
    }
}
