<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'People';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    // Eager-load relations shown in the table (role is already globally eager-loaded).
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['profile', 'department', 'supervisor']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context) => $context === 'create')
                            ->maxLength(255)
                            ->helperText('Leave blank to keep the current password when editing.')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Organization')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('role_id')
                            ->label('Role')
                            ->relationship('role', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('department_id')
                            ->label('Department')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('supervisor_id')
                            ->label('Supervisor')
                            ->relationship('supervisor', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('shift_id')
                            ->label('Shift')
                            ->relationship('shift', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
                Forms\Components\Section::make('Internship')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_intern')
                            ->label('Is intern')
                            ->live()
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('internship_start_date')
                            ->visible(fn (Forms\Get $get) => (bool) $get('is_intern')),
                        Forms\Components\DatePicker::make('internship_end_date')
                            ->afterOrEqual('internship_start_date')
                            ->visible(fn (Forms\Get $get) => (bool) $get('is_intern')),
                    ]),
                Forms\Components\Section::make('Leave Balance & Shift')
                    ->description('Read-only summary of the employee\'s current-year leave balance and assigned shift.')
                    ->columns(2)
                    ->visibleOn(['edit', 'view'])
                    ->schema([
                        Forms\Components\Placeholder::make('assigned_shift')
                            ->label('Assigned Shift')
                            ->content(fn (?User $record) => $record?->shift?->name ?? '—'),
                        Forms\Components\Placeholder::make('leave_balance_al')
                            ->label('Annual Leave (AL)')
                            ->content(fn (?User $record) => static::leaveBalanceLine($record, 'AL')),
                        Forms\Components\Placeholder::make('leave_balance_mc')
                            ->label('Medical Leave (MC)')
                            ->content(fn (?User $record) => static::leaveBalanceLine($record, 'MC')),
                        Forms\Components\Placeholder::make('leave_balance_emergency')
                            ->label('Emergency Leave')
                            ->content(fn (?User $record) => static::leaveBalanceLine($record, 'Emergency')),
                    ]),
                Forms\Components\Section::make('Employee Profile')
                    ->description('Personal, banking, and employment details.')
                    ->relationship('profile')
                    ->columns(2)
                    ->collapsed(fn (string $context) => $context === 'edit')
                    ->schema([
                        Forms\Components\FileUpload::make('profile_photo')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('profile-photos')
                            ->columnSpanFull(),

                        // Personal
                        Forms\Components\TextInput::make('phone')->tel()->maxLength(255),
                        Forms\Components\TextInput::make('ic_number')->label('IC number')->maxLength(255),
                        Forms\Components\DatePicker::make('date_of_birth')->maxDate(now()),
                        Forms\Components\Select::make('gender')
                            ->options(['Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other']),
                        Forms\Components\Select::make('marital_status')
                            ->options([
                                'Single' => 'Single',
                                'Married' => 'Married',
                                'Divorced' => 'Divorced',
                                'Widowed' => 'Widowed',
                            ]),

                        // Address
                        Forms\Components\Textarea::make('address')->columnSpanFull(),
                        Forms\Components\TextInput::make('city')->maxLength(255),
                        Forms\Components\TextInput::make('state')->maxLength(255),
                        Forms\Components\TextInput::make('postcode')->maxLength(255),
                        Forms\Components\TextInput::make('country')->default('Malaysia')->maxLength(255),

                        // Emergency contact
                        Forms\Components\TextInput::make('emergency_contact_name')->label('Emergency contact')->maxLength(255),
                        Forms\Components\TextInput::make('emergency_contact_phone')->label('Emergency phone')->tel()->maxLength(255),
                        Forms\Components\TextInput::make('emergency_contact_relationship')->label('Relationship')->maxLength(255),

                        // Banking & statutory
                        Forms\Components\TextInput::make('bank_name')->maxLength(255),
                        Forms\Components\TextInput::make('bank_account_number')->maxLength(255),
                        Forms\Components\TextInput::make('epf_number')->label('EPF number')->maxLength(255),
                        Forms\Components\TextInput::make('socso_number')->label('SOCSO number')->maxLength(255),
                        Forms\Components\TextInput::make('tax_number')->label('Tax number')->maxLength(255),

                        // Employment
                        Forms\Components\TextInput::make('job_title')->maxLength(255),
                        Forms\Components\DatePicker::make('hire_date'),
                        Forms\Components\TextInput::make('basic_salary')->numeric()->prefix('RM')->step('0.01'),
                    ]),
            ]);
    }

    /**
     * Build a "remaining / entitlement (taken used)" summary line for a leave type.
     */
    protected static function leaveBalanceLine(?User $record, string $type): string
    {
        if (! $record) {
            return '—';
        }

        $balance = LeaveBalanceService::for($record);

        if (! isset($balance[$type])) {
            return '—';
        }

        $row = $balance[$type];

        return "{$row['remaining']} / {$row['entitlement']} days remaining ({$row['taken']} taken)";
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile.profile_photo')
                    ->label('')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn (User $record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=random'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record) => $record->profile?->job_title),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('role.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Super Admin' => 'danger',
                        'Admin' => 'warning',
                        'Supervisor' => 'info',
                        'Intern' => 'gray',
                        default => 'success',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('supervisor.name')
                    ->label('Supervisor')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('shift.name')
                    ->label('Shift')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_intern')
                    ->label('Intern')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('role_id')
                    ->label('Role')
                    ->relationship('role', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_intern')
                    ->label('Interns'),
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

    public static function getRelations(): array
    {
        return [
            UserResource\RelationManagers\EmployeeDocumentsRelationManager::class,
            UserResource\RelationManagers\EmploymentHistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
