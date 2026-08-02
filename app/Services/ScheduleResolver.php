<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkingHour;
use Carbon\Carbon;

/**
 * Resolves the working schedule that applies to an employee on a given date.
 *
 * Priority:
 *   1. The shift assigned to that employee for that day of the week (weekly roster).
 *   2. If the employee is on a roster but has no shift that weekday -> rest day (null).
 *   3. Otherwise fall back to the employee's WorkingHour config (legacy behaviour).
 */
class ScheduleResolver
{
    /**
     * @return array{start:?string,end:?string,break_start:?string,break_end:?string,segments:array<int,array{start:string,end:string}>,is_flexible:bool,late_threshold:int,early_threshold:int,source:string}|null
     *         Times are 'H:i' strings. Returns null on a rest day / when no schedule applies.
     */
    public static function forUser(User $user, Carbon $date): ?array
    {
        $wh = WorkingHour::getForUser($user->id);
        $lateThreshold = $wh->late_threshold_minutes ?? 15;
        $earlyThreshold = $wh->early_leave_threshold_minutes ?? 15;

        $shift = $user->shiftForDate($date);

        if ($shift) {
            $segments = [];
            foreach ($shift->segments as $segment) {
                if (! $segment->start_time || ! $segment->end_time) {
                    continue;
                }
                $segments[] = [
                    'start' => self::toTime($segment->start_time),
                    'end' => self::toTime($segment->end_time),
                ];
            }

            // Keep 'start'/'end' pointing at the whole-day span so existing
            // consumers keep working: first segment start, last segment end.
            $start = ! empty($segments) ? $segments[0]['start'] : self::toTime($shift->start_time);
            $end = ! empty($segments) ? $segments[count($segments) - 1]['end'] : self::toTime($shift->end_time);

            return [
                'start' => $start,
                'end' => $end,
                'break_start' => self::toTime($shift->break_start),
                'break_end' => self::toTime($shift->break_end),
                'segments' => $segments,
                'is_flexible' => (bool) $shift->is_flexible,
                'late_threshold' => $lateThreshold,
                'early_threshold' => $earlyThreshold,
                'source' => 'shift',
            ];
        }

        // On a weekly roster but nothing scheduled today -> rest/off day.
        if ($user->hasWeeklyRoster()) {
            return null;
        }

        // No roster: fall back to the legacy per-user working hours.
        if ($wh && $wh->work_start && $wh->work_end) {
            return [
                'start' => self::toTime($wh->work_start),
                'end' => self::toTime($wh->work_end),
                'break_start' => self::toTime($wh->break_start),
                'break_end' => self::toTime($wh->break_end),
                'segments' => [],
                'is_flexible' => false,
                'late_threshold' => $lateThreshold,
                'early_threshold' => $earlyThreshold,
                'source' => 'working_hours',
            ];
        }

        return null;
    }

    /**
     * Paid hours for the resolved schedule (span minus unpaid break), or null.
     */
    public static function paidHours(?array $schedule): ?float
    {
        if (! $schedule) {
            return null;
        }

        // Split shift: sum each segment (overnight-aware per segment).
        if (! empty($schedule['segments'])) {
            $minutes = 0;
            foreach ($schedule['segments'] as $segment) {
                if (empty($segment['start']) || empty($segment['end'])) {
                    continue;
                }
                $segStart = Carbon::parse($segment['start']);
                $segEnd = Carbon::parse($segment['end']);
                if ($segEnd->lessThanOrEqualTo($segStart)) {
                    $segEnd->addDay();
                }
                $minutes += $segStart->diffInMinutes($segEnd);
            }

            return $minutes > 0 ? $minutes / 60 : null;
        }

        if (! $schedule['start'] || ! $schedule['end']) {
            return null;
        }

        $start = Carbon::parse($schedule['start']);
        $end = Carbon::parse($schedule['end']);
        // Overnight shift (e.g. 21:00 -> 06:00): end falls on the next day.
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }
        $minutes = $start->diffInMinutes($end);

        if ($schedule['break_start'] && $schedule['break_end']) {
            $breakStart = Carbon::parse($schedule['break_start']);
            $breakEnd = Carbon::parse($schedule['break_end']);
            if ($breakEnd->lessThanOrEqualTo($breakStart)) {
                $breakEnd->addDay();
            }
            $minutes -= $breakStart->diffInMinutes($breakEnd);
        }

        return $minutes > 0 ? $minutes / 60 : null;
    }

    /**
     * Normalise a time value (Carbon or "HH:MM[:SS]" string) to "H:i".
     */
    private static function toTime($value): ?string
    {
        if (! $value) {
            return null;
        }

        return $value instanceof \Carbon\CarbonInterface
            ? $value->format('H:i')
            : Carbon::parse($value)->format('H:i');
    }
}
