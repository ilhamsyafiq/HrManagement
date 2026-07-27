<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use App\Http\Controllers\PayrollController;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generatePayroll')
                ->label('Generate payroll')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('primary')
                ->modalHeading('Generate monthly payroll')
                ->modalDescription('Creates payroll (with statutory contributions and an overtime allowance) for all active employees who do not already have one for the chosen month.')
                ->form([
                    Forms\Components\TextInput::make('month')
                        ->label('Period (month)')
                        ->placeholder('2026-03')
                        ->helperText('Format: YYYY-MM')
                        ->default(now('Asia/Kuala_Lumpur')->format('Y-m'))
                        ->required()
                        ->rule('regex:/^\d{4}-\d{2}$/'),
                ])
                ->action(function (array $data): void {
                    $month = $data['month'];

                    // "Active employees" = everyone except Super Admin,
                    // matching the existing PayrollController convention.
                    $employees = User::with('profile')
                        ->whereHas('role', fn ($q) => $q->whereNotIn('name', ['Super Admin']))
                        ->get();

                    $generated = PayrollController::generatePayrollFor(
                        $employees,
                        $month,
                        auth()->id(),
                    );

                    Notification::make()
                        ->success()
                        ->title('Payroll generated')
                        ->body("{$generated} payroll(s) created for {$month}.")
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
