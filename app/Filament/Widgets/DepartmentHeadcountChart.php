<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use Filament\Widgets\ChartWidget;

class DepartmentHeadcountChart extends ChartWidget
{
    protected static ?string $heading = 'Employees by Department';

    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $departments = Department::withCount('users')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Employees',
                    'data' => $departments->pluck('users_count')->toArray(),
                    'backgroundColor' => '#6366f1',
                ],
            ],
            'labels' => $departments->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
