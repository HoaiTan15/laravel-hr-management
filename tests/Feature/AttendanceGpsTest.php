<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesEmployee;
use Tests\TestCase;

class AttendanceGpsTest extends TestCase
{
    use RefreshDatabase, CreatesEmployee;

    private function validGps(): array
    {
        return [
            'latitude' => (string) config('app.attendance_gps.workplace_latitude'),
            'longitude' => (string) config('app.attendance_gps.workplace_longitude'),
            'accuracy' => '10',
        ];
    }

    public function test_check_in_requires_gps_coordinates(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee->user)->post(route('attendance.check-in.store'), [])
            ->assertSessionHasErrors(['latitude', 'longitude', 'accuracy']);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_check_in_rejects_invalid_latitude(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee->user)->post(route('attendance.check-in.store'), [
            'latitude' => '91',
            'longitude' => '0',
            'accuracy' => '10',
        ])->assertSessionHasErrors('latitude');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_check_in_rejects_invalid_longitude(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee->user)->post(route('attendance.check-in.store'), [
            'latitude' => '0',
            'longitude' => '181',
            'accuracy' => '10',
        ])->assertSessionHasErrors('longitude');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_check_in_rejects_poor_accuracy(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee->user)->post(route('attendance.check-in.store'), [
            'latitude' => (string) config('app.attendance_gps.workplace_latitude'),
            'longitude' => (string) config('app.attendance_gps.workplace_longitude'),
            'accuracy' => '150',
        ])->assertSessionHasErrors('gps');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_check_in_rejects_location_outside_radius(): void
    {
        $employee = $this->createEmployee();

        // 21.0285°N, 105.8542°E is approximately 500m away from the configured workplace
        $this->actingAs($employee->user)->post(route('attendance.check-in.store'), [
            'latitude' => '21.028500',
            'longitude' => '105.854200',
            'accuracy' => '10',
        ])->assertSessionHasErrors('gps');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_check_in_stores_gps_coordinates(): void
    {
        $employee = $this->createEmployee();

        $this->actingAs($employee->user)->post(route('attendance.check-in.store'), $this->validGps())
            ->assertRedirect();

        $attendance = Attendance::firstOrFail();
        $this->assertSame(config('app.attendance_gps.workplace_latitude'), $attendance->check_in_latitude);
        $this->assertSame(config('app.attendance_gps.workplace_longitude'), $attendance->check_in_longitude);
        $this->assertSame(10.0, $attendance->check_in_accuracy);
    }

    public function test_check_out_requires_gps_coordinates(): void
    {
        $employee = $this->createEmployee();
        Attendance::create(['employee_id' => $employee->id, 'work_date' => today(), 'check_in_at' => now()->subHour()]);

        $this->actingAs($employee->user)->post(route('attendance.check-out'), [])
            ->assertSessionHasErrors(['latitude', 'longitude', 'accuracy']);

        $this->assertNull(Attendance::first()->check_out_at);
    }

    public function test_check_out_stores_gps_coordinates(): void
    {
        $employee = $this->createEmployee();
        Attendance::create(['employee_id' => $employee->id, 'work_date' => today(), 'check_in_at' => now()->subHour()]);

        $this->actingAs($employee->user)->post(route('attendance.check-out'), $this->validGps())
            ->assertRedirect();

        $attendance = Attendance::firstOrFail();
        $this->assertNotNull($attendance->check_out_at);
        $this->assertSame(config('app.attendance_gps.workplace_latitude'), $attendance->check_out_latitude);
        $this->assertSame(config('app.attendance_gps.workplace_longitude'), $attendance->check_out_longitude);
        $this->assertSame(10.0, $attendance->check_out_accuracy);
    }

    public function test_check_out_rejects_location_outside_radius(): void
    {
        $employee = $this->createEmployee();
        Attendance::create(['employee_id' => $employee->id, 'work_date' => today(), 'check_in_at' => now()->subHour()]);

        $this->actingAs($employee->user)->post(route('attendance.check-out'), [
            'latitude' => '21.028500',
            'longitude' => '105.854200',
            'accuracy' => '10',
        ])->assertSessionHasErrors('gps');

        $this->assertNull(Attendance::first()->check_out_at);
    }

    public function test_hr_check_in_validates_gps(): void
    {
        $hr = $this->createEmployee(['role' => UserRole::HR]);

        $this->actingAs($hr->user)->post(route('attendance.check-in.store'), [
            'latitude' => '21.028500',
            'longitude' => '105.854200',
            'accuracy' => '10',
        ])->assertSessionHasErrors('gps');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_hr_check_out_validates_gps(): void
    {
        $hr = $this->createEmployee(['role' => UserRole::HR]);
        Attendance::create(['employee_id' => $hr->id, 'work_date' => today(), 'check_in_at' => now()->subHour()]);

        $this->actingAs($hr->user)->post(route('attendance.check-out'), [
            'latitude' => '21.028500',
            'longitude' => '105.854200',
            'accuracy' => '10',
        ])->assertSessionHasErrors('gps');

        $this->assertNull(Attendance::first()->check_out_at);
    }
}
