<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'date', 'clock_in', 'clock_in_lat', 'clock_in_lng', 'clock_in_address',
        'clock_in_accuracy', 'clock_in_distance', 'clock_in_is_mock',
        'clock_out', 'clock_out_lat', 'clock_out_lng', 'clock_out_address',
        'clock_out_accuracy', 'clock_out_distance', 'clock_out_is_mock',
        'is_wfh', 'location_flagged', 'location_flag_reason',
        'total_work_hours', 'is_manually_edited', 'edited_by', 'edit_reason', 'edited_at',
        'is_late', 'is_early_leave', 'late_minutes', 'early_leave_minutes'
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'edited_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakRecord::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function getFormattedWorkHoursAttribute()
    {
        $workHours = $this->total_work_hours;

        // If no stored value but clock_in exists, calculate on-the-fly
        if (!$workHours && $this->clock_in) {
            $end = $this->clock_out ?? now('Asia/Kuala_Lumpur');
            $totalMinutes = ($end->timestamp - $this->clock_in->timestamp) / 60;
            $breakMinutes = $this->breaks()->whereNotNull('break_out')->sum('duration_minutes');
            // Subtract active break time too
            $activeBreak = $this->breaks()->whereNull('break_out')->first();
            if ($activeBreak) {
                $breakMinutes += now('Asia/Kuala_Lumpur')->diffInMinutes($activeBreak->break_in);
            }
            $workHours = max(0, ($totalMinutes - $breakMinutes) / 60);
        }

        if (!$workHours) {
            return 'N/A';
        }

        $totalMinutes = round($workHours * 60);
        $hours = intdiv((int) $totalMinutes, 60);
        $minutes = (int) $totalMinutes % 60;

        return "{$hours}h {$minutes}m";
    }
}
