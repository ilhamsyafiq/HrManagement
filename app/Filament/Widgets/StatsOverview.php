<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $today = now('Asia/Kuala_Lumpur')->toDateString();
        $presentToday = Attendance::where('date', $today)->count();
        $absentToday = max(0, $totalUsers - $presentToday);
        $pendingLeaves = Leave::where('status', 'Pending')->count();
        $totalAttendances = Attendance::count();

        return [
            Stat::make('Total Employees', $totalUsers)
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Present Today', $presentToday)
                ->description($absentToday . ' not clocked in')
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),

            Stat::make('Pending Leaves', $pendingLeaves)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($pendingLeaves > 0 ? 'warning' : 'gray'),

            Stat::make('Attendance Records', $totalAttendances)
                ->description('All-time clock-ins')
                ->descriptionIcon('heroicon-m-finger-print')
                ->color('info'),
        ];
    }
}
