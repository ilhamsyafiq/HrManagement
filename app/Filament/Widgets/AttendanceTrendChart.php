<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;

class AttendanceTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Attendance & Late Arrivals (Last 6 Months)';

    protected static ?string $description = 'Monthly clock-in volume with late arrivals overlaid.';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $months = [];
        $counts = [];
        $late = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now('Asia/Kuala_Lumpur')->subMonths($i);
            $months[] = $date->format('M Y');

            $counts[] = Attendance::whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->whereNotNull('clock_in')
                ->count();

            $late[] = Attendance::whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->where('is_late', true)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Attendances',
                    'data' => $counts,
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                    'pointBackgroundColor' => '#2563eb',
                    'pointRadius' => 3,
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Late Arrivals',
                    'data' => $late,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                    'pointBackgroundColor' => '#f59e0b',
                    'pointRadius' => 3,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $months,
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
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
