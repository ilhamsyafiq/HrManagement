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
        'is_late', 'is_early_leave', 'late_minutes', 'early_leave_minutes',
        'is_auto_clocked_out', 'auto_clock_out_note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'edited_at' => 'datetime',
            'is_auto_clocked_out' => 'boolean',
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

    /**
     * Alias so views can read `is_edited` (the column is `is_manually_edited`).
     * Several Blade views reference `$attendance->is_edited`; without this the
     * "Edited" badge and status logic silently never fire.
     */
    public function getIsEditedAttribute(): bool
    {
        return (bool) $this->is_manually_edited;
    }

    public function getFormattedWorkHoursAttribute()
    {
        $workHours = $this->total_work_hours;

        // If no stored value but clock_in exists, calculate on-the-fly
        if (!$workHours && $this->clock_in) {
            $end = $this->clock_out ?? now('Asia/Kuala_Lumpur');
            $totalMinutes = ($end->timestamp - $this->clock_in->timestamp) / 60;
            // Use the eager-loaded relation when available to avoid N+1 in listings.
            $breaks = $this->relationLoaded('breaks') ? $this->breaks : $this->breaks()->get();
            $breakMinutes = $breaks->whereNotNull('break_out')->sum('duration_minutes');
            // Subtract active break time too
            $activeBreak = $breaks->whereNull('break_out')->first();
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

    /**
     * Numeric worked hours for this attendance (reuses stored/derived value).
     */
    protected function numericWorkHours(): float
    {
        $workHours = $this->total_work_hours;

        // Fall back to on-the-fly calculation when there is no stored value.
        if (!$workHours && $this->clock_in) {
            $end = $this->clock_out ?? now('Asia/Kuala_Lumpur');
            $totalMinutes = ($end->timestamp - $this->clock_in->timestamp) / 60;
            $breaks = $this->relationLoaded('breaks') ? $this->breaks : $this->breaks()->get();
            $breakMinutes = $breaks->whereNotNull('break_out')->sum('duration_minutes');
            $activeBreak = $breaks->whereNull('break_out')->first();
            if ($activeBreak) {
                $breakMinutes += now('Asia/Kuala_Lumpur')->diffInMinutes($activeBreak->break_in);
            }
            $workHours = max(0, ($totalMinutes - $breakMinutes) / 60);
        }

        return (float) ($workHours ?? 0);
    }

    /**
     * Standard paid daily hours for this attendance's user.
     *
     * Derived from the user's WorkingHour config (work_start -> work_end minus
     * the configured break window) when available; otherwise falls back to the
     * configurable default in config/hr.php.
     */
    protected function standardDailyHours(): float
    {
        try {
            $user = $this->user ?? \App\Models\User::find($this->user_id);
            $date = $this->date ? \Carbon\Carbon::parse($this->date) : \Carbon\Carbon::now();

            if ($user) {
                $schedule = \App\Services\ScheduleResolver::forUser($user, $date);
                $paidHours = \App\Services\ScheduleResolver::paidHours($schedule);

                if ($paidHours !== null) {
                    return $paidHours;
                }
            }
        } catch (\Exception $e) {
            // Fall through to default below.
        }

        return (float) config('hr.standard_daily_hours', 8);
    }

    /**
     * Overtime hours = worked hours beyond the standard daily hours (>= 0).
     */
    public function getOvertimeHoursAttribute(): float
    {
        return max(0, round($this->numericWorkHours() - $this->standardDailyHours(), 2));
    }

    /**
     * Human-friendly overtime, e.g. "1h 30m" or "-" when there is none.
     */
    public function getFormattedOvertimeAttribute(): string
    {
        $overtime = $this->overtime_hours;

        if ($overtime <= 0) {
            return '-';
        }

        $totalMinutes = round($overtime * 60);
        $hours = intdiv((int) $totalMinutes, 60);
        $minutes = (int) $totalMinutes % 60;

        return "{$hours}h {$minutes}m";
    }
}
