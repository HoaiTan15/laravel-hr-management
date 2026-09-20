<?php

namespace Tests\Feature;

use App\Enums\RequestType;
use App\Enums\RequestStatus;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Concerns\CreatesEmployee;
use Tests\TestCase;

class EmployeeDomainContractTest extends TestCase
{
    use CreatesEmployee;
    use RefreshDatabase;

    #[Test]
    public function test_support_request_types_are_exactly_the_four_hrms_values(): void
    {
        $this->assertSame(
            ['hardware', 'software', 'account', 'other'],
            array_map(static fn ($case): string => $case->value, [
                RequestType::HARDWARE,
                RequestType::SOFTWARE,
                RequestType::ACCOUNT,
                RequestType::OTHER,
            ]),
        );
    }

    #[Test]
    public function test_request_status_contract_is_exactly_hrms_v0_lifecycle(): void
    {
        $this->assertSame(
            ['pending', 'completed', 'rejected'],
            array_map(static fn ($case): string => $case->value, RequestStatus::cases()),
        );
    }

    #[Test]
    public function test_task_status_contract_exposes_documented_business_states(): void
    {
        $values = array_map(static fn ($case): string => $case->value, TaskStatus::cases());

        $this->assertContains('in_progress', $values);
        $this->assertContains('completed', $values);
        $this->assertContains('stopped', $values);
    }

    #[Test]
    public function test_new_tasks_default_to_in_progress(): void
    {
        $employee = $this->createEmployee();

        $task = Task::create([
            'assigned_to' => $employee->id,
            'created_by' => $employee->user_id,
            'title' => 'Default status task',
        ]);

        $this->assertSame('in_progress', $task->fresh()->status->value);
    }
}
