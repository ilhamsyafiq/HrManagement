<?php

namespace App\Filament\Resources\ShiftAssignmentResource\Pages;

use App\Filament\Resources\ShiftAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShiftAssignment extends EditRecord
{
    protected static string $resource = ShiftAssignmentResource::class;

    protected function getRedirectUrl(): string
    {
        return \App\Filament\Pages\ShiftRoster::getUrl();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successRedirectUrl(\App\Filament\Pages\ShiftRoster::getUrl()),
        ];
    }
}
