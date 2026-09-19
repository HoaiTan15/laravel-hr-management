<?php

namespace App\Http\Controllers;

use App\Models\Request as EmployeeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRequestController extends Controller
{
    public function index(): View
    {
        return view('admin-requests', ['role' => 'admin', 'active' => 'requests', 'title' => 'Danh sách PYC', 'topTitle' => 'Dashboard Admin', 'topSub' => 'Danh sách phiếu yêu cầu', 'requests' => EmployeeRequest::with('creator', 'processor')->latest()->get()]);
    }

    public function show(EmployeeRequest $requestModel): View
    {
        return view('admin-request-show', ['role' => 'admin', 'active' => 'requests', 'title' => 'Xử lý PYC', 'topTitle' => 'Dashboard Admin', 'topSub' => 'Xử lý phiếu yêu cầu', 'requestModel' => $requestModel->load('creator', 'processor')]);
    }

    public function process(Request $request, EmployeeRequest $requestModel): RedirectResponse
    {
        abort_unless($requestModel->status->value === 'pending', 409);
        $data = $request->validate(['status' => ['required', 'in:completed,rejected'], 'processing_note' => ['required', 'string', 'max:1000']]);
        $requestModel->update(['status' => $data['status'], 'processed_by' => $request->user()->id, 'processed_at' => now(), 'processing_note' => $data['processing_note']]);
        return redirect()->route('admin.requests.show', $requestModel)->with('status', 'Đã xử lý phiếu yêu cầu.');
    }
}
