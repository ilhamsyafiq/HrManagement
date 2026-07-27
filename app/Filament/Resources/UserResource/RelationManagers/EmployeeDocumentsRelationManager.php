<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'employeeDocuments';

    protected static ?string $title = 'Documents';

    protected static ?string $icon = 'heroicon-o-document-text';

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('category')
                    ->options([
                        'Contract' => 'Contract',
                        'Certificate' => 'Certificate',
                        'ID' => 'ID',
                        'Resume' => 'Resume',
                        'Other' => 'Other',
                    ])
                    ->default('Other')
                    ->required(),
                Forms\Components\FileUpload::make('file_path')
                    ->label('File')
                    ->disk('public')
                    ->directory('employee-documents')
                    ->storeFileNamesIn('file_name')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('expiry_date'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Contract' => 'success',
                        'Certificate' => 'info',
                        'ID' => 'warning',
                        'Resume' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('file_name')->label('File')->limit(30),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->placeholder('—')
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : null),
                Tables\Columns\TextColumn::make('uploader.name')->label('Uploaded by')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Contract' => 'Contract',
                        'Certificate' => 'Certificate',
                        'ID' => 'ID',
                        'Resume' => 'Resume',
                        'Other' => 'Other',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['uploaded_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => $record->file_path ? \Storage::disk('public')->url($record->file_path) : null, shouldOpenInNewTab: true)
                    ->visible(fn ($record) => filled($record->file_path)),
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
