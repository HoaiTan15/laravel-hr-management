<?php

namespace App\Models;

use App\Enums\SalaryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlySalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'year', 'month', 'base_salary', 'working_days', 'worked_days',
        'approved_leave_days', 'paid_leave_days', 'unpaid_leave_days', 'daily_salary',
        'total_salary', 'status', 'calculated_at',
        'actual_worked_days', 'salary_before_adjustments', 'allowance_total', 'penalty_total', 'adjustment_details', 'manual_penalties',
    ];

    protected function casts(): array
    {
        return [
            'actual_worked_days' => 'decimal:1',
            'salary_before_adjustments' => 'decimal:2',
            'allowance_total' => 'decimal:2',
            'penalty_total' => 'decimal:2',
            'adjustment_details' => 'array',
            'manual_penalties' => 'array',
            'base_salary' => 'decimal:2',
            'worked_days' => 'decimal:1',
            'approved_leave_days' => 'decimal:1',
            'paid_leave_days' => 'decimal:1',
            'unpaid_leave_days' => 'decimal:1',
            'daily_salary' => 'decimal:2',
            'total_salary' => 'decimal:2',
            'status' => SalaryStatus::class,
            'calculated_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
