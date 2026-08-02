<?php

namespace App\Filament\Resources\ShiftAssignmentResource\Pages;

use App\Filament\Resources\ShiftAssignmentResource;
use App\Models\ShiftAssignment;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * Weekly-matrix list: one row per employee, the seven weekdays as columns, each
 * cell showing that day's shift (or "Off"). Replaces the default one-row-per-day
 * table, which was unreadable once employees had multiple assignments.
 */
class ListShiftAssignments extends ListRecords
{
    protected static string $resource = ShiftAssignmentResource::class;

    protected static string $view = 'filament.resources.shift-assignment.matrix';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New assignment'),
        ];
    }

    /**
     * @return array{days: array<int,string>, users: \Illuminate\Support\Collection}
     */
    public function getMatrix(): array
    {
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

        $users = User::query()
            ->whereHas('shiftAssignments')
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
                    'id' => $u->id,
                    'name' => $u->name,
                    'byDay' => $byDay,
                ];
            });

        return [
            'days' => ShiftAssignment::DAYS,
            'users' => $users,
        ];
    }
}
