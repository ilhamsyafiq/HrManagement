<?php

namespace App\Filament\Widgets;

use App\Models\Leave;
use Filament\Widgets\ChartWidget;

class LeavesByTypeChart extends ChartWidget
{
    protected static ?string $heading = 'Leaves by Type (This Year)';

    protected static ?string $description = 'Approved leave requests this year grouped by leave type.';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $year = now('Asia/Kuala_Lumpur')->year;

        // Map the stored enum values to human-friendly labels.
        $typeLabels = [
            'AL' => 'Annual Leave',
            'MC' => 'Medical Leave',
            'Emergency' => 'Emergency Leave',
            'Intern' => 'Intern Leave',
        ];

        $counts = Leave::where('status', 'Approved')
            ->whereYear('start_date', $year)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $labels = [];
        $data = [];
        foreach ($typeLabels as $key => $label) {
            $labels[] = $label;
            $data[] = (int) ($counts[$key] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Approved leaves',
                    'data' => $data,
                    'backgroundColor' => ['#3b82f6', '#ef4444', '#f59e0b', '#8b5cf6'],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                    'hoverOffset' => 6,
                ],
            ],
            'labels' => $labels,
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
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
