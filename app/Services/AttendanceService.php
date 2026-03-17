<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Models\AuditLog;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceService
{
    protected $locationService;
    protected $geofenceService;

    public function __construct(GoogleLocationService $locationService, GeofenceService $geofenceService)
    {
        $this->locationService = $locationService;
        $this->geofenceService = $geofenceService;
    }

    public function clockIn($lat, $lng, $accuracy = null, $isMock = false, $isWfh = false)
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Kuala_Lumpur')->toDateString();

        // Check if already clocked in today
        $existing = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        if ($existing && $existing->clock_in) {
            throw new \Exception('Already clocked in today');
        }

        // Geofence validation
        $geofenceResult = $this->geofenceService->validateLocation($lat, $lng, $accuracy, $isMock, $isWfh);
        if (!$geofenceResult['allowed']) {
            throw new \Exception($geofenceResult['flag_reason']);
        }

        // Get location data (optional)
        $locationData = null;
        if ($lat !== null && $lng !== null) {
            $locationData = $this->locationService->getLocationData($lat, $lng);
        }

        $data = [
            'clock_in' => Carbon::now('Asia/Kuala_Lumpur'),
            'clock_in_lat' => $lat,
            'clock_in_lng' => $lng,
            'clock_in_address' => $locationData ? $locationData['address'] : null,
            'clock_in_accuracy' => $accuracy,
            'clock_in_distance' => $geofenceResult['distance'],
            'clock_in_is_mock' => $isMock,
            'is_wfh' => $isWfh,
            'location_flagged' => !empty($geofenceResult['flags']),
            'location_flag_reason' => !empty($geofenceResult['flags']) ? $geofenceResult['flag_reason'] : null,
        ];

        // Check for late clock-in
        $workingHours = WorkingHour::getForUser($user->id);
        if ($workingHours) {
            $clockInTime = Carbon::now('Asia/Kuala_Lumpur');
            $scheduledStart = Carbon::parse($today . ' ' . $workingHours->work_start, 'Asia/Kuala_Lumpur');
            $threshold = $workingHours->late_threshold_minutes;
            $lateMinutes = max(0, $clockInTime->diffInMinutes($scheduledStart, false) * -1);
            if ($lateMinutes > $threshold) {
                $data['is_late'] = true;
                $data['late_minutes'] = $lateMinutes;
            }
        }

        if ($existing) {
            $existing->update($data);
            return $existing;
        } else {
            return Attendance::create(array_merge([
                'user_id' => $user->id,
                'date' => $today,
            ], $data));
        }
    }

    public function clockOut($lat, $lng, $accuracy = null, $isMock = false)
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Kuala_Lumpur')->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        if (!$attendance || !$attendance->clock_in) {
            throw new \Exception('Must clock in first');
        }

        if ($attendance->clock_out) {
            throw new \Exception('Already clocked out today');
        }

        // Geofence validation (WFH status carries from clock-in)
        $geofenceResult = $this->geofenceService->validateLocation($lat, $lng, $accuracy, $isMock, $attendance->is_wfh);
        if (!$geofenceResult['allowed']) {
            throw new \Exception($geofenceResult['flag_reason']);
        }

        // Get location data (optional)
        $locationData = null;
        if ($lat !== null && $lng !== null) {
            $locationData = $this->locationService->getLocationData($lat, $lng);
        }

        $clockOut = Carbon::now('Asia/Kuala_Lumpur');
        $totalMinutes = ($clockOut->timestamp - $attendance->clock_in->timestamp) / 60;
        $totalHours = max(0, round($totalMinutes / 60, 2) - $this->getTotalBreakHours($attendance));

        $updateData = [
            'clock_out' => $clockOut,
            'clock_out_lat' => $lat,
            'clock_out_lng' => $lng,
            'clock_out_address' => $locationData ? $locationData['address'] : null,
            'clock_out_accuracy' => $accuracy,
            'clock_out_distance' => $geofenceResult['distance'],
            'clock_out_is_mock' => $isMock,
            'total_work_hours' => $totalHours,
        ];

        // Update flags if clock-out has flags
        if (!empty($geofenceResult['flags'])) {
            $updateData['location_flagged'] = true;
            $existingReason = $attendance->location_flag_reason;
            $updateData['location_flag_reason'] = $existingReason
                ? $existingReason . ' | Clock-out: ' . $geofenceResult['flag_reason']
                : 'Clock-out: ' . $geofenceResult['flag_reason'];
        }

        // Check for early leave
        $workingHours = WorkingHour::getForUser($user->id);
        if ($workingHours) {
            $scheduledEnd = Carbon::parse($today . ' ' . $workingHours->work_end, 'Asia/Kuala_Lumpur');
            $threshold = $workingHours->early_leave_threshold_minutes;
            $earlyMinutes = max(0, $scheduledEnd->diffInMinutes($clockOut, false));
            if ($earlyMinutes > $threshold) {
                $updateData['is_early_leave'] = true;
                $updateData['early_leave_minutes'] = $earlyMinutes;
            }
        }

        $attendance->update($updateData);

        return $attendance;
    }

    public function breakIn($lat, $lng)
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Kuala_Lumpur')->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        if (!$attendance || !$attendance->clock_in) {
            throw new \Exception('Must be clocked in to take break');
        }

        // Get location data (optional)
        $locationData = null;
        if ($lat !== null && $lng !== null) {
            $locationData = $this->locationService->getLocationData($lat, $lng);
        }

        return BreakRecord::create([
            'attendance_id' => $attendance->id,
            'break_in' => Carbon::now('Asia/Kuala_Lumpur'),
            'break_in_lat' => $lat,
            'break_in_lng' => $lng,
            'break_in_address' => $locationData ? $locationData['address'] : null,
        ]);
    }

    public function breakOut($lat, $lng, $breakId)
    {
        try {
            $user = Auth::user();
            $break = BreakRecord::findOrFail($breakId);

            if ($break->attendance->user_id !== $user->id) {
                throw new \Exception('Unauthorized');
            }

            if ($break->break_out) {
                throw new \Exception('Break already ended');
            }

            // Get location data (optional)
            $locationData = null;
            if ($lat !== null && $lng !== null) {
                $locationData = $this->locationService->getLocationData($lat, $lng);
            }

            $breakOut = Carbon::now('Asia/Kuala_Lumpur');
            $duration = $breakOut->diffInMinutes($break->break_in);

            $break->update([
                'break_out' => $breakOut,
                'break_out_lat' => $lat,
                'break_out_lng' => $lng,
                'break_out_address' => $locationData ? $locationData['address'] : null,
                'duration_minutes' => $duration,
            ]);

            // Recalculate total work hours
            $this->recalculateTotalHours($break->attendance);

            return $break;
        } catch (\Exception $e) {
            \Log::error('Error in breakOut: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getTotalBreakHours($attendance)
    {
        try {
            return $attendance->breaks()->whereNotNull('break_out')->sum('duration_minutes') / 60;
        } catch (\Exception $e) {
            \Log::error('Error calculating total break hours: ' . $e->getMessage());
            return 0;
        }
    }

    private function recalculateTotalHours($attendance)
    {
        if ($attendance->clock_out) {
            $totalBreakHours = $this->getTotalBreakHours($attendance);
            $totalMinutes = ($attendance->clock_out->timestamp - $attendance->clock_in->timestamp) / 60;
            $totalHours = max(0, round($totalMinutes / 60, 2) - $totalBreakHours);
            $attendance->update(['total_work_hours' => $totalHours]);
        }
    }

    public function editAttendance($attendanceId, $data, $reason, $document = null)
    {
        $user = Auth::user();
        $attendance = Attendance::findOrFail($attendanceId);

        $oldValues = $attendance->only(['clock_in', 'clock_out', 'total_work_hours']);

        $attendance->update(array_merge($data, [
            'is_manually_edited' => true,
            'edited_by' => $user->id,
            'edit_reason' => $reason,
            'edited_at' => Carbon::now('Asia/Kuala_Lumpur'),
        ]));

        // Log audit
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'edit_attendance',
            'model' => 'Attendance',
            'model_id' => $attendance->id,
            'old_values' => $oldValues,
            'new_values' => $attendance->only(['clock_in', 'clock_out', 'total_work_hours']),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if ($document) {
            // Save document
            $path = $document->store('attendance_edits', 'public');
            \App\Models\Document::create([
                'user_id' => $attendance->user_id,
                'type' => 'Attendance Edit',
                'path' => $path,
                'original_name' => $document->getClientOriginalName(),
                'mime_type' => $document->getMimeType(),
                'size' => $document->getSize(),
            ]);
        }

        return $attendance;
    }
}
