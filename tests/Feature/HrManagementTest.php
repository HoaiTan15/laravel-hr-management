<?php

namespace Tests\Feature;

use App\Enums\EmploymentStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_management_pages_are_connected_to_database(): void
    {
        $department = Department::create(['name' => 'Human Resources', 'description' => 'HR', 'is_active' => true]);
        $position = Position::create(['name' => 'HR Specialist', 'description' => 'HR', 'is_active' => true]);
        $hrUser = User::factory()->create(['role' => UserRole::HR, 'name' => 'Demo HR']);

        $employee = Employee::create([
            'user_id' => $hrUser->id,
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employee_code' => 'EMP-TEST-001',
            'full_name' => 'Demo HR',
            'email' => $hrUser->email,
            'phone' => '0900000000',
            'hire_date' => today(),
            'employment_status' => EmploymentStatus::ACTIVE,
        ]);

        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => today(),
            'check_in_at' => now(),
        ]);

        $this->actingAs($hrUser);

        foreach ([
            'hr.dashboard',
            'hr.employees',
            'hr.departments',
            'hr.positions',
            'hr.tasks',
            'hr.requests',
            'hr.recruitment',
            'hr.termination',
            'hr.profile',
            'hr.attendance.index',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }

        $this->get(route('hr.employees'))->assertSee('EMP-TEST-001');
        $this->get(route('hr.profile'))->assertSee('Demo HR');
    }
}
