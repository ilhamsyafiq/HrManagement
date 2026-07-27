<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Organization';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('priority')
                    ->options([
                        'Low' => 'Low',
                        'Normal' => 'Normal',
                        'High' => 'High',
                        'Urgent' => 'Urgent',
                    ])
                    ->default('Normal')
                    ->required(),
                Forms\Components\Select::make('target')
                    ->label('Target audience')
                    ->options([
                        'All' => 'All employees',
                        'Department' => 'Specific department',
                        'Role' => 'Specific role',
                    ])
                    ->default('All')
                    ->required()
                    ->live(),
                Forms\Components\Select::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload()
                    ->required(fn (Forms\Get $get) => $get('target') === 'Department')
                    ->visible(fn (Forms\Get $get) => $get('target') === 'Department'),
                Forms\Components\Select::make('target_role')
                    ->label('Role')
                    ->options(fn () => Role::query()->orderBy('name')->pluck('name', 'name'))
                    ->searchable()
                    ->required(fn (Forms\Get $get) => $get('target') === 'Role')
                    ->visible(fn (Forms\Get $get) => $get('target') === 'Role'),
                Forms\Components\DatePicker::make('publish_date')
                    ->required()
                    ->default(now()),
                Forms\Components\DatePicker::make('expiry_date')
                    ->afterOrEqual('publish_date')
                    ->helperText('Leave blank for no expiry.'),
                Forms\Components\Select::make('created_by')
                    ->label('Created by')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->id())
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Urgent' => 'danger',
                        'High' => 'warning',
                        'Normal' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('target')
                    ->label('Audience')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (Announcement $record) => match ($record->target) {
                        'Department' => 'Dept: '.($record->department?->name ?? '—'),
                        'Role' => 'Role: '.($record->target_role ?? '—'),
                        default => 'All',
                    }),
                Tables\Columns\TextColumn::make('publish_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created by')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('publish_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'Low' => 'Low',
                        'Normal' => 'Normal',
                        'High' => 'High',
                        'Urgent' => 'Urgent',
                    ]),
                Tables\Filters\SelectFilter::make('target')
                    ->label('Audience')
                    ->options([
                        'All' => 'All employees',
                        'Department' => 'Department',
                        'Role' => 'Role',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
