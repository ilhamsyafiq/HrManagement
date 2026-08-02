<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Notifications\SystemNotification;
use App\Services\ScheduleResolver;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Closes attendance records where the employee clocked in but forgot to clock
 * out. Rather than let an open record run to "now" (which would inflate worked
 * hours and wages), the clock-out is snapped to the scheduled shift end for that
 * date via {@see ScheduleResolver}:
 *
 *   - Overnight shifts (e.g. 21:00 -> 06:00) resolve the end to the next day.
 *   - A grace period (config hr.auto_clockout.grace_minutes) must elapse past the
 *     scheduled end before a record is closed, so a still-on-shift late employee
 *     is never closed prematurely.
 *   - When no schedule applies (rest day / no roster / no working hours), the
 *     clock-out falls back to clock_in + standard_daily_hours.
 *
 * Any still-open break for the record is closed at the same snapped time. The
 * record is flagged (is_auto_clocked_out) with an explanatory note and the
 * employee is notified so they can request a correction.
 *
 * Intended to run every minute from Windows Task Scheduler / cron via
 * `php artisan schedule:run` (see routes/console.php).
 */
class CloseForgottenClockOuts extends Command
{
    protected $signature = 'attendance:auto-clockout {--dry-run : List what would be closed without writing changes}';

    protected $description = 'Snap forgotten clock-outs to the scheduled shift end so hours/wages are not inflated';

    private const TZ = 'Asia/Kuala_Lumpur';

    public function handle(): int
    {
        $now = Carbon::now(self::TZ);
        $grace = (int) config('hr.auto_clockout.grace_minutes', 120);
        $dryRun = (bool) $this->option('dry-run');

        // Candidates: clocked in, not yet clocked out, on a date on/before today.
        // (Today is included so overnight shifts that ended earlier today close.)
        $open = Attendance::with(['user', 'breaks'])
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->whereDate('date', '<=', $now->toDateString())
            ->get();

        $closed = 0;

        foreach ($open as $attendance) {
            if (! $attendance->clock_in) {
                continue;
            }

            $date = Carbon::parse($attendance->date, self::TZ)->startOfDay();
            $end = $this->resolveScheduledEnd($attendance, $date);

            // Never snap the clock-out earlier than the clock-in.
            if ($end->lessThanOrEqualTo($attendance->clock_in)) {
                $end = $attendance->clock_in->copy()->addHours(
                    (float) config('hr.standard_daily_hours', 8)
                );
            }

            // Respect the grace window: only close once we are safely past the end.
            if ($now->lessThan($end->copy()->addMinutes($grace))) {
                continue;
            }

            if ($dryRun) {
                $this->line(sprintf(
                    '[dry-run] #%d %s (%s) -> clock-out %s',
                    $attendance->id,
                    $attendance->user?->name ?? 'user#' . $attendance->user_id,
                    $attendance->date,
                    $end->format('Y-m-d H:i')
                ));
                $closed++;
                continue;
            }

            $this->close($attendance, $end);
            $closed++;
        }

        $this->info(($dryRun ? '[dry-run] ' : '') . "Auto clock-out complete: {$closed} record(s) processed.");

        return self::SUCCESS;
    }

    /**
     * Scheduled shift end datetime for the record's date (overnight-aware),
     * falling back to clock_in + standard_daily_hours when no schedule applies.
     */
    private function resolveScheduledEnd(Attendance $attendance, Carbon $date): Carbon
    {
        $schedule = $attendance->user
            ? ScheduleResolver::forUser($attendance->user, $date)
            : null;

        // Split shift: snap to the LAST segment's end (overnight-aware).
        // Segments are ordered; walk them so the running "end" rolls across days
        // and each segment that ends at/before its start bumps to the next day.
        if ($schedule && ! empty($schedule['segments'])) {
            $cursor = Carbon::parse($date->toDateString() . ' ' . $schedule['segments'][0]['start'], self::TZ);
            $end = $cursor->copy();
            foreach ($schedule['segments'] as $segment) {
                $segStart = Carbon::parse($date->toDateString() . ' ' . $segment['start'], self::TZ);
                // Keep segments non-decreasing across midnight relative to the cursor.
                while ($segStart->lessThan($cursor)) {
                    $segStart->addDay();
                }
                $segEnd = $segStart->copy()->setTimeFromTimeString($segment['end']);
                if ($segEnd->lessThanOrEqualTo($segStart)) {
                    $segEnd->addDay();
                }
                $cursor = $segEnd->copy();
                $end = $segEnd->copy();
            }

            return $end;
        }

        if ($schedule && $schedule['start'] && $schedule['end']) {
            $start = Carbon::parse($date->toDateString() . ' ' . $schedule['start'], self::TZ);
            $end = Carbon::parse($date->toDateString() . ' ' . $schedule['end'], self::TZ);
            // Overnight shift: end time rolls into the next day.
            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }

            return $end;
        }

        return $attendance->clock_in->copy()->addHours(
            (float) config('hr.standard_daily_hours', 8)
        );
    }

    /**
     * Persist the snapped clock-out, close any open break, recompute paid hours
     * and notify the employee.
     */
    private function close(Attendance $attendance, Carbon $end): void
    {
        // Close any still-open break at (or before) the snapped clock-out.
        $activeBreak = $attendance->breaks->whereNull('break_out')->first();
        if ($activeBreak) {
            $breakOut = $end->lessThan($activeBreak->break_in) ? $activeBreak->break_in : $end;
            $activeBreak->update([
                'break_out' => $breakOut,
                'duration_minutes' => (int) round($activeBreak->break_in->diffInMinutes($breakOut)),
            ]);
        }

        // Recompute worked hours = span minus completed breaks (mirrors clockOut()).
        $attendance->load('breaks');
        $breakHours = $attendance->breaks->whereNotNull('break_out')->sum('duration_minutes') / 60;
        $totalMinutes = ($end->timestamp - $attendance->clock_in->timestamp) / 60;
        $totalHours = max(0, round($totalMinutes / 60, 2) - $breakHours);

        $note = 'Auto clock-out: no clock-out was recorded; snapped to scheduled shift end ('
            . $end->format('d M Y, g:i A') . ').';

        $attendance->update([
            'clock_out' => $end,
            'total_work_hours' => $totalHours,
            'is_auto_clocked_out' => true,
            'auto_clock_out_note' => $note,
        ]);

        $attendance->user?->notify(new SystemNotification(
            'Attendance auto clocked-out',
            'You did not clock out on ' . Carbon::parse($attendance->date)->format('d M Y')
                . '. The system set your clock-out to the scheduled shift end ('
                . $end->format('g:i A') . '). Please contact HR if this is incorrect.',
            route('attendance.index'),
            'clock'
        ));

        $this->line("Closed #{$attendance->id} ({$attendance->user?->name}) -> {$end->format('Y-m-d H:i')}");
    }
}
