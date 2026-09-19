<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'department_id', 'position_id', 'employee_code', 'full_name',
        'date_of_birth', 'gender', 'cccd', 'email', 'phone', 'address', 'avatar', 'hire_date',
        'employment_status', 'termination_date', 'termination_reason',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'termination_date' => 'date',
            'employment_status' => EmploymentStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'created_by', 'user_id');
    }

    public function personnelProcesses(): HasMany
    {
        return $this->hasMany(PersonnelProcess::class);
    }
}
