<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Request as RequestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesEmployee;
use Tests\TestCase;

class EmployeeProfileTest extends TestCase
{
    use CreatesEmployee;
    use RefreshDatabase;

    public function test_employee_can_directly_update_contact_fields(): void
    {
        $employee = $this->createEmployee([], [
            'phone' => '0900000000',
            'email' => 'old@example.test',
            'address' => 'Old address',
        ]);
        $this->checkIn($employee);

        $this->actingAs($employee->user)
            ->put(route('employee.profile.update'), [
                'phone' => '0901234567',
                'email' => 'new.personal@example.test',
                'address' => 'New address',
                'avatar' => 'avatar.png',
            ])
            ->assertRedirect(route('employee.profile'));

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'phone' => '0901234567',
            'email' => 'new.personal@example.test',
            'address' => 'New address',
            'avatar' => 'avatar.png',
        ]);
    }

    public function test_protected_profile_fields_are_rejected(): void
    {
        $employee = $this->createEmployee([], [
            'employee_code' => 'EMP-PROTECTED',
            'employment_status' => 'active',
        ]);
        $this->checkIn($employee);

        $this->actingAs($employee->user)
            ->put(route('employee.profile.update'), [
                'employee_code' => 'EMP-TAMPERED',
                'department_id' => 9999,
                'position_id' => 9999,
                'hire_date' => '2020-01-01',
                'employment_status' => 'inactive',
                'role' => 'admin',
            ])
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'employee_code' => 'EMP-PROTECTED',
            'employment_status' => 'active',
        ]);
    }

    public function test_profile_change_creates_pending_request_without_changing_employee(): void
    {
        $employee = $this->createEmployee([
            'email' => 'profile-owner@example.test',
        ], [
            'phone' => '0900000000',
            'address' => 'Old address',
            'date_of_birth' => '2000-01-01',
            'gender' => 'female',
            'cccd' => '001234567890',
        ]);
        $this->checkIn($employee);

        $this->actingAs($employee->user)->post(route('employee.profile.change-request'), [
            'date_of_birth' => '2000-02-02',
            'gender' => 'male',
            'cccd' => '001234567891',
            'reason' => 'Personal information changed',
            'department_id' => 9999,
            'position_id' => 9999,
            'employment_status' => 'terminated',
        ])->assertRedirect(route('employee.profile'));

        $this->assertDatabaseHas('requests', [
            'created_by' => $employee->user_id,
            'type' => 'profile_change',
            'status' => 'pending',
        ]);
        $this->assertSame('0900000000', $employee->fresh()->phone);
        $this->assertSame('Old address', $employee->fresh()->address);
        $this->assertSame('female', $employee->fresh()->gender);
        $this->assertSame('2000-01-01', $employee->fresh()->date_of_birth->toDateString());
        $this->assertSame('001234567890', $employee->fresh()->cccd);
        $this->assertSame('active', $employee->fresh()->employment_status->value);
    }

    public function test_profile_screen_displays_immutable_employee_fields_without_edit_controls(): void
    {
        $employee = $this->createEmployee([], [
            'employee_code' => 'EMP-READONLY',
            'date_of_birth' => '2000-01-01',
            'gender' => 'female',
        ]);
        $this->checkIn($employee);

        $this->actingAs($employee->user)->get(route('employee.profile'))
            ->assertOk()
            ->assertSee('EMP-READONLY')
            ->assertDontSee('name="department_id"', false)
            ->assertDontSee('name="position_id"', false)
            ->assertDontSee('name="employment_status"', false);
    }

    public function test_profile_screen_shows_pending_profile_request(): void
    {
        $employee = $this->createEmployee();
        $this->checkIn($employee);
        RequestModel::create([
            'created_by' => $employee->user_id,
            'type' => 'profile_change',
            'payload' => ['changes' => ['cccd' => '001234567891'], 'reason' => 'Identity changed'],
            'status' => 'pending',
        ]);

        $this->actingAs($employee->user)->get(route('employee.profile'))
            ->assertOk()
            ->assertSee('PYC thay đổi hồ sơ đang chờ xử lý');
    }

    public function test_profile_change_request_appears_in_employee_request_history(): void
    {
        $employee = $this->createEmployee();
        $this->checkIn($employee);
        RequestModel::create([
            'created_by' => $employee->user_id,
            'type' => 'profile_change',
            'payload' => ['changes' => ['cccd' => '001234567890'], 'reason' => 'Identity data changed'],
            'status' => 'pending',
        ]);

        $this->actingAs($employee->user)->get(route('employee.requests.index'))
            ->assertOk()
            ->assertSee('Thay đổi hồ sơ');
    }

    public function test_pending_profile_change_can_be_edited_from_profile_flow(): void
    {
        $employee = $this->createEmployee([], [
            'date_of_birth' => '2000-01-01',
            'gender' => 'female',
            'cccd' => '001234567890',
        ]);
        $this->checkIn($employee);
        $record = RequestModel::create([
            'created_by' => $employee->user_id,
            'type' => 'profile_change',
            'payload' => ['changes' => ['cccd' => '001234567891'], 'reason' => 'Old reason'],
            'status' => 'pending',
        ]);

        $this->actingAs($employee->user)
            ->put(route('employee.profile.change-request.update', $record), [
                'date_of_birth' => '2000-02-02',
                'gender' => 'male',
                'cccd' => '001234567892',
                'reason' => 'Updated reason',
            ])
            ->assertRedirect(route('employee.profile'));

        $this->assertSame([
            'changes' => [
                'date_of_birth' => '2000-02-02',
                'gender' => 'male',
                'cccd' => '001234567892',
            ],
            'reason' => 'Updated reason',
        ], $record->fresh()->payload);
        $this->assertSame('pending', $record->fresh()->status->value);
        $this->assertSame('female', $employee->fresh()->gender);
    }

    private function checkIn(Employee $employee): void
    {
        Attendance::create(['employee_id' => $employee->id, 'work_date' => today(), 'check_in_at' => now()]);
    }
}
