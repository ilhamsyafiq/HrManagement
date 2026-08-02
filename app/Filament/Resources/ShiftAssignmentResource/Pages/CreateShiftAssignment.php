<?php

namespace App\Filament\Resources\ShiftAssignmentResource\Pages;

use App\Filament\Resources\ShiftAssignmentResource;
use App\Models\ShiftAssignment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateShiftAssignment extends CreateRecord
{
    protected static string $resource = ShiftAssignmentResource::class;

    protected function getRedirectUrl(): string
    {
        return \App\Filament\Pages\ShiftRoster::getUrl();
    }

    /**
     * The form lets the admin tick several weekdays at once. Create (or
     * re-point) one assignment per selected day, and return the first so
     * Filament has a record to redirect to.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $days = $this->data['days'] ?? [];

        $first = null;

        foreach ($days as $day) {
            $record = ShiftAssignment::updateOrCreate(
                [
                    'user_id' => $data['user_id'],
                    'day_of_week' => $day,
                ],
                [
                    'shift_id' => $data['shift_id'],
                    'notes' => $data['notes'] ?? null,
                ],
            );

            $first ??= $record;
        }

        return $first ?? new ShiftAssignment();
    }
}
