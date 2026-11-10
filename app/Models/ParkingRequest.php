<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkingRequest extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function permit(): BelongsTo
    {
        return $this->belongsTo(ParkingPermit::class);
    }

    public function typeLabel(): string
    {
        return ['register' => 'Đăng ký xe', 'change' => 'Đổi xe', 'stop' => 'Ngừng gửi xe'][$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối', 'cancelled' => 'Đã hủy'][$this->status] ?? $this->status;
    }
}
