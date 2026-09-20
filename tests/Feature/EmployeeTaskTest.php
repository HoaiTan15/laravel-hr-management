<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\Concerns\CreatesEmployee;
use Tests\TestCase;

class EmployeeTaskTest extends TestCase
{
    use CreatesEmployee;
    use RefreshDatabase;

    public function test_employee_can_filter_own_tasks_and_save_status_and_notes(): void
    {
        $employee = $this->createEmployee();
        $other = $this->createEmployee();
        $this->checkIn($employee);
        Task::create(['assigned_to' => $employee->id, 'created_by' => $other->user_id, 'title' => 'Prepare report', 'status' => 'in_progress']);
        Task::create(['assigned_to' => $employee->id, 'created_by' => $other->user_id, 'title' => 'Ignore completed', 'status' => 'completed']);
        Task::create(['assigned_to' => $other->id, 'created_by' => $other->user_id, 'title' => 'Other report', 'status' => 'in_progress']);

        $this->actingAs($employee->user)->get(route('employee.tasks.index', ['q' => 'Prepare', 'status' => 'in_progress']))
            ->assertOk()
            ->assertSee('Prepare report')
            ->assertDontSee('Other report')
            ->assertDontSee('Ignore completed');

        $task = Task::where('title', 'Prepare report')->firstOrFail();
        $this->actingAs($employee->user)->put(route('employee.tasks.status', $task), [
            'status' => 'in_progress',
            'notes' => 'Started review',
        ])->assertRedirect(route('employee.tasks.show', $task));

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'in_progress', 'notes' => 'Started review']);
    }

    public function test_employee_can_update_stopped_status_and_notes(): void
    {
        $employee = $this->createEmployee();
        $this->checkIn($employee);
        $task = Task::create([
            'assigned_to' => $employee->id,
            'created_by' => $employee->user_id,
            'title' => 'Document process',
            'description' => 'Write the process description.',
            'status' => 'in_progress',
            'due_at' => now()->addDay(),
        ]);

        $this->actingAs($employee->user)
            ->put(route('employee.tasks.status', $task), [
                'status' => 'stopped',
                'notes' => 'Stopped by current instruction.',
            ])
            ->assertRedirect(route('employee.tasks.show', $task));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'stopped',
            'notes' => 'Stopped by current instruction.',
        ]);
    }

    public function test_employee_task_list_is_paginated(): void
    {
        $employee = $this->createEmployee();
        $this->checkIn($employee);

        for ($index = 1; $index <= 11; $index++) {
            Task::create([
                'assigned_to' => $employee->id,
                'created_by' => $employee->user_id,
                'title' => "Task {$index}",
                'status' => 'in_progress',
            ]);
        }

        $this->actingAs($employee->user)
            ->get(route('employee.tasks.index'))
            ->assertViewHas('tasks', fn ($tasks): bool => $tasks instanceof LengthAwarePaginator && $tasks->perPage() === 10);
    }

    public function test_employee_cannot_update_another_employees_task(): void
    {
        $employee = $this->createEmployee();
        $other = $this->createEmployee();
        $this->checkIn($employee);
        $task = Task::create(['assigned_to' => $other->id, 'created_by' => $other->user_id, 'title' => 'Other task', 'status' => 'in_progress']);

        $this->actingAs($employee->user)
            ->put(route('employee.tasks.status', $task), ['status' => 'completed', 'notes' => 'No access'])
            ->assertForbidden();
    }

    private function checkIn(Employee $employee): void
    {
        Attendance::create(['employee_id' => $employee->id, 'work_date' => today(), 'check_in_at' => now()]);
    }
}
