<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array{work_date: string, check_in_at: string, check_out_at: string}
     */
    public function definition(): array
    {
        return [
            'work_date' => '2026-11-09',
            'check_in_at' => '2026-11-09 08:00:00',
            'check_out_at' => '2026-11-09 17:00:00',
        ];
    }
}
