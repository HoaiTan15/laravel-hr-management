<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'HR Specialist', 'description' => 'Human resources operations.'],
            ['name' => 'Software Engineer', 'description' => 'Application development.'],
            ['name' => 'Financial Analyst', 'description' => 'Financial analysis and reporting.'],
        ] as $position) {
            Position::updateOrCreate(['name' => $position['name']], $position + ['is_active' => true]);
        }
    }
}
