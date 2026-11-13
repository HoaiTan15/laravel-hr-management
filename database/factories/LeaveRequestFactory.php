<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requested_by' => User::factory(),
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-10',
            'duration_type' => 'full_day',
            'requested_days' => 1,
            'paid_leave_days' => 1,
            'unpaid_leave_days' => 0,
            'reason' => 'Nghỉ phép',
            'status' => 'approved',
        ];
    }
}
