<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'action';

    /**
     * Read-only audit trail: no create/edit/delete.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Event')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('User')
                            ->placeholder('System'),
                        Infolists\Components\TextEntry::make('action')
                            ->badge(),
                        Infolists\Components\TextEntry::make('model')
                            ->label('Entity'),
                        Infolists\Components\TextEntry::make('model_id')
                            ->label('Entity ID'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                    ]),
                Infolists\Components\Section::make('Changes')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('old_values')
                            ->label('Old values')
                            ->columnSpanFull(),
                        Infolists\Components\KeyValueEntry::make('new_values')
                            ->label('New values')
                            ->columnSpanFull(),
                    ]),
                Infolists\Components\Section::make('Request')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('ip_address')
                            ->label('IP address')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('user_agent')
                            ->label('User agent')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('System')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state) => match (true) {
                        str_contains($state, 'delete') => 'danger',
                        str_contains($state, 'create') => 'success',
                        str_contains($state, 'approve') => 'info',
                        str_contains($state, 'reject') => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('model')
                    ->label('Entity')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model_id')
                    ->label('Entity ID')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('changes')
                    ->label('Changes (old → new)')
                    ->state(function (AuditLog $record): string {
                        $old = $record->old_values ?? [];
                        $new = $record->new_values ?? [];
                        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
                        if (empty($keys)) {
                            return '—';
                        }

                        return collect($keys)->take(3)->map(function ($key) use ($old, $new) {
                            $from = is_scalar($old[$key] ?? null) ? ($old[$key] ?? '∅') : '…';
                            $to = is_scalar($new[$key] ?? null) ? ($new[$key] ?? '∅') : '…';

                            return "{$key}: {$from} → {$to}";
                        })->implode('; ');
                    })
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options(fn () => AuditLog::query()
                        ->distinct()
                        ->orderBy('action')
                        ->pluck('action', 'action')
                        ->toArray()),
                Tables\Filters\SelectFilter::make('model')
                    ->label('Entity')
                    ->options(fn () => AuditLog::query()
                        ->distinct()
                        ->orderBy('model')
                        ->pluck('model', 'model')
                        ->toArray()),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('From'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}
