<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'People';

    protected static ?int $navigationSort = 2;

    // Eager-load relations shown in the table to eliminate N+1 queries.
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['user', 'editor', 'breaks']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Attendance')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Employee')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->required(),
                        Forms\Components\DateTimePicker::make('clock_in')
                            ->seconds(false),
                        Forms\Components\DateTimePicker::make('clock_out')
                            ->seconds(false)
                            ->afterOrEqual('clock_in'),
                        Forms\Components\Toggle::make('is_wfh')
                            ->label('Work from home'),
                        Forms\Components\TextInput::make('total_work_hours')
                            ->numeric()
                            ->suffix('hours'),
                    ]),
                Forms\Components\Section::make('Flags')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_late'),
                        Forms\Components\Toggle::make('is_early_leave'),
                        Forms\Components\TextInput::make('late_minutes')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('early_leave_minutes')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('location_flagged'),
                        Forms\Components\TextInput::make('location_flag_reason')
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make('Manual edit audit')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        Forms\Components\Toggle::make('is_manually_edited'),
                        Forms\Components\Select::make('edited_by')
                            ->label('Edited by')
                            ->relationship('editor', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Textarea::make('edit_reason')
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('edited_at')
                            ->seconds(false),
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
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clock_in')
                    ->time('H:i')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clock_out')
                    ->time('H:i')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('formatted_work_hours')
                    ->label('Work hours'),
                Tables\Columns\TextColumn::make('formatted_overtime')
                    ->label('Overtime')
                    ->badge()
                    ->color(fn (string $state) => $state === '-' ? 'gray' : 'info')
                    ->placeholder('-'),
                Tables\Columns\IconColumn::make('is_wfh')
                    ->label('WFH')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(function (Attendance $record) {
                        if ($record->is_late && $record->is_early_leave) {
                            return 'Late & Early Leave';
                        }
                        if ($record->is_late) {
                            return 'Late';
                        }
                        if ($record->is_early_leave) {
                            return 'Early Leave';
                        }

                        return $record->clock_in ? 'On Time' : 'Absent';
                    })
                    ->color(fn (string $state) => match ($state) {
                        'On Time' => 'success',
                        'Late', 'Early Leave', 'Late & Early Leave' => 'warning',
                        'Absent' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('location_flagged')
                    ->label('Flagged')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_manually_edited')
                    ->label('Edited')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_wfh')
                    ->label('Work from home'),
                Tables\Filters\TernaryFilter::make('is_late')
                    ->label('Late'),
                Tables\Filters\TernaryFilter::make('location_flagged')
                    ->label('Location flagged'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
