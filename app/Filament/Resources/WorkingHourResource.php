<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkingHourResource\Pages;
use App\Models\WorkingHour;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkingHourResource extends Resource
{
    protected static ?string $model = WorkingHour::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Leave blank for company default working hours.'),
                Forms\Components\Toggle::make('is_default')
                    ->label('Default hours')
                    ->helperText('Applied to employees without their own schedule.')
                    ->inline(false),
                Forms\Components\TimePicker::make('work_start')
                    ->seconds(false)
                    ->required()
                    ->default('09:00'),
                Forms\Components\TimePicker::make('work_end')
                    ->seconds(false)
                    ->required()
                    ->default('17:30')
                    ->after('work_start'),
                Forms\Components\TimePicker::make('break_start')
                    ->seconds(false)
                    ->default('13:00'),
                Forms\Components\TimePicker::make('break_end')
                    ->seconds(false)
                    ->default('14:00')
                    ->after('break_start'),
                Forms\Components\TextInput::make('late_threshold_minutes')
                    ->label('Late threshold (minutes)')
                    ->numeric()
                    ->required()
                    ->default(15)
                    ->minValue(0)
                    ->suffix('min'),
                Forms\Components\TextInput::make('early_leave_threshold_minutes')
                    ->label('Early leave threshold (minutes)')
                    ->numeric()
                    ->required()
                    ->default(15)
                    ->minValue(0)
                    ->suffix('min'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->placeholder('Default (all employees)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_start')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_end')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('break_start')
                    ->time('H:i')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('break_end')
                    ->time('H:i')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('late_threshold_minutes')
                    ->label('Late (min)')
                    ->suffix(' min')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('early_leave_threshold_minutes')
                    ->label('Early leave (min)')
                    ->suffix(' min')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('is_default', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default hours'),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListWorkingHours::route('/'),
            'create' => Pages\CreateWorkingHour::route('/create'),
            'edit' => Pages\EditWorkingHour::route('/{record}/edit'),
        ];
    }
}
