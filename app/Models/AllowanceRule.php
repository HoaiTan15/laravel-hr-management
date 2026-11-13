<?php

namespace App\Models;

use Database\Factories\AllowanceRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AllowanceRule extends Model
{
    /** @use HasFactory<AllowanceRuleFactory> */
    use HasFactory;

    public const TYPES = [
        'parking' => 'Gửi xe',
        'meal' => 'Tiền ăn',
        'transport' => 'Đi lại / Xăng xe',
        'phone' => 'Điện thoại',
        'responsibility' => 'Trách nhiệm',
        'other' => 'Khác',
    ];

    public const SCOPES = [
        'company' => 'Toàn công ty',
        'departments' => 'Theo phòng ban',
        'employees' => 'Nhân viên cụ thể',
    ];

    public const CALCULATION_BASES = ['monthly' => 'Cố định theo tháng', 'working_day' => 'Theo ngày công thực tế'];

    protected $fillable = ['name', 'type', 'amount', 'calculation_basis', 'scope', 'description', 'is_active'];

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
