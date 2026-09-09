<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /** Per-instance memos to avoid re-querying on repeated capability checks. */
    protected ?bool $supervisorFlag = null;
    protected ?bool $hodFlag = null;

    /**
     * Only Admin and Super Admin may access the Filament /admin panel.
     * Employee self-service stays in the existing Blade UI.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() || $this->isSuperAdmin();
    }

    protected $with = ['role'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'department_id',
        'supervisor_id',
        'shift_id',
        'employment_type',
        'is_intern',
        'internship_start_date',
        'internship_end_date',
        'university',
        'academic_supervisor_name',
        'academic_supervisor_contact',
        'course',
        'internship_weeks',
        'al_entitlement',
        'mc_entitlement',
        'emergency_entitlement',
        'dashboard_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'internship_start_date' => 'date',
            'internship_end_date' => 'date',
            'dashboard_preferences' => 'array',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function shiftAssignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    /**
     * The shift this user works on the weekday of the given date, or null if
     * that weekday is a rest/off day (or they have no weekly roster at all).
     */
    public function shiftForDate(\Carbon\Carbon $date): ?Shift
    {
        return $this->shiftAssignments()
            ->where('day_of_week', $date->dayOfWeek)
            ->with('shift')
            ->first()?->shift;
    }

    /**
     * True when this user is scheduled via the weekly shift roster.
     */
    public function hasWeeklyRoster(): bool
    {
        return $this->shiftAssignments()->exists();
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    /** Departments this user is the Head (HOD) of. */
    public function headedDepartments()
    {
        return $this->hasMany(Department::class, 'hod_id');
    }

    /** True when this user is the Head of any department. */
    public function isHod(): bool
    {
        if ($this->hodFlag !== null) {
            return $this->hodFlag;
        }

        return $this->hodFlag = $this->headedDepartments()->exists();
    }

    /**
     * Does $user report to this user? True when either $user is a direct report
     * (supervisor_id) OR this user is the HOD of $user's department. An HOD acts
     * as the supervisor for everyone in the department(s) they head.
     */
    public function supervises(User $user): bool
    {
        if ($user->supervisor_id === $this->id) {
            return true;
        }

        return $user->department_id !== null
            && Department::where('id', $user->department_id)
                ->where('hod_id', $this->id)
                ->exists();
    }

    /**
     * IDs of everyone this user supervises: direct reports, plus (if they are an
     * HOD) all members of the department(s) they head. Excludes self.
     */
    public function supervisedUserIds(): \Illuminate\Support\Collection
    {
        $ids = $this->subordinates()->pluck('id');

        $headedDeptIds = $this->headedDepartments()->pluck('id');
        if ($headedDeptIds->isNotEmpty()) {
            $ids = $ids->merge(
                static::whereIn('department_id', $headedDeptIds)
                    ->where('id', '!=', $this->id)
                    ->pluck('id')
            );
        }

        return $ids->unique()->values();
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot('last_read_message_id')
            ->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function leaveEntitlements()
    {
        return $this->hasMany(LeaveEntitlement::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function profile()
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function employeeDocuments()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function employmentHistories()
    {
        return $this->hasMany(EmploymentHistory::class)->orderByDesc('effective_date');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    public function calendarEvents()
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function workingHours()
    {
        return $this->hasOne(WorkingHour::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function isSuperAdmin()
    {
        return $this->role->name === 'Super Admin';
    }

    public function isAdmin()
    {
        return $this->role->name === 'Admin';
    }

    public function isSupervisor()
    {
        // Relationship-driven: a user is a supervisor because people report to
        // them — either directly (supervisor_id) or because they are the HOD of a
        // department (an HOD supervises everyone in their department). The old
        // dedicated "Supervisor" role has been retired.
        //
        // Admins / Super Admins are deliberately excluded: they are handled by
        // their own role checks, and role-branching logic elsewhere (e.g.
        // pending-approval queues, RecipientResolver) checks isSupervisor()
        // BEFORE isAdmin(), so returning false here keeps that routing correct.
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return false;
        }

        if ($this->supervisorFlag !== null) {
            return $this->supervisorFlag;
        }

        return $this->supervisorFlag = $this->subordinates()->exists() || $this->isHod();
    }

    public function isEmployee()
    {
        return $this->role->name === 'Employee';
    }

    public function isIntern()
    {
        return $this->role->name === 'Intern';
    }

    /**
     * Part-timers work task-based / flexible hours: no late or early-leave
     * tracking, paid by actual hours worked, and no AL/MC/Emergency leave.
     */
    public function isPartTime(): bool
    {
        return $this->employment_type === 'part_time';
    }
}
