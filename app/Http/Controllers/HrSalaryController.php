<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentStatus;
use App\Models\Employee;
use App\Models\MonthlySalary;
use App\Services\ExcelExportService;
use App\Services\SalaryService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HrSalaryController extends Controller
{
    public function __construct(private readonly SalaryService $salaryService) {}

    public function index(Request $request): View|StreamedResponse
    {
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
                ['Mã NV', 'Họ tên', 'Phòng ban', 'Năm', 'Tháng', 'Ngày công tháng', 'Ngày làm', 'Ngày nghỉ duyệt', 'Nghỉ có lương', 'Nghỉ không lương', 'Lương cứng', 'Lương ngày', 'Tổng lương'],
                $salaries->map(fn (MonthlySalary $salary): array => [
                    $salary->employee?->employee_code, $salary->employee?->full_name, $salary->employee?->department?->name,
                    $year, $month, (int) $salary->working_days, (float) $salary->worked_days,
                    (float) $salary->approved_leave_days, (float) $salary->paid_leave_days, (float) $salary->unpaid_leave_days,
                    (float) $salary->base_salary, (float) $salary->daily_salary, (float) $salary->total_salary,
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
}
