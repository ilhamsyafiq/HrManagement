<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftAssignmentResource\Pages;
use App\Models\ShiftAssignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShiftAssignmentResource extends Resource
{
    protected static ?string $model = ShiftAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Settings';

    /**
     * Hidden from navigation: the "Shift Roster" page is the single roster
     * view + editor. This resource's create/edit forms are still reachable from
     * the roster cells and the "New assignment" button, but it no longer appears
     * as a separate "Shift Assignments" menu item (removed as a duplicate).
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('shift_id')
                    ->label('Shift')
                    ->relationship('shift', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                // Part-time / flexible: works any day — no need to pick weekdays.
                Forms\Components\Toggle::make('flexible_days')
                    ->label('Flexible days (part-time)')
                    ->helperText('On: this employee can work any day — the shift is applied to every day, so there is no need to tick weekdays.')
                    ->live()
                    ->default(false)
                    ->dehydrated(false)
                    ->visibleOn('create'),
                // Create: assign the same shift to several weekdays at once.
                Forms\Components\CheckboxList::make('days')
                    ->label('Days of week')
                    ->options(ShiftAssignment::DAYS)
                    ->columns(2)
                    ->required(fn (Forms\Get $get) => ! $get('flexible_days'))
                    ->hidden(fn (Forms\Get $get) => (bool) $get('flexible_days'))
                    ->helperText('Tick every day this shift applies. Days left unticked are treated as rest/off days.')
                    ->dehydrated(false)
                    ->visibleOn('create'),
                // Edit: a single assignment is one weekday.
                Forms\Components\Select::make('day_of_week')
                    ->label('Day of week')
                    ->options(ShiftAssignment::DAYS)
                    ->required()
                    ->hiddenOn('create')
                    ->rule(function (?ShiftAssignment $record, Forms\Get $get) {
                        return function (string $attribute, $value, \Closure $fail) use ($record, $get) {
                            $exists = ShiftAssignment::query()
                                ->where('user_id', $get('user_id'))
                                ->where('day_of_week', $value)
                                ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                                ->exists();

                            if ($exists) {
                                $fail('This employee already has a shift assigned for that day.');
                            }
                        };
                    }),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Day')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ShiftAssignment::DAYS[$state] ?? '—')
                    // Weekend (Sun=0, Sat=6) in amber; weekdays in gray for quick scanning.
                    ->color(fn ($state) => in_array((int) $state, [0, 6]) ? 'warning' : 'gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shift.name')
                    ->label('Shift')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hours')
                    ->label('Hours')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(function (ShiftAssignment $record) {
                        $shift = $record->shift;
                        if (! $shift) {
                            return '—';
                        }
                        if ($shift->is_flexible) {
                            return 'Flexible';
                        }
                        $fmt = fn ($t) => ! $t ? '??' : ($t instanceof \DateTimeInterface ? $t->format('H:i') : substr((string) $t, 0, 5));
                        if ($shift->relationLoaded('segments') && $shift->segments->count()) {
                            return $shift->segments
                                ->map(fn ($seg) => $fmt($seg->start_time) . '–' . $fmt($seg->end_time))
                                ->implode('  +  ');
                        }
                        return $fmt($shift->start_time) . '–' . $fmt($shift->end_time);
                    }),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('day_of_week')
            ->groups([
                Tables\Grouping\Group::make('user.name')
                    ->label('Employee'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('shift_id')
                    ->label('Shift')
                    ->relationship('shift', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('day_of_week')
                    ->label('Day')
                    ->options(ShiftAssignment::DAYS),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'shift.segments']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShiftAssignments::route('/'),
            'create' => Pages\CreateShiftAssignment::route('/create'),
            'edit' => Pages\EditShiftAssignment::route('/{record}/edit'),
        ];
    }
}
