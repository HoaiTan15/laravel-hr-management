<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Employee;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HrTaskController extends Controller
{
    public function create(): View
    {
        return view('hr.tasks.form', [
            'task' => new Task(['status' => TaskStatus::PENDING]),
            'employees' => Employee::with('department')->where('employment_status', 'active')->orderBy('full_name')->get(),
            'formAction' => route('hr.tasks.store'),
            'formMethod' => 'POST',
            'title' => 'Giao công việc',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Task::create([
            ...$this->validated($request),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('hr.tasks')->with('success', 'Đã giao công việc cho nhân viên.');
    }

    public function show(Task $task): View
    {
        $task->load('assignee.department', 'assignee.position', 'creator');

        return view('hr.tasks.show', [
            'task' => $task,
            'title' => 'Chi tiết công việc',
            'topTitle' => 'Cổng nhân sự',
            'topSub' => 'Theo dõi công việc',
            'active' => 'tasks',
        ]);
    }

    public function edit(Task $task): View
    {
        $task->load('assignee');

        return view('hr.tasks.form', [
            'task' => $task,
            'employees' => Employee::with('department')->where('employment_status', 'active')->orWhereKey($task->assigned_to)->orderBy('full_name')->get(),
            'formAction' => route('hr.tasks.update', $task),
            'formMethod' => 'PATCH',
            'title' => 'Cập nhật công việc',
        ]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $task->update($this->validated($request));

        return redirect()->route('hr.tasks')->with('success', 'Đã cập nhật công việc.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()->route('hr.tasks')->with('success', 'Đã xóa công việc.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'assigned_to' => ['required', 'exists:employees,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(array_column(TaskStatus::cases(), 'value'))],
            'due_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
