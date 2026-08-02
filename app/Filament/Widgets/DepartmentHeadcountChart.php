<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use Filament\Widgets\ChartWidget;

class DepartmentHeadcountChart extends ChartWidget
{
    protected static ?string $heading = 'Headcount by Department';

    protected static ?string $description = 'Number of employees assigned to each department.';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $departments = Department::withCount('users')
            ->orderByDesc('users_count')
            ->get();

        if ($departments->isEmpty()) {
            return [
                'datasets' => [['label' => 'Employees', 'data' => [0]]],
                'labels' => ['No departments'],
            ];
        }

        $palette = [
            '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', '#f59e0b',
            '#10b981', '#06b6d4', '#3b82f6', '#14b8a6', '#a855f7',
        ];

        $colors = [];
        foreach ($departments as $i => $dept) {
            $colors[] = $palette[$i % count($palette)];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Employees',
                    'data' => $departments->pluck('users_count')->toArray(),
                    'backgroundColor' => $colors,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $departments->pluck('name')->toArray(),
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
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
