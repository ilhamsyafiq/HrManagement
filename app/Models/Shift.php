<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'description',
        'is_active',
        'is_flexible',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'break_start' => 'datetime:H:i',
            'break_end' => 'datetime:H:i',
            'is_active' => 'boolean',
            'is_flexible' => 'boolean',
        ];
    }

    /**
     * Non-contiguous work blocks for a split shift, ordered by sort_order.
     * A shift with no segment rows behaves as a single span (legacy).
     */
    public function segments()
    {
        return $this->hasMany(ShiftSegment::class)->orderBy('sort_order');
    }

    /**
     * Paid hours for this shift.
     *
     * When segment rows exist, paid time = SUM over segments of (end - start),
     * overnight-aware per segment. Otherwise the legacy behaviour applies:
     * span (start -> end) minus the unpaid break.
     */
    public function paidHours(): float
    {
        $segments = $this->relationLoaded('segments') ? $this->segments : $this->segments()->get();

        if ($segments->isNotEmpty()) {
            $minutes = 0;
            foreach ($segments as $segment) {
                if (! $segment->start_time || ! $segment->end_time) {
                    continue;
                }
                $start = \Carbon\Carbon::parse($segment->start_time);
                $end = \Carbon\Carbon::parse($segment->end_time);
                // Overnight segment (e.g. 20:00 -> 02:00): end falls next day.
                if ($end->lessThanOrEqualTo($start)) {
                    $end->addDay();
                }
                $minutes += $start->diffInMinutes($end);
            }

            return max(0, $minutes) / 60;
        }

        if (! $this->start_time || ! $this->end_time) {
            return 0.0;
        }

        $start = $this->start_time->copy();
        $end = $this->end_time->copy();
        // Overnight shift (e.g. 21:00 -> 06:00): end falls on the next day.
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }
        $minutes = $start->diffInMinutes($end);

        if ($this->break_start && $this->break_end) {
            $breakStart = $this->break_start->copy();
            $breakEnd = $this->break_end->copy();
            if ($breakEnd->lessThanOrEqualTo($breakStart)) {
                $breakEnd->addDay();
            }
            $minutes -= $breakStart->diffInMinutes($breakEnd);
        }

        return max(0, $minutes) / 60;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function assignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }
}
