<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentStatus;
use App\Models\Employee;
use App\Services\SalaryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrSalaryController extends Controller
{
    public function __construct(private readonly SalaryService $salaryService) {}

    public function index(Request $request): View
    {
        $year = (int) $request->integer('year', now()->year);
        $month = max(1, min(12, (int) $request->integer('month', now()->month)));
        $employees = Employee::query()
            ->with(['department', 'position'])
            ->where('employment_status', EmploymentStatus::ACTIVE)
            ->orderBy('employee_code')
            ->get();
        $salaries = collect($this->salaryService->calculateForEmployees($employees, $year, $month));
        $salaries->load('employee.department');

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
