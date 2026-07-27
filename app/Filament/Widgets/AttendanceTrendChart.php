<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;

class AttendanceTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Attendance Trend (Last 6 Months)';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $months = [];
        $counts = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now('Asia/Kuala_Lumpur')->subMonths($i);
            $months[] = $date->format('M Y');
            $counts[] = Attendance::whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Attendances',
                    'data' => $counts,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
