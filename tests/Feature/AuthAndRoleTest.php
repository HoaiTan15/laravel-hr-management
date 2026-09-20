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

class AuthAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_login_form(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('name="email"', false);
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'hr@test.example',
            'password' => Hash::make('secret-password'),
            'role' => UserRole::HR,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('hr.home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'hr@test.example']);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'hr@test.example',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        User::factory()->create([
            'email' => 'inactive@test.example',
            'is_active' => false,
            'password' => Hash::make('secret-password'),
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'inactive@test.example',
                'password' => 'secret-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_invalidates_the_session(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($user)->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_guests_are_redirected_from_protected_routes(): void
    {
        $this->get(route('admin.home'))
            ->assertRedirect(route('login'));
    }

    public function test_roles_can_access_only_their_landing_area(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $hr = $this->createEmployee(UserRole::HR);

        $this->actingAs($admin)->get(route('admin.home'))->assertOk();
        $this->actingAs($admin)->get(route('hr.home'))->assertForbidden();
        $this->actingAs($hr->user)->get(route('hr.home'))->assertRedirect(route('attendance.check-in'));
        $this->actingAs($hr->user)->get(route('admin.home'))->assertForbidden();
    }

    public function test_employee_is_redirected_until_they_have_checked_in(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee->user)
            ->get(route('employee.home'))
            ->assertOk()
            ->assertSee('Chấm công đầu ngày')
            ->assertSee('Check-in ngay');

        $this->actingAs($employee->user)
            ->get(route('attendance.check-in'))
            ->assertOk();
    }

    public function test_authenticated_employee_entering_the_application_reaches_the_check_in_gate(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee->user)
            ->get(route('home'))
            ->assertRedirect(route('employee.home'));

        $this->actingAs($employee->user)
            ->get(route('employee.home'))
            ->assertOk()
            ->assertSee('Chấm công đầu ngày');

        $this->actingAs($employee->user)
            ->get(route('employee.tasks.index'))
            ->assertRedirect(route('employee.home'));
    }

    public function test_checked_in_employee_can_access_their_landing_area(): void
    {
        $employee = $this->createEmployee();

        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => today(),
            'check_in_at' => now(),
        ]);

        $this->actingAs($employee->user)
            ->get(route('employee.home'))
            ->assertOk();
    }

    public function test_admin_is_not_subject_to_employee_check_in_gate(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)->get(route('admin.home'))->assertOk();
    }

    public function test_hr_is_redirected_until_they_have_checked_in(): void
    {
        $hr = $this->createEmployee(UserRole::HR);

        $this->actingAs($hr->user)
            ->get(route('hr.home'))
            ->assertRedirect(route('attendance.check-in'));
    }

    public function test_admin_cannot_access_employee_hr_attendance_entry_routes(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $this->actingAs($admin)->get(route('attendance.check-in'))->assertForbidden();
        $this->actingAs($admin)->post(route('attendance.check-out'))->assertForbidden();
    }

    public function test_inactive_authenticated_user_is_logged_out(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee->user);
        $employee->user->update(['is_active' => false]);

        $this->get(route('employee.home'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    private function createEmployee(UserRole $role = UserRole::EMPLOYEE): Employee
    {
        $department = Department::create(['name' => fake()->unique()->company]);
        $position = Position::create(['name' => fake()->unique()->jobTitle]);
        $user = User::factory()->create(['role' => $role]);

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
