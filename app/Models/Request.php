<?php

namespace App\Models;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by', 'type', 'payload', 'status', 'processed_by', 'processed_at', 'processing_note',
    ];

    protected function casts(): array
    {
        return [
            'type' => RequestType::class,
            'payload' => 'array',
            'status' => RequestStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function displayTitle(): string
    {
        $payload = $this->payload ?? [];

        return $payload['title']
            ?? match ($this->type?->value) {
                'profile_change' => 'Yêu cầu thay đổi hồ sơ',
                'hardware' => 'Yêu cầu hỗ trợ phần cứng',
                'software' => 'Yêu cầu hỗ trợ phần mềm',
                'account' => 'Yêu cầu tài khoản',
                default => 'Phiếu yêu cầu',
            };
    }

    /**
     * Return all human-readable content stored in the request payload.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public function displayDetails(): array
    {
        $payload = $this->payload ?? [];
        $details = [];

        foreach (['content' => 'Nội dung', 'description' => 'Mô tả', 'reason' => 'Lý do'] as $key => $label) {
            if (filled($payload[$key] ?? null)) {
                $details[] = ['label' => $label, 'value' => (string) $payload[$key]];
            }
        }

        if (is_array($payload['changes'] ?? null) && $payload['changes'] !== []) {
            $labels = [
                'date_of_birth' => 'Ngày sinh',
                'gender' => 'Giới tính',
                'cccd' => 'CCCD',
                'phone' => 'Số điện thoại',
                'address' => 'Địa chỉ',
            ];
            $changes = collect($payload['changes'])
                ->map(fn ($value, $key) => ($labels[$key] ?? str_replace('_', ' ', ucfirst($key))).': '.(is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE)))
                ->implode('; ');
            $details[] = ['label' => 'Thông tin thay đổi', 'value' => $changes];
        }

        if ($details === []) {
            foreach ($payload as $key => $value) {
                if ($key === 'title' || ! filled($value)) {
                    continue;
                }
                $details[] = [
                    'label' => str_replace('_', ' ', ucfirst($key)),
                    'value' => is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE),
                ];
            }
        }

        return $details;
    }
}
