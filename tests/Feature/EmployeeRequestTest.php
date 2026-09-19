<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Request as RequestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesEmployee;
use Tests\TestCase;

class EmployeeRequestTest extends TestCase
{
    use CreatesEmployee;
    use RefreshDatabase;

    #[DataProvider('supportTypes')]
    public function test_employee_can_create_each_hrms_support_type(string $type): void
    {
        $employee = $this->createEmployee();
        $this->checkIn($employee);

        $this->actingAs($employee->user)->post(route('employee.requests.store'), [
            'type' => $type,
            'title' => 'Documented request',
            'content' => 'Request content.',
        ])->assertRedirect();

        $this->assertDatabaseHas('requests', [
            'created_by' => $employee->user_id,
            'type' => $type,
        ]);
    }

    public static function supportTypes(): array
    {
        return [['hardware'], ['software'], ['account'], ['other']];
    }

    public function test_employee_request_form_exposes_the_four_hrms_request_types(): void
    {
        $employee = $this->createEmployee();
        $this->checkIn($employee);

        $this->actingAs($employee->user)->get(route('employee.requests.create'))
            ->assertOk()
            ->assertSee('Phần cứng')
            ->assertSee('Phần mềm')
            ->assertSee('Account')
            ->assertSee('Khác');
    }

    public function test_employee_can_create_hardware_request_and_see_only_own_requests(): void
    {
        $employee = $this->createEmployee();
        $other = $this->createEmployee();
        $this->checkIn($employee);
        RequestModel::create(['created_by' => $other->user_id, 'type' => 'hardware', 'payload' => ['title' => 'Other request'], 'status' => 'pending']);

        $this->actingAs($employee->user)->post(route('employee.requests.store'), [
            'type' => 'hardware',
            'title' => 'Need support',
            'content' => 'Please help with the documented support request.',
        ])->assertRedirect();

        $this->actingAs($employee->user)->get(route('employee.requests.index'))
            ->assertOk()
            ->assertSee('Need support')
            ->assertDontSee('Other request');
        $this->assertDatabaseHas('requests', ['created_by' => $employee->user_id, 'type' => 'hardware', 'status' => 'pending']);
    }

    public function test_employee_can_edit_and_cancel_only_pending_owned_request(): void
    {
        $employee = $this->createEmployee();
        $this->checkIn($employee);
        $record = RequestModel::create(['created_by' => $employee->user_id, 'type' => 'hardware', 'payload' => ['title' => 'Old title', 'content' => 'Old content'], 'status' => 'pending']);

        $this->actingAs($employee->user)->put(route('employee.requests.update', $record), [
            'type' => 'hardware',
            'title' => 'New title',
            'content' => 'New content',
        ])->assertRedirect(route('employee.requests.show', $record));
        $this->assertDatabaseHas('requests', ['id' => $record->id, 'type' => 'hardware']);
        $this->assertSame('New title', $record->fresh()->payload['title']);

        $this->actingAs($employee->user)->delete(route('employee.requests.destroy', $record))
            ->assertRedirect(route('employee.requests.index'));
        $this->assertDatabaseMissing('requests', ['id' => $record->id]);
    }

    public function test_employee_cannot_edit_or_cancel_processed_request(): void
    {
        $employee = $this->createEmployee();
        $this->checkIn($employee);
        $record = RequestModel::create([
            'created_by' => $employee->user_id,
            'type' => 'hardware',
            'payload' => ['title' => 'Support'],
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        $this->actingAs($employee->user)->get(route('employee.requests.edit', $record))->assertForbidden();
        $this->actingAs($employee->user)->delete(route('employee.requests.destroy', $record))->assertForbidden();
        $this->assertDatabaseHas('requests', ['id' => $record->id, 'status' => 'completed']);
    }

    public function test_undocumented_request_type_is_rejected(): void
    {
        $employee = $this->createEmployee();
        $this->checkIn($employee);

        $this->actingAs($employee->user)->post(route('employee.requests.store'), [
            'type' => 'support',
            'title' => 'Undocumented type',
            'content' => 'Should not be accepted.',
        ])->assertSessionHasErrors('type');
    }

    public function test_profile_change_type_cannot_bypass_profile_change_form(): void
    {
        $employee = $this->createEmployee();
        $this->checkIn($employee);

        $this->actingAs($employee->user)->post(route('employee.requests.store'), [
            'type' => 'profile_change',
            'title' => 'Bypass profile flow',
            'content' => 'This must use the profile screen.',
        ])->assertSessionHasErrors('type');
    }

    public function test_employee_can_see_profile_change_details_in_request_detail(): void
    {
        $employee = $this->createEmployee();
        $this->checkIn($employee);
        $record = RequestModel::create([
            'created_by' => $employee->user_id,
            'type' => 'profile_change',
            'payload' => ['changes' => ['cccd' => '001234567890'], 'reason' => 'Identity document updated'],
            'status' => 'pending',
        ]);

        $this->actingAs($employee->user)
            ->get(route('employee.requests.show', $record))
            ->assertOk()
            ->assertSee('001234567890')
            ->assertSee('Identity document updated');
    }

    public function test_employee_cannot_process_a_request(): void
    {
        $employee = $this->createEmployee();
        $this->checkIn($employee);
        $record = RequestModel::create(['created_by' => $employee->user_id, 'type' => 'hardware', 'payload' => ['title' => 'Support'], 'status' => 'pending']);

        $this->actingAs($employee->user)
            ->put(route('admin.requests.process', $record), ['status' => 'completed', 'processing_note' => 'No'])
            ->assertForbidden();
    }

    private function checkIn(Employee $employee): void
    {
        Attendance::create(['employee_id' => $employee->id, 'work_date' => today(), 'check_in_at' => now()]);
    }
}
