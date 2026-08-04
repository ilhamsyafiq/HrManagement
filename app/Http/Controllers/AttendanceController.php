<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Models\Department;
use App\Models\User;
use App\Services\AttendanceCalendarService;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(AttendanceCalendarService $calendarService)
    {
        $user = Auth::user();
        $targetUserId = request('user', $user->id);

        // If supervisor is viewing an intern's attendance
        if ($user->isSupervisor() && $targetUserId != $user->id) {
            $internIds = $user->subordinates()->where('is_intern', true)->pluck('id');
            if (!$internIds->contains($targetUserId)) {
                abort(403, 'Unauthorized access to attendance records.');
            }
        } elseif ($targetUserId != $user->id) {
            abort(403, 'Unauthorized access to attendance records.');
        }

        $targetUser = $targetUserId == $user->id ? $user : User::findOrFail($targetUserId);
        $month = $this->resolveMonth(request('month'));
        $view = request('view') === 'list' ? 'list' : 'calendar';

        // Legacy flat list (kept as a toggle).
        if ($view === 'list') {
            $attendances = Attendance::with('breaks')
                ->where('user_id', $targetUserId)
                ->orderBy('date', 'desc')
                ->paginate(20);

            return view('attendance.index', [
                'view' => 'list',
                'attendances' => $attendances,
                'targetUser' => $targetUser,
                'month' => $month,
                'canViewTeam' => $this->canViewTeam($user),
            ]);
        }

        return view('attendance.index', [
            'view' => 'calendar',
            'calendar' => $calendarService->buildMonth($targetUser, $month->copy()),
            'targetUser' => $targetUser,
            'month' => $month,
            'canViewTeam' => $this->canViewTeam($user),
        ]);
    }

    /**
     * Team attendance matrix (Supervisors / HODs / Admins). Shows each teammate's
     * daily status for a month so leave (AL) can be planned around the team.
     */
    public function team(AttendanceCalendarService $calendarService)
    {
        $user = Auth::user();

        if (! $this->canViewTeam($user)) {
            abort(403, 'You do not have a team to view.');
        }

        $month = $this->resolveMonth(request('month'));
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();

        $departmentId = request('department') ? (int) request('department') : null;
        $members = $this->resolveTeam($user, $departmentId);

        $rows = $members->map(fn (User $m) => [
            'user' => $m,
            'calendar' => $calendarService->buildMonth($m, $month->copy()),
        ])->values();

        // Per-day "on leave" counts across the team, for clash spotting.
        $daysInMonth = $month->daysInMonth;
        $leaveCounts = array_fill(1, $daysInMonth, 0);
        foreach ($rows as $row) {
            foreach ($row['calendar']['days'] as $day) {
                if ($day['type'] === 'leave') {
                    $leaveCounts[$day['day']]++;
                }
            }
        }

        return view('attendance.team', [
            'rows' => $rows,
            'month' => $month,
            'leaveCounts' => $leaveCounts,
            'departments' => $isAdmin ? Department::orderBy('name')->get() : collect(),
            'departmentId' => $departmentId,
            'isAdmin' => $isAdmin,
            // Managers see the leave TYPE (AL/MC/EL); peers only see "on leave"
            // (keeps medical/personal leave private from same-dept colleagues).
            'showLeaveType' => $this->managesAnyone($user),
        ]);
    }

    /**
     * Parse a 'Y-m' month selector, defaulting to the current month.
     */
    private function resolveMonth(?string $input): Carbon
    {
        if ($input) {
            try {
                return Carbon::createFromFormat('Y-m', $input, 'Asia/Kuala_Lumpur')->startOfMonth();
            } catch (\Exception $e) {
                // fall through to current month
            }
        }

        return Carbon::now('Asia/Kuala_Lumpur')->startOfMonth();
    }

    /**
     * Whether the user may see a team view at all. Everyone in a department can
     * see their department; managers/admins additionally covered below.
     */
    private function canViewTeam(User $user): bool
    {
        return $user->isAdmin()
            || $user->isSuperAdmin()
            || $user->department_id !== null
            || $user->subordinates()->exists()
            || Department::where('hod_id', $user->id)->exists();
    }

    /**
     * Whether the user manages anyone (direct reports or a department they head)
     * or is an admin — used to decide if they may see leave *types* vs. just
     * "on leave" for their same-department peers.
     */
    private function managesAnyone(User $user): bool
    {
        return $user->isAdmin()
            || $user->isSuperAdmin()
            || $user->subordinates()->exists()
            || Department::where('hod_id', $user->id)->exists();
    }

    /**
     * The set of users this viewer may see: direct reports + members of any
     * department they head + their own department (so a regular employee sees
     * their same-department colleagues) + themselves. Admins see everyone.
     */
    private function resolveTeam(User $user, ?int $departmentId = null): Collection
    {
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return User::query()
                ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
                ->orderBy('name')
                ->get();
        }

        $ids = $user->subordinates()->pluck('id');

        $deptIds = Department::where('hod_id', $user->id)->pluck('id');
        if ($user->department_id) {
            $deptIds = $deptIds->push($user->department_id);
        }
        if ($deptIds->isNotEmpty()) {
            $ids = $ids->merge(User::whereIn('department_id', $deptIds->unique())->pluck('id'));
        }

        $ids = $ids->push($user->id); // always include self

        return User::whereIn('id', $ids->unique())
            ->orderBy('name')
            ->get();
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        try {
            $attendance = $this->attendanceService->clockIn($request->lat, $request->lng);
            return response()->json(['success' => true, 'attendance' => $attendance]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        try {
            $attendance = $this->attendanceService->clockOut($request->lat, $request->lng);
            return response()->json(['success' => true, 'attendance' => $attendance]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function breakIn(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        try {
            $break = $this->attendanceService->breakIn($request->lat, $request->lng);
            return response()->json(['success' => true, 'break' => $break]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function breakOut(Request $request)
    {
        $request->validate([
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'break_id' => 'required|integer',
        ]);

        try {
            $break = $this->attendanceService->breakOut($request->lat, $request->lng, $request->break_id);
            return response()->json(['success' => true, 'break' => $break]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function edit(Request $request, $id)
    {
        $request->validate([
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $attendance = Attendance::findOrFail($id);

        // Only the record's owner, the owner's supervisor, or an admin may edit.
        $user = Auth::user();
        $isAdmin = $user->isAdmin() || $user->isSuperAdmin();
        $isOwner = $attendance->user_id === $user->id;
        $isOwnersSupervisor = $attendance->user && $attendance->user->supervisor_id === $user->id;
        if (!$isAdmin && !$isOwner && !$isOwnersSupervisor) {
            abort(403);
        }

        $data = [];

        if ($request->clock_in) {
            $data['clock_in'] = $attendance->date->format('Y-m-d') . ' ' . $request->clock_in . ':00';
        }

        if ($request->clock_out) {
            $data['clock_out'] = $attendance->date->format('Y-m-d') . ' ' . $request->clock_out . ':00';
        }

        $this->attendanceService->editAttendance($id, $data, $request->reason, $request->file('document'));

        return redirect()->back()->with('success', 'Attendance updated successfully');
    }

    public function getTodayAttendance()
    {
        $user = Auth::user();
        $today = now('Asia/Kuala_Lumpur')->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        return response()->json([
            'attendance' => $attendance,
            'breaks' => $attendance ? $attendance->breaks : [],
        ]);
    }
}
