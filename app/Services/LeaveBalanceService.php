<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;

class LeaveBalanceService
{
    /**
     * Compute the current-calendar-year leave balance for a user.
     *
     * Returns an array keyed by leave type (AL, MC, Emergency) with:
     *   - entitlement: configured annual days (config/hr.php)
     *   - taken:       number of Approved leave days used this year
     *   - remaining:   entitlement - taken (never below 0)
     *
     * @return array<string, array{entitlement:int, taken:int, remaining:int}>
     */
    public static function for(User $user): array
    {
        // Part-timers are paid by actual hours worked and have no AL/MC/Emergency
        // entitlement: return an empty balance so the UI shows no leave for them.
        if ($user->isPartTime()) {
            return [];
        }

        $entitlements = config('hr.leave_entitlements', []);

        $year = Carbon::now('Asia/Kuala_Lumpur')->year;
        $startOfYear = Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Kuala_Lumpur')->toDateString();
        $endOfYear = Carbon::create($year, 12, 31, 23, 59, 59, 'Asia/Kuala_Lumpur')->toDateString();

        // Map each leave type to the per-user override column on `users`.
        $overrideColumns = [
            'AL' => 'al_entitlement',
            'MC' => 'mc_entitlement',
            'Emergency' => 'emergency_entitlement',
        ];

        $balance = [];

        foreach ($entitlements as $type => $configEntitlement) {
            // Per-user override wins over the flat config default; null = use config.
            $col = $overrideColumns[$type] ?? null;
            $override = $col ? ($user->{$col} ?? null) : null;
            $entitlement = is_null($override) ? (int) $configEntitlement : (int) $override;

            // Count approved leaves of this type whose start_date falls within the year.
            $leaves = Leave::where('user_id', $user->id)
                ->where('type', $type)
                ->where('status', 'Approved')
                ->whereBetween('start_date', [$startOfYear, $endOfYear])
                ->get();

            // Sum inclusive day-span of each leave (start and end dates count).
            $taken = $leaves->sum(function ($leave) {
                return $leave->start_date->diffInDays($leave->end_date) + 1;
            });

            $balance[$type] = [
                'entitlement' => (int) $entitlement,
                'taken' => (int) $taken,
                'remaining' => (int) max(0, $entitlement - $taken),
            ];
        }

        return $balance;
    }
}
