<?php

namespace App\Http\Controllers;

use App\Enums\RequestType;
use App\Models\Request as EmployeeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeProfileController extends Controller
{
    public function show(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);
        $employee->load('department', 'position', 'user');
        $pendingProfileChange = EmployeeRequest::where('created_by', $request->user()->id)
            ->where('type', RequestType::PROFILE_CHANGE)
            ->where('status', 'pending')
            ->latest()
            ->first();
        $profileChangeRequest = $request->filled('change_request')
            ? EmployeeRequest::whereKey($request->integer('change_request'))
                ->where('created_by', $request->user()->id)
                ->where('type', RequestType::PROFILE_CHANGE)
                ->where('status', 'pending')
                ->first()
            : null;

        return view('employee-profile', [
            'role' => 'employee', 'active' => 'profile', 'title' => 'Hồ sơ cá nhân',
            'topTitle' => 'Dashboard cá nhân', 'topSub' => 'Hồ sơ cá nhân', 'employee' => $employee,
            'pendingProfileChange' => $pendingProfileChange,
            'profileChangeRequest' => $profileChangeRequest,
            'showChangeRequest' => $request->boolean('change'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);

        $data = $request->validate([
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'avatar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'employee_code' => ['prohibited'],
            'role' => ['prohibited'],
            'department_id' => ['prohibited'],
            'position_id' => ['prohibited'],
            'hire_date' => ['prohibited'],
            'employment_status' => ['prohibited'],
            'date_of_birth' => ['prohibited'],
            'gender' => ['prohibited'],
            'cccd' => ['prohibited'],
        ]);

        $employee->update(collect($data)->only(['phone', 'email', 'address', 'avatar'])->all());

        return redirect()->route('employee.profile')->with('status', 'Đã cập nhật thông tin liên hệ.');
    }

    public function requestChange(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);
        $data = $request->validate([
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'cccd' => ['nullable', 'string', 'max:20'],
            'reason' => ['required', 'string', 'max:300'],
        ]);

        $changes = collect($data)->except('reason')->filter(fn ($value) => filled($value))->all();

        if ($changes === []) {
            return back()->withInput()->withErrors([
                'profile_change' => 'Vui lòng nhập ít nhất một thông tin cần thay đổi.',
            ]);
        }

        EmployeeRequest::create([
            'created_by' => $request->user()->id,
            'type' => RequestType::PROFILE_CHANGE,
            'payload' => ['changes' => $changes, 'reason' => $data['reason']],
            'status' => 'pending',
        ]);

        return redirect()->route('employee.profile')->with('status', 'Đã gửi yêu cầu thay đổi hồ sơ.');
    }

    public function updateChangeRequest(Request $request, EmployeeRequest $requestModel): RedirectResponse
    {
        abort_unless($requestModel->created_by === $request->user()->id, 403);
        abort_unless($requestModel->type === RequestType::PROFILE_CHANGE, 403);
        abort_unless($requestModel->status->value === 'pending', 403);

        $data = $request->validate([
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'cccd' => ['nullable', 'string', 'max:20'],
            'reason' => ['required', 'string', 'max:300'],
        ]);
        $changes = collect($data)->except('reason')->filter(fn ($value) => filled($value))->all();

        if ($changes === []) {
            return back()->withInput()->withErrors([
                'profile_change' => 'Vui lòng nhập ít nhất một thông tin cần thay đổi.',
            ]);
        }

        $requestModel->update([
            'payload' => ['changes' => $changes, 'reason' => $data['reason']],
        ]);

        return redirect()->route('employee.profile')->with('status', 'Đã cập nhật yêu cầu thay đổi hồ sơ.');
    }
}
