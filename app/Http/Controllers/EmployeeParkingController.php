<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ParkingPermit;
use App\Models\ParkingRequest;
use App\Services\ParkingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeParkingController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $this->employee($request);

        return view('employee.parking', [
            'title' => 'Xe của tôi', 'active' => 'parking', 'employee' => $employee,
            'permit' => ParkingPermit::where('active_employee_id', $employee->id)->first(),
            'pending' => ParkingRequest::where('pending_employee_id', $employee->id)->exists(),
            'requests' => ParkingRequest::with('reviewer')->where('employee_id', $employee->id)->latest()->paginate(10),
            'permits' => ParkingPermit::where('employee_id', $employee->id)->latest()->get(),
        ]);
    }

    public function store(Request $request, ParkingService $parking): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:register,change,stop'],
            'plate' => ['exclude_if:type,stop', 'required', 'string', 'max:25', 'regex:/^[a-zA-Z0-9 .-]+$/'],
            'vehicle_type' => ['exclude_if:type,stop', 'required', 'in:motorbike,car'],
            'vehicle_description' => ['exclude_if:type,stop', 'required', 'string', 'max:150'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        $parking->submit($this->employee($request), $data);

        return redirect()->route('employee.parking')->with('success', 'Đã gửi yêu cầu gửi xe. HR sẽ kiểm tra và xử lý.');
    }

    public function cancel(Request $request, ParkingRequest $parkingRequest, ParkingService $parking): RedirectResponse
    {
        abort_unless($parkingRequest->employee_id === $this->employee($request)->id, 403);
        $parking->cancel($parkingRequest);

        return redirect()->route('employee.parking')->with('success', 'Đã hủy yêu cầu. Xe hiện tại vẫn giữ nguyên.');
    }

    private function employee(Request $request): Employee
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);

        return $employee;
    }
}
