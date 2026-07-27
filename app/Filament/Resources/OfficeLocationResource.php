<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeLocationResource\Pages;
use App\Models\OfficeLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OfficeLocationResource extends Resource
{
    protected static ?string $model = OfficeLocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('latitude')
                    ->numeric()
                    ->required()
                    ->minValue(-90)
                    ->maxValue(90)
                    ->step('any')
                    ->helperText('Decimal degrees, e.g. 3.13898700'),
                Forms\Components\TextInput::make('longitude')
                    ->numeric()
                    ->required()
                    ->minValue(-180)
                    ->maxValue(180)
                    ->step('any')
                    ->helperText('Decimal degrees, e.g. 101.68685500'),
                Forms\Components\TextInput::make('radius')
                    ->label('Radius (metres)')
                    ->numeric()
                    ->required()
                    ->default(200)
                    ->minValue(1)
                    ->suffix('m')
                    ->helperText('Geofence radius for clock-in/out.'),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->inline(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('latitude')
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitude')
                    ->sortable(),
                Tables\Columns\TextColumn::make('radius')
                    ->label('Radius')
                    ->suffix(' m')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
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
            'index' => Pages\ListOfficeLocations::route('/'),
            'create' => Pages\CreateOfficeLocation::route('/create'),
            'edit' => Pages\EditOfficeLocation::route('/{record}/edit'),
        ];
    }
}
