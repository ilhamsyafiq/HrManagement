<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds a month of day-by-day attendance status for a user, combining
 * attendance records, approved/pending leaves, public holidays and the
 * resolved shift schedule (rest/off days).
 *
 * The output is view-ready: each day carries a type, colour key, short code
 * (for the team matrix) and label (for tooltips), so the Blade stays dumb.
 */
class AttendanceCalendarService
{
    private const TZ = 'Asia/Kuala_Lumpur';

    /** Human labels for leave type codes. */
    private const LEAVE_LABELS = [
        'AL' => 'Annual Leave',
        'MC' => 'Medical Leave',
        'Emergency' => 'Emergency Leave',
        'Intern' => 'Intern Leave',
    ];

    /**
     * @return array{
     *   month: Carbon,
     *   leading: int,
     *   days: array<string, array<string, mixed>>,
     *   summary: array<string, int|float>
     * }
     */
    public function buildMonth(User $user, Carbon $monthStart): array
    {
        $monthStart = $monthStart->copy()->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $today = Carbon::today(self::TZ);

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->keyBy(fn (Attendance $a) => $a->date->toDateString());

        $leaves = Leave::where('user_id', $user->id)
            ->whereIn('status', ['Approved', 'Pending'])
            ->whereDate('start_date', '<=', $monthEnd->toDateString())
            ->whereDate('end_date', '>=', $monthStart->toDateString())
            // Approved first so it wins over an overlapping pending request.
            ->orderByRaw("FIELD(status, 'Approved', 'Pending')")
            ->get();

        $holidays = $this->holidayMap($monthStart, $monthEnd);

        $days = [];
        $summary = [
            'worked' => 0, 'al' => 0, 'mc' => 0, 'emergency' => 0, 'intern' => 0,
            'absent' => 0, 'off' => 0, 'holiday' => 0, 'late' => 0, 'ot_hours' => 0.0,
        ];

        for ($d = $monthStart->copy(); $d->lte($monthEnd); $d->addDay()) {
            $key = $d->toDateString();
            $status = $this->resolveDay(
                $user,
                $d->copy(),
                $today,
                $attendances->get($key),
                $this->leaveForDate($leaves, $d),
                $holidays->get($key),
            );
            $days[$key] = $status;
            $this->tally($summary, $status);
        }

        return [
            'month' => $monthStart,
            'leading' => (int) $monthStart->dayOfWeek, // 0 = Sunday
            'days' => $days,
            'summary' => $summary,
        ];
    }

