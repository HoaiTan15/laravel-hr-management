<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, [UserRole::HR, UserRole::EMPLOYEE], true)
            && $this->user()?->is_active === true
            && $this->user()?->employee !== null;
    }

    public function rules(): array
    {
        return [];
    }
}
