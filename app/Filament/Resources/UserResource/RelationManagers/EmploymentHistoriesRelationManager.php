<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EmploymentHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'employmentHistories';

    protected static ?string $title = 'Employment History';

    protected static ?string $icon = 'heroicon-o-briefcase';

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('action')
                    ->options([
                        'Hired' => 'Hired',
                        'Promotion' => 'Promotion',
                        'Transfer' => 'Transfer',
                        'Salary Adjustment' => 'Salary Adjustment',
                        'Demotion' => 'Demotion',
                        'Resigned' => 'Resigned',
                        'Terminated' => 'Terminated',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('effective_date')->required(),
                Forms\Components\TextInput::make('position')->maxLength(255),
                Forms\Components\TextInput::make('department')->maxLength(255),
                Forms\Components\TextInput::make('salary')->numeric()->prefix('RM')->step('0.01'),
                Forms\Components\Textarea::make('remarks')->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action')
            ->defaultSort('effective_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('effective_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Hired' => 'success',
                        'Promotion', 'Salary Adjustment' => 'info',
                        'Transfer' => 'warning',
                        'Demotion' => 'gray',
                        'Resigned', 'Terminated' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('position')->placeholder('—'),
                Tables\Columns\TextColumn::make('department')->placeholder('—'),
                Tables\Columns\TextColumn::make('salary')->money('MYR')->placeholder('—'),
                Tables\Columns\TextColumn::make('performer.name')->label('By')->placeholder('—')->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['performed_by'] = auth()->id();
                        return $data;
                    }),
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
