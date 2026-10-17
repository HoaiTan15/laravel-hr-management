<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\SalaryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeSalaryController extends Controller
{
    public function __construct(private readonly SalaryService $salaryService) {}

    public function index(Request $request): View
    {
        $employee = $this->employee($request);
        $year = (int) $request->integer('year', now()->year);
        $month = max(1, min(12, (int) $request->integer('month', now()->month)));
        $salary = $this->salaryService->calculate($employee, $year, $month);
        $history = $employee->monthlySalaries()->where('year', $year)->orderByDesc('month')->get();

        return view('employee.salary.index', [
            'role' => 'employee', 'active' => 'salary', 'title' => 'Lương của tôi',
            'topTitle' => 'Dashboard cá nhân', 'topSub' => 'Theo dõi lương tháng',
            'employee' => $employee, 'salary' => $salary, 'history' => $history,
            'year' => $year, 'month' => $month,
        ]);
    }

    private function employee(Request $request): Employee
    {
        $employee = $request->user()->load('employee.department', 'employee.position')->employee;
        abort_unless($employee, 403);

        return $employee;
    }
}
