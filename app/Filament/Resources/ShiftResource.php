<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TimePicker::make('start_time')
                    ->seconds(false)
                    ->hidden(fn (\Filament\Forms\Get $get) => (bool) $get('is_flexible'))
                    ->required(fn (\Filament\Forms\Get $get) => ! $get('is_flexible')),
                Forms\Components\TimePicker::make('end_time')
                    ->seconds(false)
                    ->hidden(fn (\Filament\Forms\Get $get) => (bool) $get('is_flexible'))
                    ->required(fn (\Filament\Forms\Get $get) => ! $get('is_flexible')),
                Forms\Components\TimePicker::make('break_start')
                    ->label('Break start')
                    ->seconds(false)
                    ->helperText('Optional unpaid break, e.g. Friday prayer 12:30.'),
                Forms\Components\TimePicker::make('break_end')
                    ->label('Break end')
                    ->seconds(false)
                    ->after('break_start')
                    ->requiredWith('break_start'),
                Forms\Components\Toggle::make('is_flexible')
                    ->label('Flexible hours')
                    ->helperText('Flexible hours: no late / early-leave tracking; employee only needs to fulfil total hours.')
                    ->default(false),
                Forms\Components\Repeater::make('segments')
                    ->relationship()
                    ->label('Work Segments (leave empty for a single span using the times above)')
                    ->schema([
                        Forms\Components\TimePicker::make('start_time')
                            ->seconds(false)
                            ->required(),
                        Forms\Components\TimePicker::make('end_time')
                            ->seconds(false)
                            ->required(),
                    ])
                    ->orderColumn('sort_order')
                    ->columns(2)
                    ->defaultItems(0)
                    ->addActionLabel('Add work segment')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->time('H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('break')
                    ->label('Break')
                    ->state(fn ($record) => $record->break_start && $record->break_end
                        ? $record->break_start->format('H:i') . '–' . $record->break_end->format('H:i')
                        : '—'),
                Tables\Columns\TextColumn::make('paid_hours')
                    ->label('Paid hrs')
                    ->state(fn ($record) => number_format($record->paidHours(), 2)),
                Tables\Columns\IconColumn::make('is_flexible')
                    ->label('Flexible')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Employees')
                    ->counts('users')
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
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'edit' => Pages\EditShift::route('/{record}/edit'),
        ];
    }
}
