<?php

namespace App\Http\Controllers;

use App\Enums\RequestType;
use App\Models\Request as EmployeeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeRequestController extends Controller
{
    public function index(Request $request): View
    {
        return view('employee-requests', ['role' => 'employee', 'active' => 'requests', 'title' => 'Phiếu yêu cầu', 'topTitle' => 'Dashboard cá nhân', 'topSub' => 'Phiếu yêu cầu', 'requests' => EmployeeRequest::with('processor')->where('created_by', $request->user()->id)->latest()->get()]);
    }

    public function create(Request $request): View
    {
        return view('employee-request-form', ['role' => 'employee', 'active' => 'requests', 'title' => 'Tạo phiếu yêu cầu', 'topTitle' => 'Dashboard cá nhân', 'topSub' => 'Tạo phiếu yêu cầu', 'requestModel' => null, 'defaultType' => $request->string('type')->value()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $record = EmployeeRequest::create([
            'created_by' => $request->user()->id,
            'type' => $data['type'],
            'payload' => ['title' => $data['title'], 'content' => $data['content']],
            'status' => 'pending',
        ]);
        return redirect()->route('employee.requests.show', $record)->with('status', 'Đã gửi phiếu yêu cầu.');
    }

    public function show(Request $request, EmployeeRequest $requestModel): View
    {
        $this->authorizeOwner($request, $requestModel);
        return view('employee-request-show', ['role' => 'employee', 'active' => 'requests', 'title' => 'Chi tiết phiếu yêu cầu', 'topTitle' => 'Dashboard cá nhân', 'topSub' => 'Chi tiết PYC', 'requestModel' => $requestModel->load('processor')]);
    }

    public function edit(Request $request, EmployeeRequest $requestModel): View
    {
        $this->authorizePending($request, $requestModel);
        if ($requestModel->type === RequestType::PROFILE_CHANGE) {
            abort(404);
        }
        return view('employee-request-form', ['role' => 'employee', 'active' => 'requests', 'title' => 'Chỉnh sửa phiếu yêu cầu', 'topTitle' => 'Dashboard cá nhân', 'topSub' => 'Chỉnh sửa PYC', 'requestModel' => $requestModel, 'defaultType' => $requestModel->type->value]);
    }

    public function update(Request $request, EmployeeRequest $requestModel): RedirectResponse
    {
        $this->authorizePending($request, $requestModel);
        $data = $this->validated($request);
        $requestModel->update(['type' => $data['type'], 'payload' => ['title' => $data['title'], 'content' => $data['content']]]);
        return redirect()->route('employee.requests.show', $requestModel)->with('status', 'Đã cập nhật phiếu yêu cầu.');
    }

    public function destroy(Request $request, EmployeeRequest $requestModel): RedirectResponse
    {
        $this->authorizePending($request, $requestModel);
        $requestModel->delete();
        return redirect()->route('employee.requests.index')->with('status', 'Đã hủy phiếu yêu cầu.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:hardware,software,account,other'],
            'title' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string', 'max:5000'],
        ]);
    }

    private function authorizeOwner(Request $request, EmployeeRequest $record): void
    {
        abort_unless($record->created_by === $request->user()->id, 403);
    }

    private function authorizePending(Request $request, EmployeeRequest $record): void
    {
        $this->authorizeOwner($request, $record);
        abort_unless($record->status->value === 'pending', 403);
    }
}
