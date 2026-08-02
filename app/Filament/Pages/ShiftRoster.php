<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ShiftAssignmentResource;
use App\Models\ShiftAssignment;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Pages\Page;

/**
 * The single weekly-roster view AND editor: one row per employee, Sun–Sat as
 * columns. Assigned cells link to the (nav-hidden) ShiftAssignment edit form;
 * empty cells are rest days. "New assignment" opens the create form.
 */
class ShiftRoster extends Page
{
    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $title = 'Shift Roster';

    protected static string $view = 'filament.pages.shift-roster';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addAssignment')
                ->label('New assignment')
                ->icon('heroicon-o-plus')
                ->url(ShiftAssignmentResource::getUrl('create')),
        ];
    }

    protected function getViewData(): array
    {
        // Shift start/end are cast to Carbon; segment times are raw "HH:MM:SS"
        // strings. Handle both so we always render "HH:MM".
        $fmt = function ($t) {
            if (! $t) {
                return '??';
            }
            if ($t instanceof \DateTimeInterface) {
                return $t->format('H:i');
            }

            return substr((string) $t, 0, 5);
        };

        $hours = function ($shift) use ($fmt): string {
            if (! $shift) {
                return '';
            }
            if ($shift->is_flexible) {
                return 'Flexible';
            }
            if ($shift->relationLoaded('segments') && $shift->segments->count()) {
                return $shift->segments
                    ->map(fn ($s) => $fmt($s->start_time) . '–' . $fmt($s->end_time))
                    ->implode(' + ');
            }

            return $fmt($shift->start_time) . '–' . $fmt($shift->end_time);
        };

        $rows = User::query()
            ->orderBy('name')
            ->with(['shiftAssignments.shift.segments'])
            ->get()
            ->map(function (User $u) use ($hours) {
                $byDay = [];
                foreach ($u->shiftAssignments as $a) {
                    $byDay[$a->day_of_week] = [
                        'id' => $a->id,
                        'shift' => $a->shift?->name ?? '—',
                        'hours' => $hours($a->shift),
                        'flexible' => (bool) ($a->shift?->is_flexible),
                    ];
                }

                return [
                    'name' => $u->name,
                    'byDay' => $byDay,
                ];
            });

        return [
            'days' => ShiftAssignment::DAYS,
            'rows' => $rows,
        ];
    }
}
