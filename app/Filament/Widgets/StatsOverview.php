<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Claim;
use App\Models\Leave;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $tz = 'Asia/Kuala_Lumpur';
        $today = now($tz)->toDateString();

        $totalUsers = User::count();
        $presentToday = Attendance::where('date', $today)
            ->whereNotNull('clock_in')
            ->count();

        // People approved to be on leave today (spanning the date range).
        $onLeaveToday = Leave::where('status', 'Approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->distinct('user_id')
            ->count('user_id');

        $absentToday = max(0, $totalUsers - $presentToday - $onLeaveToday);

        $attendanceRate = $totalUsers > 0
            ? round(($presentToday / $totalUsers) * 100)
            : 0;

        $pendingLeaves = Leave::where('status', 'Pending')->count();
        $pendingClaims = Claim::where('status', 'Pending')->count();
        $lateToday = Attendance::where('date', $today)->where('is_late', true)->count();

        // 7-day present trend for the sparkline under the attendance card.
        $presentTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now($tz)->subDays($i)->toDateString();
            $presentTrend[] = Attendance::where('date', $day)
                ->whereNotNull('clock_in')
                ->count();
        }

        return [
            Stat::make('Total Employees', $totalUsers)
                ->description('Active registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([$totalUsers, $totalUsers, $totalUsers, $totalUsers, $totalUsers, $totalUsers, $totalUsers]),

            Stat::make('Present Today', $presentToday . ' / ' . $totalUsers)
                ->description($attendanceRate . '% attendance rate today')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($attendanceRate >= 80 ? 'success' : ($attendanceRate >= 50 ? 'warning' : 'danger'))
                ->chart($presentTrend),

            Stat::make('Absent Today', $absentToday)
                ->description($onLeaveToday . ' on approved leave')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color($absentToday > 0 ? 'danger' : 'success'),

            Stat::make('Late Arrivals Today', $lateToday)
                ->description('Clocked in after start time')
                ->descriptionIcon('heroicon-m-clock')
                ->color($lateToday > 0 ? 'warning' : 'gray'),

            Stat::make('Pending Leave Requests', $pendingLeaves)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($pendingLeaves > 0 ? 'warning' : 'gray'),

            Stat::make('Pending Claims', $pendingClaims)
                ->description('Expense claims to review')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($pendingClaims > 0 ? 'warning' : 'gray'),
        ];
    }
}
