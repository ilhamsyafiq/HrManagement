<?php

namespace App\Filament\Widgets;

use App\Models\Leave;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingLeavesTable extends BaseWidget
{
    protected static ?string $heading = 'Upcoming Approved Leaves';

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        $today = now('Asia/Kuala_Lumpur')->toDateString();

        $typeLabels = [
            'AL' => 'Annual Leave',
            'MC' => 'Medical Leave',
            'Emergency' => 'Emergency Leave',
            'Intern' => 'Intern Leave',
        ];

        return $table
            ->query(
                Leave::query()
                    ->where('status', 'Approved')
                    ->whereDate('end_date', '>=', $today)
                    ->with('user')
                    ->orderBy('start_date')
            )
            ->emptyStateHeading('No upcoming approved leaves')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->weight('bold')
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $typeLabels[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'AL' => 'info',
                        'MC' => 'danger',
                        'Emergency' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('From')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('To')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Days')
                    ->state(fn (Leave $record): int => $record->start_date->diffInDays($record->end_date) + 1)
                    ->badge()
                    ->color('success'),
            ]);
    }
}
