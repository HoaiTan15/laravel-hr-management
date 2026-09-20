<?php

namespace Tests\Feature;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesEmployee;
use Tests\TestCase;

class EmployeeAttendanceTest extends TestCase
{
    use CreatesEmployee;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_employee_cannot_check_in_twice_on_the_same_day(): void
    {
        $employee = $this->createEmployee();
        Carbon::setTestNow(Carbon::parse('2026-09-19 08:00:00'));

        $this->actingAs($employee->user)->post(route('attendance.check-in.store'))->assertRedirect();
        $this->actingAs($employee->user)->post(route('attendance.check-in.store'))
            ->assertSessionHasErrors('attendance');

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_employee_cannot_check_out_twice_or_overwrite_first_checkout(): void
    {
        $employee = $this->createEmployee();
        Carbon::setTestNow(Carbon::parse('2026-09-19 08:00:00'));
        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => today(),
            'check_in_at' => now(),
        ]);

        Carbon::setTestNow(Carbon::parse('2026-09-19 17:00:00'));
        $this->actingAs($employee->user)->post(route('attendance.check-out'))->assertRedirect();
        $firstCheckout = Attendance::firstOrFail()->check_out_at;

        Carbon::setTestNow(Carbon::parse('2026-09-19 18:00:00'));
        $this->actingAs($employee->user)->post(route('attendance.check-out'))
            ->assertSessionHasErrors('attendance');

        $this->assertTrue($firstCheckout->equalTo(Attendance::firstOrFail()->check_out_at));
    }

    public function test_logout_with_open_shift_redirects_to_dashboard_checkout_modal(): void
    {
        $employee = $this->createEmployee();
        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => today(),
            'check_in_at' => now(),
        ]);

        $this->actingAs($employee->user)
            ->post(route('logout'))
            ->assertRedirect(route('employee.home'))
            ->assertSessionHas('attendance_modal', 'checkout-confirmation');

        $this->assertAuthenticatedAs($employee->user);
    }

    public function test_confirmed_checkout_records_time_without_logging_out(): void
    {
        $employee = $this->createEmployee();
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => today(),
            'check_in_at' => now()->subHour(),
        ]);

        $this->actingAs($employee->user)
            ->post(route('attendance.check-out'))
            ->assertRedirect(route('employee.home'));

        $this->assertAuthenticatedAs($employee->user);
        $this->assertNotNull($attendance->fresh()->check_out_at);
    }

    public function test_logout_after_checkout_completes_directly(): void
    {
        $employee = $this->createEmployee();
        Attendance::create([
            'employee_id' => $employee->id,
            'work_date' => today(),
            'check_in_at' => now()->subHours(2),
            'check_out_at' => now()->subHour(),
        ]);

        $this->actingAs($employee->user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

}
