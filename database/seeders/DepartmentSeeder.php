<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Human Resources', 'description' => 'People operations and employee support.'],
            ['name' => 'Engineering', 'description' => 'Product and software engineering.'],
            ['name' => 'Finance', 'description' => 'Financial planning and operations.'],
        ] as $department) {
            Department::updateOrCreate(['name' => $department['name']], $department + ['is_active' => true]);
        }
    }
}
