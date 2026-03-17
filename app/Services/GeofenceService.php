<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\OfficeLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class GeofenceService
{
    /**
     * Validate location for clock in/out.
     * Returns array with 'allowed' (bool), 'distance' (float|null), 'flags' (array).
     */
    public function validateLocation(
        ?float $lat,
        ?float $lng,
        ?float $accuracy = null,
        bool $isMock = false,
        bool $isWfh = false
    ): array {
        $result = [
            'allowed' => true,
            'distance' => null,
            'nearest_office' => null,
            'flags' => [],
            'flag_reason' => null,
        ];

        // If geofence is disabled, always allow
        if (!config('geofence.enabled')) {
            return $result;
        }

        // WFH bypass - allow but still record location data if provided
        if ($isWfh) {
            $result['allowed'] = true;
            if ($lat !== null && $lng !== null) {
                $nearest = $this->findNearestOffice($lat, $lng);
                $result['distance'] = $nearest['distance'];
                $result['nearest_office'] = $nearest['office'];
            }
            return $result;
        }

        // Location is required when geofence is enabled and not WFH
        if ($lat === null || $lng === null) {
            $result['allowed'] = false;
            $result['flag_reason'] = 'Location data is required for clock in/out.';
            return $result;
        }

        // Anti-fake GPS: check mock location
        if ($isMock && config('geofence.anti_fake.reject_mock')) {
            $result['allowed'] = false;
            $result['flags'][] = 'mock_location';
            $result['flag_reason'] = 'Mock/fake location detected. Please disable any GPS spoofing apps.';
            return $result;
        }

        // Anti-fake GPS: check accuracy
        if ($accuracy !== null) {
            $maxAccuracy = config('geofence.max_accuracy', 100);
            if ($accuracy > $maxAccuracy) {
                $result['allowed'] = false;
                $result['flags'][] = 'low_accuracy';
                $result['flag_reason'] = "GPS accuracy too low ({$accuracy}m). Please try again in an open area.";
                return $result;
            }

            // Suspiciously perfect accuracy (potential fake GPS)
            $suspiciousThreshold = config('geofence.anti_fake.suspicious_accuracy_threshold', 1);
            if ($accuracy < $suspiciousThreshold) {
                $result['flags'][] = 'suspicious_accuracy';
            }
        }

        // Check distance from nearest office
        $nearest = $this->findNearestOffice($lat, $lng);
        $result['distance'] = $nearest['distance'];
        $result['nearest_office'] = $nearest['office'];

        $allowedRadius = $nearest['radius'];
        if ($result['distance'] > $allowedRadius) {
            $result['allowed'] = false;
            $result['flag_reason'] = "You are " . round($result['distance']) . "m from the nearest office ({$nearest['office']}). Maximum allowed distance is {$allowedRadius}m.";
            return $result;
        }

        // Anti-fake GPS: check speed from last clock event
        $speedCheck = $this->checkSpeedAnomaly($lat, $lng);
        if ($speedCheck['flagged']) {
            $result['flags'][] = 'speed_anomaly';
            $result['flag_reason'] = $speedCheck['reason'];
            // Flag but still allow - admin can review
            $result['allowed'] = true;
        }

        // Combine flags
        if (!empty($result['flags']) && $result['flag_reason'] === null) {
            $result['flag_reason'] = implode(', ', $result['flags']);
        }

        return $result;
    }

    /**
     * Calculate distance between two points using Haversine formula.
     * Returns distance in meters.
     */
    public function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2)
            + cos($lat1Rad) * cos($lat2Rad)
            * sin($deltaLng / 2) * sin($deltaLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Find the nearest active office and return distance + office info.
     */
    public function findNearestOffice(float $lat, float $lng): array
    {
        // Cache active offices for 10 minutes to avoid DB query on every clock action
        $offices = Cache::remember('active_office_locations', 600, function () {
            return OfficeLocation::where('is_active', true)->get();
        });

        if ($offices->isEmpty()) {
            // Fall back to config
            $configOffices = config('geofence.offices', []);
            $defaultRadius = config('geofence.radius', 200);

            if (empty($configOffices)) {
                return ['distance' => 0, 'office' => 'No office configured', 'radius' => $defaultRadius];
            }

            $nearest = null;
            $minDistance = PHP_FLOAT_MAX;

            foreach ($configOffices as $office) {
                $distance = $this->haversineDistance($lat, $lng, $office['lat'], $office['lng']);
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearest = $office;
                }
            }

            return [
                'distance' => $minDistance,
                'office' => $nearest['name'] ?? 'Unknown Office',
                'radius' => $defaultRadius,
            ];
        }

        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;
        $nearestRadius = config('geofence.radius', 200);

        foreach ($offices as $office) {
            $distance = $this->haversineDistance($lat, $lng, $office->latitude, $office->longitude);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $office;
                $nearestRadius = $office->radius;
            }
        }

        return [
            'distance' => $minDistance,
            'office' => $nearest->name,
            'radius' => $nearestRadius,
        ];
    }

    /**
     * Check for speed anomalies between consecutive location events.
     * Detects impossibly fast movement that suggests GPS spoofing.
     */
    private function checkSpeedAnomaly(float $lat, float $lng): array
    {
        $user = Auth::user();
        $maxSpeedKmh = config('geofence.anti_fake.max_speed_kmh', 200);

        // Get the last attendance record with location
        $lastAttendance = Attendance::where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereNotNull('clock_in_lat')
                    ->orWhereNotNull('clock_out_lat');
            })
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$lastAttendance) {
            return ['flagged' => false];
        }

        // Use the most recent location (clock_out if available, otherwise clock_in)
        $lastLat = $lastAttendance->clock_out_lat ?? $lastAttendance->clock_in_lat;
        $lastLng = $lastAttendance->clock_out_lng ?? $lastAttendance->clock_in_lng;
        $lastTime = $lastAttendance->clock_out ?? $lastAttendance->clock_in;

        if ($lastLat === null || $lastLng === null || $lastTime === null) {
            return ['flagged' => false];
        }

        $distance = $this->haversineDistance($lastLat, $lastLng, $lat, $lng);
        $timeDiffHours = Carbon::now('Asia/Kuala_Lumpur')->diffInSeconds($lastTime) / 3600;

        // Avoid division by zero (if less than 10 seconds apart)
        if ($timeDiffHours < (10 / 3600)) {
            return ['flagged' => false];
        }

        $speedKmh = ($distance / 1000) / $timeDiffHours;

        if ($speedKmh > $maxSpeedKmh) {
            return [
                'flagged' => true,
                'reason' => "Suspicious movement speed detected (" . round($speedKmh) . " km/h over " . round($distance) . "m).",
            ];
        }

        return ['flagged' => false];
    }
}
