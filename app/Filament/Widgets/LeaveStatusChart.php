<?php

namespace App\Filament\Widgets;

use App\Models\Leave;
use Filament\Widgets\ChartWidget;

class LeaveStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Leave Status Breakdown';

    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $stats = [
            'Approved' => Leave::where('status', 'Approved')->count(),
            'Pending' => Leave::where('status', 'Pending')->count(),
            'Rejected' => Leave::where('status', 'Rejected')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Leaves',
                    'data' => array_values($stats),
                    'backgroundColor' => ['#22c55e', '#f59e0b', '#ef4444'],
                ],
            ],
            'labels' => array_keys($stats),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
