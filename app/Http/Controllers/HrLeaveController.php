<?php

namespace App\Http\Controllers;

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrLeaveController extends Controller
{
    public function __construct(private readonly LeaveService $leaveService) {}

    public function index(Request $request): View
    {
        $year = (int) $request->integer('year', now()->year);
        $leaves = LeaveRequest::query()
            ->with(['employee.department', 'employee.position', 'requester', 'reviewer'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->whereYear('start_date', $year)
            ->latest()
            ->get();

        return view('hr.leave.index', [
            'role' => 'hr', 'active' => 'leave', 'title' => 'Quản lý nghỉ phép',
            'topTitle' => 'Cổng nhân sự', 'topSub' => 'Duyệt và theo dõi nghỉ phép',
            'leaves' => $leaves, 'year' => $year,
            'pendingCount' => $leaves->where('status', LeaveStatus::PENDING)->count(),
            'approvedDays' => $leaves->where('status', LeaveStatus::APPROVED)->sum(fn (LeaveRequest $leave): float => (float) $leave->requested_days),
            'unpaidDays' => $leaves->where('status', LeaveStatus::APPROVED)->sum(fn (LeaveRequest $leave): float => (float) $leave->unpaid_leave_days),
        ]);
    }

    public function show(LeaveRequest $leaveRequest): View
    {
        return view('hr.leave.show', [
            'role' => 'hr', 'active' => 'leave', 'title' => 'Chi tiết đơn nghỉ phép',
            'topTitle' => 'Cổng nhân sự', 'topSub' => 'Duyệt đơn nghỉ phép',
            'leave' => $leaveRequest->load(['employee.department', 'employee.position', 'requester', 'reviewer']),
        ]);
    }

    public function review(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($leaveRequest->status !== LeaveStatus::PENDING) {
            return back()->withErrors(['status' => 'Đơn nghỉ phép này đã được xử lý trước đó.']);
        }

        app(DatabaseManager::class)->transaction(function () use ($request, $leaveRequest, $data): void {
            $leaveRequest->update([
                'status' => $data['status'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_note' => $data['review_note'] ?? null,
                'paid_leave_days' => 0,
                'unpaid_leave_days' => 0,
            ]);

            if ($data['status'] === LeaveStatus::APPROVED->value) {
                $this->leaveService->recalculateYearAllocations($leaveRequest->employee, $leaveRequest->start_date->year);
            }
        });

        return redirect()->route('hr.leave.show', $leaveRequest)->with('success', $data['status'] === 'approved' ? 'Đã duyệt đơn nghỉ phép.' : 'Đã từ chối đơn nghỉ phép.');
    }
}
