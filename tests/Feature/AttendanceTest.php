<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_check_in_without_submitting_an_employee_id(): void
    {
        $employee = $this->createEmployee(UserRole::HR);

        $this->actingAs($employee->user)->post(route('attendance.check-in.store'), [])
            ->assertRedirect(route('attendance.check-in'));

        $this->assertDatabaseCount('attendances', 1);
        $this->assertSame(today()->toDateString(), Attendance::first()->work_date->toDateString());
    }

    public function test_employee_can_check_in(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee->user)->post(route('attendance.check-in.store'), [])
            ->assertRedirect(route('employee.home'));

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_duplicate_check_in_is_rejected(): void
    {
        $employee = $this->createEmployee();
        Attendance::create(['employee_id' => $employee->id, 'work_date' => today(), 'check_in_at' => now()]);

        $this->actingAs($employee->user)->post(route('attendance.check-in.store'), [])
            ->assertSessionHasErrors('attendance');

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_employee_cannot_check_in_for_another_employee(): void
    {
        $employee = $this->createEmployee();
        $other = $this->createEmployee();

        $this->actingAs($employee->user)->post(route('attendance.check-in.store'), ['employee_id' => $other->id]);

        $this->assertDatabaseHas('attendances', ['employee_id' => $employee->id]);
        $this->assertDatabaseMissing('attendances', ['employee_id' => $other->id]);
    }

    public function test_check_out_succeeds_without_logging_user_out(): void
    {
        $employee = $this->createEmployee();
        Attendance::create(['employee_id' => $employee->id, 'work_date' => today(), 'check_in_at' => now()->subHour()]);

        $this->actingAs($employee->user)->post(route('attendance.checkout'))
            ->assertRedirect(route('employee.home'));

        $this->assertAuthenticatedAs($employee->user);
        $this->assertNotNull(Attendance::first()->fresh()->check_out_at);
    }

    public function test_check_out_without_check_in_is_rejected(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee->user)->post(route('attendance.checkout'))
            ->assertSessionHasErrors('attendance');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_second_check_out_is_rejected(): void
    {
        $employee = $this->createEmployee();
        Attendance::create(['employee_id' => $employee->id, 'work_date' => today(), 'check_in_at' => now()->subHour(), 'check_out_at' => now()]);

        $this->actingAs($employee->user)->post(route('attendance.checkout'))
            ->assertSessionHasErrors('attendance');
    }

    public function test_employee_cannot_adjust_attendance(): void
    {
        $employee = $this->createEmployee();
        $attendance = Attendance::create(['employee_id' => $employee->id, 'work_date' => today(), 'check_in_at' => now()]);

        $this->actingAs($employee->user)->patch(route('hr.attendances.update', $attendance), ['adjustment_reason' => 'Attempt'])
            ->assertForbidden();
    }

    public function test_hr_can_adjust_attendance_with_reason(): void
    {
        $hr = $this->createEmployee(UserRole::HR);
        $attendance = Attendance::create(['employee_id' => $hr->id, 'work_date' => today(), 'check_in_at' => now()]);

        $this->actingAs($hr->user)->patch(route('hr.attendances.update', $attendance), [
            'check_in_at' => now()->subMinutes(20)->format('Y-m-d H:i:s'),
            'adjustment_reason' => 'Corrected from timesheet.',
        ])->assertRedirect(route('hr.attendances.show', $attendance));

        $this->assertDatabaseHas('attendances', ['id' => $attendance->id, 'adjusted_by' => $hr->user_id, 'adjustment_reason' => 'Corrected from timesheet.']);
    }

    public function test_adjustment_requires_a_reason(): void
    {
        $hr = $this->createEmployee(UserRole::HR);
        $attendance = Attendance::create(['employee_id' => $hr->id, 'work_date' => today(), 'check_in_at' => now()]);

        $this->actingAs($hr->user)->patch(route('hr.attendances.update', $attendance), [])->assertSessionHasErrors('adjustment_reason');
    }

    private function createEmployee(UserRole $role = UserRole::EMPLOYEE): Employee
    {
        $department = Department::create(['name' => fake()->unique()->company]);
        $position = Position::create(['name' => fake()->unique()->jobTitle]);
        $user = User::factory()->create(['role' => $role, 'password' => Hash::make('password')]);

        return Employee::create([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employee_code' => fake()->unique()->bothify('TEST-####'),
            'full_name' => $user->name,
            'hire_date' => today(),
            'employment_status' => 'active',
        ]);
    }
}
