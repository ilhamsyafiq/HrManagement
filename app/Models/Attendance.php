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
        if (!$this->total_work_hours) {
            return 'N/A';
        }

        $totalMinutes = round($this->total_work_hours * 60);
        $hours = intdiv((int) $totalMinutes, 60);
        $minutes = (int) $totalMinutes % 60;

        return "{$hours}h {$minutes}m";
    }
}
