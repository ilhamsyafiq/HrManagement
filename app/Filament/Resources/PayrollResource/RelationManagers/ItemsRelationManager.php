<?php

namespace App\Filament\Resources\PayrollResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Payroll Items';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'Allowance' => 'Allowance',
                        'Deduction' => 'Deduction',
                        'Bonus' => 'Bonus',
                        'Reimbursement' => 'Reimbursement',
                        'Overtime' => 'Overtime',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('RM')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Deduction' => 'danger',
                        'Bonus' => 'success',
                        'Overtime' => 'warning',
                        'Reimbursement' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('MYR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('MYR')),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(40)
                    ->toggleable(),
            ])
            ->defaultSort('type')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'Allowance' => 'Allowance',
                        'Deduction' => 'Deduction',
                        'Bonus' => 'Bonus',
                        'Reimbursement' => 'Reimbursement',
                        'Overtime' => 'Overtime',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
