<?php

namespace Database\Seeders;

use App\Enums\EmploymentStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::where('name', 'Human Resources')->firstOrFail();
        $engineering = Department::where('name', 'Engineering')->firstOrFail();
        $hrPosition = Position::where('name', 'HR Specialist')->firstOrFail();
        $engineerPosition = Position::where('name', 'Software Engineer')->firstOrFail();

        $employees = [
            ['email' => 'hr@example.test', 'code' => 'EMP-0001', 'name' => 'Demo HR Manager', 'department' => $department, 'position' => $hrPosition, 'phone' => '0900000001'],
            ['email' => 'employee@example.test', 'code' => 'EMP-0002', 'name' => 'Demo Employee One', 'department' => $engineering, 'position' => $engineerPosition, 'phone' => '0900000002'],
            ['email' => 'employee2@example.test', 'code' => 'EMP-0003', 'name' => 'Demo Employee Two', 'department' => $engineering, 'position' => $engineerPosition, 'phone' => '0900000003'],
        ];

        foreach ($employees as $data) {
            $user = User::where('email', $data['email'])->firstOrFail();
            Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $data['department']->id,
                    'position_id' => $data['position']->id,
                    'employee_code' => $data['code'],
                    'full_name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'hire_date' => '2025-01-15',
                    'employment_status' => EmploymentStatus::ACTIVE,
                ],
            );
        }
    }
}
