<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Models\Request as EmployeeRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrRequestController extends Controller
{
    public function show(EmployeeRequest $requestModel): View
    {
        $requestModel->load('creator', 'processor');

        return view('hr.requests.show', [
            'requestModel' => $requestModel,
            'title' => 'Chi tiết phiếu yêu cầu',
            'topTitle' => 'Cổng nhân sự',
            'topSub' => 'Chi tiết phiếu yêu cầu',
            'active' => 'requests',
        ]);
    }

    public function index(Request $request): View
    {
        $records = EmployeeRequest::with('creator')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->latest()
            ->get();

        return view('ui.module', [
            'role' => 'hr', 'active' => 'requests', 'title' => 'Phiếu yêu cầu',
            'description' => 'Theo dõi các yêu cầu do nhân sự gửi; việc phê duyệt PYC thuộc quy trình Admin.',
            'sectionTitle' => 'Phiếu yêu cầu', 'icon' => 'receipt_long',
            'requests' => $records,
            'pendingRequests' => $records->where('status', RequestStatus::PENDING)->count(),
            'completedRequests' => $records->where('status', RequestStatus::COMPLETED)->count(),
            'rejectedRequests' => $records->where('status', RequestStatus::REJECTED)->count(),
            'averageProcessing' => '—',
        ]);
    }
}
