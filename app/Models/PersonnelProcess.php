<?php

namespace App\Models;

use App\Enums\PersonnelProcessStatus;
use App\Enums\PersonnelProcessType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'created_by', 'type', 'status', 'effective_date', 'reason',
        'completed_by', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => PersonnelProcessType::class,
            'status' => PersonnelProcessStatus::class,
            'effective_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
