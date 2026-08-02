<?php

namespace App\Filament\Widgets;

use App\Models\Role;
use Filament\Widgets\ChartWidget;

class HeadcountByRoleChart extends ChartWidget
{
    protected static ?string $heading = 'Headcount by Role';

    protected static ?string $description = 'Distribution of staff across access roles.';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $roles = Role::withCount('users')
            ->orderByDesc('users_count')
            ->get();

        if ($roles->isEmpty()) {
            return [
                'datasets' => [['label' => 'Employees', 'data' => [0]]],
                'labels' => ['No roles'],
            ];
        }

        $palette = ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4'];
        $colors = [];
        foreach ($roles as $i => $role) {
            $colors[] = $palette[$i % count($palette)];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Employees',
                    'data' => $roles->pluck('users_count')->toArray(),
                    'backgroundColor' => $colors,
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $roles->pluck('name')->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => [
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
