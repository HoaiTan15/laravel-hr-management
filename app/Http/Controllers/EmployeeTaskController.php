<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeTaskController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:in_progress,completed,stopped'],
            'due_from' => ['nullable', 'date'],
            'due_to' => ['nullable', 'date', 'after_or_equal:due_from'],
        ]);

        $taskQuery = $employee->tasks()->with('creator');

        $taskQuery
            ->when($filters['q'] ?? null, fn ($query, $value) => $query->where('title', 'like', "%{$value}%"))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['due_from'] ?? null, fn ($query, $value) => $query->whereDate('due_at', '>=', $value))
            ->when($filters['due_to'] ?? null, fn ($query, $value) => $query->whereDate('due_at', '<=', $value));

        return view('employee-tasks', [
            'role' => 'employee', 'active' => 'tasks', 'title' => 'Danh sách công việc',
            'topTitle' => 'Dashboard cá nhân', 'topSub' => 'Danh sách công việc',
            'tasks' => $taskQuery->orderBy('due_at')->paginate(10)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Task $task): View
    {
        $this->authorizeTask($request, $task);
        $task->load('creator', 'assignee');

        return view('employee-task-show', [
            'role' => 'employee', 'active' => 'tasks', 'title' => 'Chi tiết công việc',
            'topTitle' => 'Dashboard cá nhân', 'topSub' => 'Chi tiết công việc', 'task' => $task,
        ]);
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($request, $task);
        $data = $request->validate([
            'status' => ['required', 'in:in_progress,completed,stopped'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $task->update($data);

        return redirect()->route('employee.tasks.show', $task)->with('status', 'Đã cập nhật tiến độ công việc.');
    }

    private function authorizeTask(Request $request, Task $task): void
    {
        abort_unless($request->user()->employee?->id === $task->assigned_to, 403);
    }
}
