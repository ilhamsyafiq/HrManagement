<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Filament\Resources\PayrollResource\RelationManagers;
use App\Models\Payroll;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Organization';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'month';

    /**
     * The payroll module is gated behind config('hr.payroll_enabled') while the
     * statutory calculation is under review with finance & HR. When disabled the
     * resource is hidden from navigation and all its pages deny access (403).
     */
    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return (bool) config('hr.payroll_enabled');
    }

    public static function canViewAny(): bool
    {
        return (bool) config('hr.payroll_enabled');
    }

    public static function canAccess(): bool
    {
        return (bool) config('hr.payroll_enabled');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payroll Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Employee')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('month')
                            ->label('Period (month)')
                            ->placeholder('2026-03')
                            ->helperText('Format: YYYY-MM')
                            ->required()
                            ->maxLength(7),
                        Forms\Components\Select::make('status')
                            ->options([
                                'Draft' => 'Draft',
                                'Approved' => 'Approved',
                                'Paid' => 'Paid',
                            ])
                            ->default('Draft')
                            ->required(),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Payment date'),
                    ]),

                Forms\Components\Section::make('Earnings')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('basic_salary')
                            ->numeric()->prefix('RM')->default(0)->required(),
                        Forms\Components\TextInput::make('total_allowances')
                            ->label('Total allowances')
                            ->numeric()->prefix('RM')->default(0)
                            ->helperText('Auto-calculated from payroll items where computed server-side.'),
                        Forms\Components\TextInput::make('gross_salary')
                            ->numeric()->prefix('RM')->default(0),
                    ]),

                Forms\Components\Section::make('Statutory Deductions & Contributions')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('epf_employee')
                            ->label('EPF (employee)')->numeric()->prefix('RM')->default(0),
                        Forms\Components\TextInput::make('epf_employer')
                            ->label('EPF (employer)')->numeric()->prefix('RM')->default(0),
                        Forms\Components\TextInput::make('socso_employee')
                            ->label('SOCSO (employee)')->numeric()->prefix('RM')->default(0),
                        Forms\Components\TextInput::make('socso_employer')
                            ->label('SOCSO (employer)')->numeric()->prefix('RM')->default(0),
                        Forms\Components\TextInput::make('eis_employee')
                            ->label('EIS (employee)')->numeric()->prefix('RM')->default(0),
                        Forms\Components\TextInput::make('eis_employer')
                            ->label('EIS (employer)')->numeric()->prefix('RM')->default(0),
                        Forms\Components\TextInput::make('pcb_tax')
                            ->label('PCB (tax)')->numeric()->prefix('RM')->default(0),
                        Forms\Components\TextInput::make('total_deductions')
                            ->label('Total deductions')->numeric()->prefix('RM')->default(0),
                    ]),

                Forms\Components\Section::make('Net')
                    ->schema([
                        Forms\Components\TextInput::make('net_salary')
                            ->label('Net pay')
                            ->numeric()->prefix('RM')->default(0)->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('month')
                    ->label('Period')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('basic_salary')
                    ->label('Basic')
                    ->money('MYR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gross_salary')
                    ->label('Gross')
                    ->money('MYR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_deductions')
                    ->label('Deductions')
                    ->money('MYR')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('epf_employee')
                    ->label('EPF')->money('MYR')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('socso_employee')
                    ->label('SOCSO')->money('MYR')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('eis_employee')
                    ->label('EIS')->money('MYR')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pcb_tax')
                    ->label('PCB')->money('MYR')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('net_salary')
                    ->label('Net pay')
                    ->money('MYR')
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Approved' => 'success',
                        'Paid' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'Approved' => 'Approved',
                        'Paid' => 'Paid',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Payroll $record) => $record->status === 'Draft')
                    ->action(fn (Payroll $record) => $record->update([
                        'status' => 'Approved',
                        'approved_by' => auth()->id(),
                    ])),
                Tables\Actions\Action::make('markPaid')
                    ->label('Mark paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Payroll $record) => $record->status === 'Approved')
                    ->action(fn (Payroll $record) => $record->update([
                        'status' => 'Paid',
                        'payment_date' => $record->payment_date ?? now(),
                    ])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit' => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