    /**
     * Resolve a single day to one status by precedence:
     * worked > holiday > leave > off (no schedule) > absent (past workday) > upcoming.
     *
     * @return array<string, mixed>
     */
    private function resolveDay(
        User $user,
        Carbon $date,
        Carbon $today,
        ?Attendance $att,
        ?Leave $leave,
        ?Holiday $holiday,
    ): array {
        $base = [
            'day' => $date->day,
            'date' => $date->toDateString(),
            'dow' => (int) $date->dayOfWeek,
            'is_today' => $date->isSameDay($today),
            'is_weekend' => $date->isWeekend(),
        ];

        $scheduled = ScheduleResolver::forUser($user, $date); // null => rest/off day

        // 1. Worked — an attendance record with a clock-in wins over everything.
        if ($att && $att->clock_in) {
            $badges = [];
            if ($att->is_late) {
                $badges[] = 'Late';
            }
            if ($att->is_early_leave) {
                $badges[] = 'Early';
            }
            if ($att->is_wfh) {
                $badges[] = 'WFH';
            }
            if ($att->overtime_hours > 0) {
                $badges[] = 'OT ' . $att->formatted_overtime;
            }
            if ($holiday) {
                $badges[] = 'Holiday';
            }

            return $base + [
                'type' => 'worked',
                'short' => 'P',
                'color' => 'emerald',
                'label' => 'Worked · ' . $att->formatted_work_hours,
                'meta' => [
                    'hours' => $att->formatted_work_hours,
                    'badges' => $badges,
                    'late' => (bool) $att->is_late,
                    'ot' => (float) $att->overtime_hours,
                ],
            ];
        }

        // 2. Public holiday.
        if ($holiday) {
            return $base + [
                'type' => 'holiday',
                'short' => 'PH',
                'color' => 'purple',
                'label' => 'Public Holiday · ' . $holiday->name,
                'meta' => ['name' => $holiday->name],
            ];
        }

        // 3. On leave (approved or pending).
        if ($leave) {
            $pending = $leave->status === 'Pending';
            $colors = ['AL' => 'blue', 'MC' => 'rose', 'Emergency' => 'amber', 'Intern' => 'indigo'];
            $shorts = ['AL' => 'AL', 'MC' => 'MC', 'Emergency' => 'EL', 'Intern' => 'IL'];
            $label = self::LEAVE_LABELS[$leave->type] ?? $leave->type;

            return $base + [
                'type' => 'leave',
                'short' => $shorts[$leave->type] ?? 'L',
                'color' => $colors[$leave->type] ?? 'blue',
                'leave_type' => $leave->type,
                'pending' => $pending,
                'label' => $label . ($pending ? ' (Pending)' : ''),
                'meta' => ['status' => $leave->status],
            ];
        }

        // 4. Rest / off day — no schedule resolves for this date.
        if (! $scheduled) {
            return $base + [
                'type' => 'off',
                'short' => '',
                'color' => 'slate',
                'label' => $date->isWeekend() ? 'Weekend / Off' : 'Rest day',
                'meta' => [],
            ];
        }

        // 5. A scheduled workday in the past with nothing recorded => absent.
        if ($date->lt($today)) {
            return $base + [
                'type' => 'absent',
                'short' => '✕',
                'color' => 'red',
                'label' => 'Absent (no clock-in)',
                'meta' => [],
            ];
        }

        // 6. Today (not yet clocked in) or a future scheduled workday.
        return $base + [
            'type' => 'upcoming',
            'short' => '',
            'color' => 'neutral',
            'label' => $date->isSameDay($today) ? 'Scheduled today' : 'Scheduled',
            'meta' => [],
        ];
    }

    /**
     * @param array<string, int|float> $summary
     * @param array<string, mixed> $status
     */
    private function tally(array &$summary, array $status): void
    {
        switch ($status['type']) {
            case 'worked':
                $summary['worked']++;
                if ($status['meta']['late'] ?? false) {
                    $summary['late']++;
                }
                $summary['ot_hours'] += (float) ($status['meta']['ot'] ?? 0);
                break;
            case 'holiday':
                $summary['holiday']++;
                break;
            case 'leave':
                $map = ['AL' => 'al', 'MC' => 'mc', 'Emergency' => 'emergency', 'Intern' => 'intern'];
                $bucket = $map[$status['leave_type']] ?? null;
                if ($bucket) {
                    $summary[$bucket]++;
                }
                break;
            case 'off':
                $summary['off']++;
                break;
            case 'absent':
                $summary['absent']++;
                break;
        }
    }

    /**
     * The first leave covering the date (approved preferred — see order in query).
     */
    private function leaveForDate(Collection $leaves, Carbon $date): ?Leave
    {
        return $leaves->first(
            fn (Leave $l) => $date->betweenIncluded($l->start_date, $l->end_date)
        );
    }

    /**
     * Holidays keyed by 'Y-m-d' for the month, including recurring holidays
     * mapped onto the current year's matching month/day.
     */
    private function holidayMap(Carbon $monthStart, Carbon $monthEnd): Collection
    {
        $map = Holiday::whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->keyBy(fn (Holiday $h) => $h->date->toDateString());

        $recurring = Holiday::where('is_recurring', true)
            ->whereMonth('date', $monthStart->month)
            ->get();

        foreach ($recurring as $h) {
            if ($h->date->day > $monthStart->daysInMonth) {
                continue;
            }
            $key = $monthStart->copy()->day($h->date->day)->toDateString();
            if (! $map->has($key)) {
                $map->put($key, $h);
            }
        }

        return $map;
    }
}
