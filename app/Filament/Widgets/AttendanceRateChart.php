<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class AttendanceRateChart extends ChartWidget
{
    protected static ?string $heading = 'Daily Attendance Rate (Last 30 Days)';

    protected static ?string $description = 'Percentage of the workforce that clocked in each day.';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $tz = 'Asia/Kuala_Lumpur';
        $totalUsers = max(1, User::count());

        // Pull the last 30 days of present counts in one grouped query.
        $start = now($tz)->subDays(29)->toDateString();
        $end = now($tz)->toDateString();

        $presentByDate = Attendance::whereBetween('date', [$start, $end])
            ->whereNotNull('clock_in')
            ->selectRaw('date, COUNT(DISTINCT user_id) as present')
            ->groupBy('date')
            ->pluck('present', 'date');

        $labels = [];
        $rates = [];

        for ($i = 29; $i >= 0; $i--) {
            $day = now($tz)->subDays($i);
            $key = $day->toDateString();
            $labels[] = $day->format('d M');
            $present = (int) ($presentByDate[$key] ?? 0);
            $rates[] = round(($present / $totalUsers) * 100, 1);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Attendance Rate (%)',
                    'data' => $rates,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.12)',
                    'fill' => true,
                    'tension' => 0.3,
                    'pointRadius' => 2,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
