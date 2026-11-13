<?php

namespace App\Models;

use Database\Factories\PenaltyRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PenaltyRule extends Model
{
    /** @use HasFactory<PenaltyRuleFactory> */
    use HasFactory;

    public const TYPES = [
        'late' => 'Đi muộn',
        'early_departure' => 'Về sớm',
        'missing_checkout' => 'Quên chấm công ra',
        'unexcused_absence' => 'Nghỉ không phép',
        'work_rules' => 'Vi phạm nội quy',
        'other' => 'Khác',
    ];

    public const SCOPES = [
        'company' => 'Toàn công ty',
        'departments' => 'Theo phòng ban',
        'employees' => 'Nhân viên cụ thể',
    ];

    protected $fillable = ['name', 'type', 'amount', 'scope', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:0', 'is_active' => 'boolean'];
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class);
    }
}
