<?php

namespace App\Http\Controllers;

use App\Services\GpsLocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAttendanceLocationController extends Controller
{
    public function index(GpsLocationService $gps): View
    {
        return view('admin.attendance-location', [
            'title' => 'Tọa độ chấm công',
            'location' => $gps->workplace(),
            'useServerDefaults' => (bool) (DB::table('attendance_locations')->find(1)?->use_server_defaults ?? true),
            'defaults' => config('app.attendance_gps'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', 'in:server,custom'],
            'coordinates' => ['exclude_unless:mode,custom', 'required', 'string', 'max:100'],
            'radius_meters' => ['exclude_unless:mode,custom', 'required', 'integer', 'between:1,100000'],
            'max_accuracy_meters' => ['exclude_unless:mode,custom', 'required', 'integer', 'between:1,10000'],
        ]);
        $latitude = $longitude = null;
        if ($data['mode'] === 'custom') {
            $number = '[+-]?(?:\d+(?:\.\d+)?|\.\d+)';
            if (! preg_match('/^\s*('.$number.')(?:\s*,\s*|\s+)('.$number.')\s*$/D', $data['coordinates'], $matches)
                || abs((float) $matches[1]) > 90 || abs((float) $matches[2]) > 180) {
                throw ValidationException::withMessages(['coordinates' => 'Nhập vĩ độ, kinh độ hợp lệ, ví dụ: 10.762622, 106.660172.']);
            }
            $latitude = (float) $matches[1];
            $longitude = (float) $matches[2];
        }

        DB::table('attendance_locations')->updateOrInsert(['id' => 1], [
            'use_server_defaults' => $data['mode'] === 'server',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius_meters' => $data['mode'] === 'custom' ? $data['radius_meters'] : null,
            'max_accuracy_meters' => $data['mode'] === 'custom' ? $data['max_accuracy_meters'] : null,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.attendance-location')->with('success', 'Đã lưu tọa độ chấm công. Cấu hình áp dụng cho check-in và check-out.');
    }
}
