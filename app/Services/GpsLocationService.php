<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

/**
 * Server-side GPS validation for attendance check-in/check-out.
 * The client only reports coordinates + accuracy; the server decides validity.
 */
class GpsLocationService
{
    /** Compute great-circle distance in meters between two coordinates (Haversine). */
    public static function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earthRadius * asin(sqrt($a));
    }

    /** Workplace coordinates from config (.env). */
    public function workplace(): array
    {
        return [
            'latitude' => (float) Config::get('app.attendance_gps.workplace_latitude'),
            'longitude' => (float) Config::get('app.attendance_gps.workplace_longitude'),
            'radius_meters' => (float) Config::get('app.attendance_gps.radius_meters'),
            'max_accuracy_meters' => (float) Config::get('app.attendance_gps.max_accuracy_meters'),
        ];
    }

    /**
     * Validate a raw GPS reading against the workplace geofence.
     * Returns validated, normalized coordinates ready to persist.
     *
     * @throws ValidationException when the reading is unusable.
     */
    public function validateOrFail(?string $latitude, ?string $longitude, ?string $accuracy): array
    {
        if ($latitude === null || $longitude === null || $accuracy === null
            || ! is_numeric($latitude) || ! is_numeric($longitude) || ! is_numeric($accuracy)) {
            throw ValidationException::withMessages([
                'gps' => 'Không lấy được vị trí GPS. Vui lòng cho phép truy cập vị trí và thử lại.',
            ]);
        }

        $lat = (float) $latitude;
        $lng = (float) $longitude;
        $acc = (float) $accuracy;

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            throw ValidationException::withMessages([
                'gps' => 'Tọa độ GPS không hợp lệ.',
            ]);
        }

        if ($acc < 0) {
            throw ValidationException::withMessages([
                'gps' => 'Giá trị độ chính xác GPS không hợp lệ.',
            ]);
        }

        $workplace = $this->workplace();

        if ($acc > $workplace['max_accuracy_meters']) {
            throw ValidationException::withMessages([
                'gps' => sprintf(
                    'Độ chính xác GPS (%.0fm) vượt quá ngưỡng cho phép (%.0fm). Di chuyển ra nơi có tín hiệu GPS tốt hơn.',
                    $acc,
                    $workplace['max_accuracy_meters']
                ),
            ]);
        }

        $distance = self::distanceMeters($lat, $lng, $workplace['latitude'], $workplace['longitude']);

        if ($distance > $workplace['radius_meters']) {
            throw ValidationException::withMessages([
                'gps' => sprintf(
                    'Bạn cách nơi làm việc khoảng %.0fm, vượt quá bán kính cho phép %.0fm. Không thể chấm công từ ngoài phạm vi.',
                    $distance,
                    $workplace['radius_meters']
                ),
            ]);
        }

        return [
            'latitude' => round($lat, 7),
            'longitude' => round($lng, 7),
            'accuracy' => round($acc, 2),
            'distance_meters' => round($distance, 2),
        ];
    }
}
