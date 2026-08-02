<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class TodayAttendanceChart extends ChartWidget
{
    protected static ?string $heading = 'Workforce Status Today';

    protected static ?string $description = 'Present vs. absent vs. on approved leave right now.';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $tz = 'Asia/Kuala_Lumpur';
        $today = now($tz)->toDateString();

        $totalUsers = User::count();

        $present = Attendance::where('date', $today)
            ->whereNotNull('clock_in')
            ->count();

        $onLeave = Leave::where('status', 'Approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->distinct('user_id')
            ->count('user_id');

        $absent = max(0, $totalUsers - $present - $onLeave);

        return [
            'datasets' => [
                [
                    'label' => 'Employees',
                    'data' => [$present, $absent, $onLeave],
                    'backgroundColor' => ['#22c55e', '#ef4444', '#f59e0b'],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                    'hoverOffset' => 6,
                ],
            ],
            'labels' => ['Present', 'Absent', 'On Leave'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '55%',
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
