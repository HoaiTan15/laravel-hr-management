<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class AdjustAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::HR && $this->user()->is_active;
    }

    public function rules(): array
    {
        return [
            'check_in_at' => ['sometimes', 'nullable', 'date'],
            'check_out_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:check_in_at'],
            'adjustment_reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
