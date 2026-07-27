<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'People';

    protected static ?string $recordTitleAttribute = 'title';

    // Eager-load the employee (and supervisor) to eliminate N+1 queries in the table.
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['user', 'supervisor']);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Document')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Employee'),
                        Infolists\Components\TextEntry::make('title')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('type')
                            ->badge(),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'signed' => 'success',
                                'rejected' => 'danger',
                                'pending' => 'warning',
                                'revised' => 'info',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('original_name')
                            ->label('File')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('mime_type')
                            ->label('Type')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('comments')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Infolists\Components\Section::make('Review & Signing')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('supervisor.name')
                            ->label('Supervisor')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('signed_at')
                            ->label('Signed at')
                            ->dateTime()
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Submitted')
                            ->dateTime(),
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
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->placeholder('—')
                    ->limit(40),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Internship Report' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'signed' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        'revised' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('signed_at')
                    ->label('Signed')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'signed' => 'Signed',
                        'revised' => 'Revised',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'AL' => 'AL',
                        'MC' => 'MC',
                        'Attendance Edit' => 'Attendance Edit',
                        'Internship Report' => 'Internship Report',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'view' => Pages\ViewDocument::route('/{record}'),
        ];
    }
}
