<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BugReportResource\Pages;
use App\Models\BugReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BugReportResource extends Resource
{
    protected static ?string $model = BugReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Bug Reports';

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Only Super Admin may see the navigation entry.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isSuperAdmin() === true;
    }

    /**
     * Only Super Admin may list/read any records.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperAdmin() === true;
    }

    /**
     * Only Super Admin may access the resource pages.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() === true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed',
                    ])
                    ->default('open')
                    ->required(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Report')
                    ->schema([
                        Infolists\Components\TextEntry::make('type')
                            ->badge()
                            ->color(fn (string $state) => $state === 'bug' ? 'danger' : 'info'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state) => $state === 'open' ? 'warning' : 'success'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Submitted by')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('title')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('page_url')
                            ->label('Page URL')
                            ->url(fn (BugReport $record) => $record->page_url)
                            ->openUrlInNewTab()
                            ->placeholder('—')
                            ->columnSpanFull(),
                        Infolists\Components\ImageEntry::make('image_path')
                            ->label('Attachment')
                            ->disk('public')
                            ->height(300)
                            ->placeholder('No image')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => $state === 'bug' ? 'danger' : 'info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Submitted by')
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => $state === 'open' ? 'warning' : 'success')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'bug' => 'Bug',
                        'feedback' => 'Feedback',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListBugReports::route('/'),
            'view' => Pages\ViewBugReport::route('/{record}'),
            'edit' => Pages\EditBugReport::route('/{record}/edit'),
        ];
    }
}
