<?php

namespace App\Http\Controllers;

use App\Models\ParkingMonthlyCost;
use App\Models\ParkingPermit;
use App\Models\ParkingRequest;
use App\Services\ParkingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HrParkingController extends Controller
{
    public function index(Request $request, ParkingService $parking): View
    {
        $data = $request->validate([
            'month' => ['nullable', 'date_format:Y-m', 'after_or_equal:2000-01', 'before_or_equal:'.now()->format('Y-m')],
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,approved,rejected,cancelled'],
        ]);
        $month = CarbonImmutable::parse(($data['month'] ?? now()->format('Y-m')).'-01');
        $parking->prepareMonth($month->year, $month->month);
        $search = $data['q'] ?? '';
        $matchEmployee = fn ($q) => $q->where('full_name', 'like', '%'.$search.'%')->orWhere('employee_code', 'like', '%'.$search.'%');
        $permits = ParkingPermit::with('employee.department')->whereNotNull('active_employee_id')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('plate', 'like', '%'.strtoupper(preg_replace('/[ .-]/', '', $search)).'%')->orWhereHas('employee', $matchEmployee)))
            ->latest()->paginate(15, ['*'], 'vehicles_page')->withQueryString();
        $requests = ParkingRequest::with('employee.department', 'reviewer', 'permit')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $data['status']))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('plate', 'like', '%'.strtoupper(preg_replace('/[ .-]/', '', $search)).'%')->orWhereHas('employee', $matchEmployee)))
            ->latest()->paginate(10, ['*'], 'requests_page')->withQueryString();
        $costQuery = ParkingMonthlyCost::where('year', $month->year)->where('month', $month->month);

        return view('hr.parking', [
            'title' => 'Quản lý gửi xe', 'active' => 'parking', 'permits' => $permits, 'requests' => $requests,
            'month' => $month, 'activeCount' => ParkingPermit::whereNotNull('active_employee_id')->count(),
            'pendingCount' => ParkingRequest::where('status', 'pending')->count(),
            'totalCost' => (clone $costQuery)->sum('amount'), 'ticketCount' => (clone $costQuery)->count(),
            'costs' => $costQuery->with('employee.department', 'permit')->orderBy('employee_id')->paginate(15, ['*'], 'costs_page')->withQueryString(),
        ]);
    }

    public function review(Request $request, ParkingRequest $parkingRequest, ParkingService $parking): RedirectResponse
    {
        $request->merge(['_parking_request_id' => $parkingRequest->id]);
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'review_note' => ['required_if:status,rejected', 'nullable', 'string', 'max:1000'],
            'monthly_cost' => $request->input('status') === 'approved' && $parkingRequest->type !== 'stop'
                ? ['required', 'numeric', 'min:0', 'max:100000000'] : ['exclude'],
        ], [
            'monthly_cost.required' => 'Vui lòng nhập chi phí công ty trả cho bãi xe mỗi tháng. Nhập 0 nếu bãi xe không thu phí.',
            'monthly_cost.numeric' => 'Chi phí phải là một số tiền hợp lệ.',
            'monthly_cost.min' => 'Chi phí không được nhỏ hơn 0.',
            'review_note.required_if' => 'Vui lòng nhập lý do từ chối yêu cầu.',
        ]);
        $parking->review($parkingRequest, $request->user(), $data);

        return redirect()->route('hr.parking')->with('success', $data['status'] === 'approved' ? 'Đã duyệt yêu cầu gửi xe.' : 'Đã từ chối yêu cầu gửi xe.');
    }

    public function end(Request $request, ParkingPermit $parkingPermit, ParkingService $parking): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        abort_unless($parkingPermit->active_employee_id, 409);
        $parking->endForEmployee($parkingPermit->employee, $request->user(), $data['reason'], $parkingPermit->id);

        return redirect()->route('hr.parking')->with('success', 'Đã kết thúc quyền gửi xe và lưu lịch sử.');
    }
}
