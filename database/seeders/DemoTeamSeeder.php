<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Demo data for the monthly attendance calendar + team views.
 *
 * Sets up the IT department as a clean demo team: a HOD/supervisor, several
 * reports on a Mon–Fri roster (so weekends render as Off), a month of varied
 * attendance (present / late / WFH / OT / absent), and overlapping approved +
 * pending leaves (AL/MC/Emergency) so the team matrix, the "on leave" clash
 * counter and the calendar leave chips all have something to show.
 *
 * Idempotent — safe to re-run. Scoped to the demo users only.
 */
class DemoTeamSeeder extends Seeder
{
    private const TZ = 'Asia/Kuala_Lumpur';

    public function run(): void
    {
        $now = Carbon::now(self::TZ);
        $normalShiftId = Shift::where('name', 'Normal Shift')->value('id') ?? 2;
        $itDeptId = Department::where('name', 'IT')->value('id');
        $adminId = User::whereHas('role', fn ($q) => $q->whereIn('name', ['Super Admin', 'Admin']))->value('id');

        $supervisor = User::where('email', 'supervisor@example.com')->first();
        if (! $supervisor || ! $itDeptId) {
            $this->command?->warn('DemoTeamSeeder: base data missing (run the main seeders first). Skipping.');
            return;
        }

        // IT department headed by the supervisor.
        Department::whereKey($itDeptId)->update(['hod_id' => $supervisor->id]);
        $supervisor->update(['department_id' => $itDeptId]);

        // Regular reports in IT (exclude the flexible part-timer to keep the demo clean).
        $team = User::whereIn('email', [
            'employee@example.com',
            'sarah@example.com',
            'nurul@example.com',
            'intern@example.com',
        ])->get();

        foreach ($team as $m) {
            $m->update(['supervisor_id' => $supervisor->id, 'department_id' => $itDeptId]);
        }

        // Mon–Fri Normal-Shift roster (weekends = Off) for the supervisor + team.
        $rosterUsers = $team->push($supervisor);
        foreach ($rosterUsers as $m) {
            ShiftAssignment::where('user_id', $m->id)->delete();
            foreach (range(1, 5) as $dow) { // 1 = Mon … 5 = Fri
                ShiftAssignment::create([
                    'user_id' => $m->id,
                    'shift_id' => $normalShiftId,
                    'day_of_week' => $dow,
                ]);
            }
        }

        // ---- Leaves (curated, overlapping) ----------------------------------
        $email = fn (string $e) => User::where('email', $e)->value('id');
        $monthStart = $now->copy()->startOfMonth();
        $ym = fn (int $day) => $monthStart->copy()->day($day)->toDateString();          // day in current month
        $prevYm = fn (int $day) => $monthStart->copy()->subMonth()->day($day)->toDateString(); // day in previous month

        $leaves = [
            // employee (ilham): pending single day + an approved MC block
            ['user' => 'employee@example.com', 'type' => 'AL', 'status' => 'Pending', 'from' => $ym(5), 'to' => $ym(5)],
            ['user' => 'employee@example.com', 'type' => 'MC', 'status' => 'Approved', 'from' => $ym(6), 'to' => $ym(7)],
            // Sarah: approved AL block this month + a past MC (previous month, for the "actuals" view)
            ['user' => 'sarah@example.com', 'type' => 'AL', 'status' => 'Approved', 'from' => $ym(11), 'to' => $ym(13)],
            ['user' => 'sarah@example.com', 'type' => 'MC', 'status' => 'Approved', 'from' => $prevYm(21), 'to' => $prevYm(22)],
            // Nurul: pending AL that OVERLAPS Sarah on the 12th–13th -> clash counter lights up
            ['user' => 'nurul@example.com', 'type' => 'AL', 'status' => 'Pending', 'from' => $ym(12), 'to' => $ym(14)],
            // Intern: pending emergency leave
            ['user' => 'intern@example.com', 'type' => 'Emergency', 'status' => 'Pending', 'from' => $ym(20), 'to' => $ym(20)],
            // Supervisor's own approved AL
            ['user' => 'supervisor@example.com', 'type' => 'AL', 'status' => 'Approved', 'from' => $ym(25), 'to' => $ym(26)],
        ];

        // Clear prior leaves for the demo users, then insert the curated set.
        $demoIds = $rosterUsers->pluck('id')->all();
        Leave::whereIn('user_id', $demoIds)->delete();

        $leaveDaysByUser = [];
        foreach ($leaves as $lv) {
            $uid = $email($lv['user']);
            if (! $uid) {
                continue;
            }
            Leave::create([
                'user_id' => $uid,
                'type' => $lv['type'],
                'start_date' => $lv['from'],
                'end_date' => $lv['to'],
                'reason' => 'Demo ' . $lv['type'] . ' leave',
                'status' => $lv['status'],
                'approved_by' => $lv['status'] === 'Approved' ? $adminId : null,
                'approved_at' => $lv['status'] === 'Approved' ? $now : null,
            ]);
            // Track covered dates so we don't also stamp attendance on them.
            for ($d = Carbon::parse($lv['from']); $d->lte(Carbon::parse($lv['to'])); $d->addDay()) {
                $leaveDaysByUser[$uid][$d->toDateString()] = true;
            }
        }

        // ---- Holidays (one mid-month weekday, for visibility) ----------------
        Holiday::firstOrCreate(
            ['date' => $monthStart->copy()->day(17)->toDateString()],
            ['name' => 'State Holiday (Demo)', 'type' => 'Public', 'is_recurring' => false, 'created_by' => $adminId]
        );
        $holidayDates = Holiday::whereBetween('date', [
            $monthStart->copy()->subMonth()->startOfMonth()->toDateString(),
            $monthStart->copy()->endOfMonth()->toDateString(),
        ])->pluck('date')->map(fn ($d) => Carbon::parse($d)->toDateString())->flip();

        // ---- Attendance: previous month full + current month up to today ----
        $start = $now->copy()->subMonth()->startOfMonth();
        $seeded = 0;
        foreach ($team as $ui => $emp) {
            for ($date = $start->copy(); $date->lte($now); $date->addDay()) {
                if ($date->isWeekend()) {
                    continue; // Off (rest day)
                }
                $ds = $date->toDateString();
                if (isset($leaveDaysByUser[$emp->id][$ds]) || $holidayDates->has($ds)) {
                    continue; // leave / holiday day — let those statuses show
                }

                $key = $date->day + $ui;

                // Deterministic variety + a few absences (gaps -> "Absent").
                if ($key % 11 === 0) {
                    continue; // absent
                }
                $late = $key % 7 === 0;
                $wfh = $key % 9 === 0;
                $ot = $key % 5 === 0;

                $clockIn = $date->copy()->setTime($late ? 9 : 9, $late ? 35 : 2);
                $clockOut = $date->copy()->setTime($ot ? 19 : 17, $ot ? 5 : 30);

                $attendance = Attendance::firstOrCreate(
                    ['user_id' => $emp->id, 'date' => $ds],
                    [
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'is_late' => $late,
                        'late_minutes' => $late ? 35 : 0,
                        'is_early_leave' => false,
                        'early_leave_minutes' => 0,
                        'is_wfh' => $wfh,
                    ]
                );

                // 1h lunch break so paid hours match the 7.5h standard (no phantom OT).
                BreakRecord::firstOrCreate(
                    ['attendance_id' => $attendance->id],
                    [
                        'break_in' => $date->copy()->setTime(13, 0),
                        'break_out' => $date->copy()->setTime(14, 0),
                        'duration_minutes' => 60,
                    ]
                );
                $seeded++;
            }
        }

        $this->command?->info("DemoTeamSeeder: IT team ready — {$seeded} attendance rows, " . count($leaves) . ' leaves.');
        $this->command?->info('Login as supervisor@example.com (HOD) to see Team Attendance + team leaves; sarah@example.com as a same-dept employee. Password: password');
    }
}
