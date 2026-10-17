<?php

namespace App\Models;

use App\Enums\LeaveDurationType;
use App\Enums\LeaveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'requested_by', 'start_date', 'end_date', 'duration_type',
        'half_day_period', 'requested_days', 'paid_leave_days', 'unpaid_leave_days',
        'reason', 'status', 'reviewed_by', 'reviewed_at', 'review_note',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'requested_days' => 'decimal:1',
            'paid_leave_days' => 'decimal:1',
            'unpaid_leave_days' => 'decimal:1',
            'duration_type' => LeaveDurationType::class,
            'status' => LeaveStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getIsOverQuotaAttribute(): bool
    {
        return (float) $this->unpaid_leave_days > 0;
    }
}
