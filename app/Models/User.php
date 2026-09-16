<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function adjustedAttendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'adjusted_by');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function createdRequests(): HasMany
    {
        return $this->hasMany(Request::class, 'created_by');
    }

    public function processedRequests(): HasMany
    {
        return $this->hasMany(Request::class, 'processed_by');
    }

    public function createdPersonnelProcesses(): HasMany
    {
        return $this->hasMany(PersonnelProcess::class, 'created_by');
    }

    public function completedPersonnelProcesses(): HasMany
    {
        return $this->hasMany(PersonnelProcess::class, 'completed_by');
    }
}
