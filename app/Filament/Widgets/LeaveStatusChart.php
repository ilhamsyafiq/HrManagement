<?php

namespace App\Filament\Widgets;

use App\Models\Leave;
use Filament\Widgets\ChartWidget;

class LeaveStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Leave Requests by Status';

    protected static ?string $description = 'All leave requests grouped by their current approval status.';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $stats = [
            'Approved' => Leave::where('status', 'Approved')->count(),
            'Supervisor Approved' => Leave::where('status', 'Supervisor Approved')->count(),
            'Pending' => Leave::where('status', 'Pending')->count(),
            'Rejected' => Leave::where('status', 'Rejected')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Leaves',
                    'data' => array_values($stats),
                    'backgroundColor' => ['#22c55e', '#0ea5e9', '#f59e0b', '#ef4444'],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                    'hoverOffset' => 6,
                ],
            ],
            'labels' => array_keys($stats),
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
