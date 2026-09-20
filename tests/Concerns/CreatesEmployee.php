<?php

namespace Tests\Concerns;

use App\Enums\EmploymentStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;

trait CreatesEmployee
{
    protected function createEmployee(array $userOverrides = [], array $employeeOverrides = []): Employee
    {
        $department = Department::create(['name' => fake()->unique()->company]);
        $position = Position::create(['name' => fake()->unique()->jobTitle]);
        $user = User::factory()->create(array_merge(['role' => UserRole::EMPLOYEE], $userOverrides));

        return Employee::create(array_merge([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employee_code' => fake()->unique()->bothify('TEST-####'),
            'full_name' => $user->name,
            'email' => $user->email,
            'hire_date' => today(),
            'employment_status' => EmploymentStatus::ACTIVE,
        ], $employeeOverrides));
    }
}
