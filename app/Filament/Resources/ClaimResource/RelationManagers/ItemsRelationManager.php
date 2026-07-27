<?php

namespace App\Filament\Resources\ClaimResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Claim Items';

    protected static ?string $recordTitleAttribute = 'description';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('RM')
                    ->required(),
                Forms\Components\DatePicker::make('expense_date')
                    ->required(),
                Forms\Components\Select::make('category')
                    ->options([
                        'Transport' => 'Transport',
                        'Meal' => 'Meal',
                        'Accommodation' => 'Accommodation',
                        'Office Supplies' => 'Office Supplies',
                        'Medical' => 'Medical',
                        'Training' => 'Training',
                        'Other' => 'Other',
                    ])
                    ->default('Other')
                    ->required(),
                Forms\Components\FileUpload::make('receipt_path')
                    ->label('Receipt')
                    ->disk('public')
                    ->directory('claim-receipts')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('MYR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->money('MYR')),
                Tables\Columns\TextColumn::make('expense_date')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Transport' => 'Transport',
                        'Meal' => 'Meal',
                        'Accommodation' => 'Accommodation',
                        'Office Supplies' => 'Office Supplies',
                        'Medical' => 'Medical',
                        'Training' => 'Training',
                        'Other' => 'Other',
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
