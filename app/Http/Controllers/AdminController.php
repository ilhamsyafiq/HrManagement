<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\OfficeLocation;
use App\Mail\UserAccountCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin() && !auth()->user()->isSupervisor()) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        if (auth()->user()->isSupervisor()) {
            $subordinateIds = auth()->user()->subordinates->pluck('id');
            $totalUsers = $subordinateIds->count();
            $totalAttendances = Attendance::whereIn('user_id', $subordinateIds)->count();
            $pendingLeaves = Leave::whereIn('user_id', $subordinateIds)->where('status', 'Pending')->count();
            $recentAudits = collect();
        } else {
            $totalUsers = User::count();
            $totalAttendances = Attendance::count();
            $pendingLeaves = Leave::where('status', 'Pending')->count();
            $recentAudits = AuditLog::with('user')->latest()->take(10)->get();
        }

        // Analytics data
        $departmentCounts = Department::withCount('users')->get();
        $departmentLabels = $departmentCounts->pluck('name')->toArray();
        $departmentData = $departmentCounts->pluck('users_count')->toArray();

        // Monthly attendance trend (last 6 months)
        $attendanceMonths = [];
        $attendanceCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now('Asia/Kuala_Lumpur')->subMonths($i);
            $attendanceMonths[] = $date->format('M Y');
            $attendanceCounts[] = Attendance::whereYear('date', $date->year)
                ->whereMonth('date', $date->month)->count();
        }

        // Leave status breakdown
        $leaveStats = [
            'Approved' => Leave::where('status', 'Approved')->count(),
            'Pending' => Leave::where('status', 'Pending')->count(),
            'Rejected' => Leave::where('status', 'Rejected')->count(),
        ];

        // Today's attendance
        $today = now('Asia/Kuala_Lumpur')->toDateString();
        $presentToday = Attendance::where('date', $today)->count();
        $absentToday = $totalUsers - $presentToday;

        // Important announcements for popup
        $popupAnnouncements = Announcement::with('creator')
            ->where('is_active', true)
            ->where('publish_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now());
            })
            ->where(function ($q) {
                $q->whereIn('priority', ['High', 'Urgent'])
                  ->orWhere(function ($q2) {
                      $q2->where('publish_date', '>=', now()->subDay())
                          ->where('publish_date', '<=', now()->addDays(3));
                  });
            })
            ->orderByRaw("FIELD(priority, 'Urgent', 'High', 'Normal', 'Low')")
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalAttendances', 'pendingLeaves', 'recentAudits',
            'departmentLabels', 'departmentData',
            'attendanceMonths', 'attendanceCounts',
            'leaveStats', 'presentToday', 'absentToday', 'popupAnnouncements'
        ));
    }

    public function users()
    {
        $query = User::with(['role', 'department']);

        // If not Super Admin, exclude Super Admin and Admin users
        if (!auth()->user()->isSuperAdmin()) {
            $query->whereNotIn('role_id', [1, 2]); // Exclude Super Admin (1) and Admin (2)
        }

        $users = $query->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            abort(403, 'Only admins can create users or assign supervisors/interns.');
        }

        $rolesQuery = \App\Models\Role::query();

        // If not Super Admin, exclude Super Admin and Admin roles
        if (!auth()->user()->isSuperAdmin()) {
            $rolesQuery->whereNotIn('id', [1, 2]); // Exclude Super Admin (1) and Admin (2)
        }

        $roles = $rolesQuery->get();
        $departments = \App\Models\Department::all();
        $internRole = \App\Models\Role::where('name', 'Intern')->first();
        // The dedicated Supervisor role was retired; anyone who isn't an intern
        // can be assigned as a supervisor (supervisor powers come from having
        // reports, not from a role).
        $supervisors = User::where('is_intern', false)
            ->when($internRole, fn ($q) => $q->where('role_id', '!=', $internRole->id))
            ->orderBy('name')->get();

        return view('admin.users.create', compact('roles', 'departments', 'supervisors', 'internRole'));
    }

    public function storeUser(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            abort(403, 'Only admins can create users or assign supervisors/interns.');
        }

        // Prevent non-Super Admin from creating Super Admin or Admin users
        if (!auth()->user()->isSuperAdmin() && in_array($request->role_id, [1, 2])) {
            abort(403, 'Unauthorized to create this type of user.');
        }

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'is_intern' => 'boolean',
            'internship_start_date' => 'nullable|date',
            'internship_end_date' => 'nullable|date',
        ]);

        // Determine if user is intern based on role or checkbox
        $isIntern = $request->is_intern || $request->role_id == \App\Models\Role::where('name', 'Intern')->first()->id;

        // If intern, supervisor is required
        if ($isIntern) {
            $request->validate([
                'supervisor_id' => 'required|exists:users,id',
            ]);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'department_id' => $request->department_id,
            'supervisor_id' => $request->supervisor_id,
            'is_intern' => $isIntern,
            'internship_start_date' => $request->internship_start_date,
            'internship_end_date' => $request->internship_end_date,
        ]);

        return redirect()->route('admin.users')->with('success', 'User created successfully');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);

        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            abort(403, 'Only admins can edit users or change supervisor/intern assignments.');
        }

        // Prevent non-Super Admin from editing Super Admin or Admin users
        if (!auth()->user()->isSuperAdmin() && in_array($user->role_id, [1, 2])) {
            abort(403, 'Unauthorized to edit this user.');
        }

        $rolesQuery = \App\Models\Role::query();

        // If not Super Admin, exclude Super Admin and Admin roles
        if (!auth()->user()->isSuperAdmin()) {
            $rolesQuery->whereNotIn('id', [1, 2]); // Exclude Super Admin (1) and Admin (2)
        }

        $roles = $rolesQuery->get();
        $departments = \App\Models\Department::all();
        $internRole = \App\Models\Role::where('name', 'Intern')->first();
        // Supervisor role retired: any non-intern (except this user) can supervise.
        $supervisors = User::where('is_intern', false)
            ->where('id', '!=', $user->id)
            ->when($internRole, fn ($q) => $q->where('role_id', '!=', $internRole->id))
            ->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles', 'departments', 'supervisors', 'internRole'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            abort(403, 'Only admins can update users or change supervisor/intern assignments.');
        }

        // Prevent non-Super Admin from updating to Super Admin or Admin roles
        if (!auth()->user()->isSuperAdmin() && in_array($request->role_id, [1, 2])) {
            abort(403, 'Unauthorized to assign this role.');
        }

        // Prevent non-Super Admin from editing Super Admin or Admin users
        if (!auth()->user()->isSuperAdmin() && in_array($user->role_id, [1, 2])) {
            abort(403, 'Unauthorized to edit this user.');
        }

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'is_intern' => 'boolean',
            'internship_start_date' => 'nullable|date',
            'internship_end_date' => 'nullable|date',
        ]);

        // Determine if user is intern based on role or checkbox
        $isIntern = $request->is_intern || $request->role_id == \App\Models\Role::where('name', 'Intern')->first()->id;

        // If intern, supervisor is required
        if ($isIntern) {
            $request->validate([
                'supervisor_id' => 'required|exists:users,id',
            ]);
        }

        $updateData = $request->only([
            'name', 'email', 'role_id', 'department_id', 'supervisor_id',
            'internship_start_date', 'internship_end_date'
        ]);
        $updateData['is_intern'] = $isIntern;

        // Clear intern fields if not intern
        if (!$isIntern) {
            $updateData['internship_start_date'] = null;
            $updateData['internship_end_date'] = null;
            $updateData['supervisor_id'] = $request->supervisor_id; // Keep supervisor if set
        }

        $user->update($updateData);

        return redirect()->route('admin.users')->with('success', 'User updated successfully');
    }

    public function attendances(Request $request)
    {
        $query = Attendance::with(['user', 'user.department', 'editor']);

        // If supervisor, only show subordinates' attendances
        if (auth()->user()->isSupervisor()) {
            $subordinateIds = auth()->user()->subordinates->pluck('id');
            $query->whereIn('user_id', $subordinateIds);
        }

        // Filter by user
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by month
        if ($request->month) {
            $month = $request->month; // format: YYYY-MM
            $query->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$month]);
        }

        // Filter flagged records
        if ($request->filter === 'flagged') {
            $query->where(function ($q) {
                $q->where('is_late', true)->orWhere('is_early_leave', true);
            });
        } elseif ($request->filter === 'wfh') {
            $query->where('is_wfh', true);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(20)->appends($request->query());
        $filter = $request->filter;
        $users = User::orderBy('name')->get();
        return view('admin.attendances', compact('attendances', 'filter', 'users'));
    }

    public function leaves()
    {
        $query = Leave::with(['user', 'approver']);

        // If supervisor, only show subordinates' leaves
        if (auth()->user()->isSupervisor()) {
            $subordinateIds = auth()->user()->subordinates->pluck('id');
            $query->whereIn('user_id', $subordinateIds);
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.leaves', compact('leaves'));
    }

    public function auditLogs()
    {
        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->paginate(50);
        return view('admin.audit-logs', compact('logs'));
    }

    public function reports()
    {
        $departments = Department::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        return view('admin.reports', compact('departments', 'users'));
    }

    public function generateAttendanceReport(Request $request)
    {
        $pdf = app(\App\Services\ReportPdfService::class)->attendance([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'department_id' => $request->department_id,
            'user_id' => $request->user_id,
        ]);

        $filename = 'attendance_report_' . date('Y-m-d') . '.pdf';
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function generateLeaveReport(Request $request)
    {
        $pdf = app(\App\Services\ReportPdfService::class)->leave([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'department_id' => $request->department_id,
            'user_id' => $request->user_id,
        ]);

        $filename = 'leave_report_' . date('Y-m-d') . '.pdf';
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function generateEmployeeReport(Request $request)
    {
        $pdf = app(\App\Services\ReportPdfService::class)->employee([
            'department_id' => $request->department_id,
        ]);

        $filename = 'employee_report_' . date('Y-m-d') . '.pdf';
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function generateDepartmentReport(Request $request)
    {
        $pdf = app(\App\Services\ReportPdfService::class)->department();

        $filename = 'department_report_' . date('Y-m-d') . '.pdf';
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function generateMonthlySummaryReport(Request $request)
    {
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');

        $pdf = app(\App\Services\ReportPdfService::class)->monthlySummary([
            'month' => $month,
            'year' => $year,
            'department_id' => $request->department_id,
        ]);

        $filename = 'monthly_summary_' . $year . '_' . $month . '.pdf';
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function generateAuditReport(Request $request)
    {
        $pdf = app(\App\Services\ReportPdfService::class)->audit([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        $filename = 'audit_report_' . date('Y-m-d') . '.pdf';
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // ===================== Office Location Management =====================

    public function officeLocations()
    {
        $offices = OfficeLocation::orderBy('name')->get();
        $geofenceEnabled = config('geofence.enabled');
        $defaultRadius = config('geofence.radius');

        return view('admin.office-locations', compact('offices', 'geofenceEnabled', 'defaultRadius'));
    }

    public function storeOfficeLocation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:50|max:5000',
        ]);

        OfficeLocation::create($request->only('name', 'latitude', 'longitude', 'radius'));
        Cache::forget('active_office_locations');

        return redirect()->route('admin.office-locations')->with('success', 'Office location added successfully.');
    }

    public function updateOfficeLocation(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:50|max:5000',
            'is_active' => 'nullable|boolean',
        ]);

        $office = OfficeLocation::findOrFail($id);
        $office->update([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
            'is_active' => $request->has('is_active'),
        ]);

        Cache::forget('active_office_locations');

        return redirect()->route('admin.office-locations')->with('success', 'Office location updated successfully.');
    }

    public function deleteOfficeLocation($id)
    {
        OfficeLocation::findOrFail($id)->delete();
        Cache::forget('active_office_locations');

        return redirect()->route('admin.office-locations')->with('success', 'Office location deleted successfully.');
    }

    public function deleteUser($id)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            abort(403, 'Only Super Admin and Admin can delete users.');
        }

        $user = User::findOrFail($id);

        // Super Admin cannot be deleted
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.users')->with('error', 'Super Admin account cannot be deleted.');
        }

        // Admin cannot delete other admins (only Super Admin can)
        if ($user->isAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.users')->with('error', 'Only Super Admin can delete admin accounts.');
        }

        // Audit logging
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_user',
            'model' => 'User',
            'model_id' => $user->id,
            'old_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name ?? 'N/A',
                'department' => $user->department->name ?? 'N/A',
            ],
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users')->with('success', "User \"{$userName}\" has been deleted successfully.");
    }

    // ===================== Department Management =====================

    public function departments()
    {
        $departments = Department::withCount('users')->with('hod')->orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('admin.departments.index', compact('departments', 'users'));
    }

    public function storeDepartment(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string|max:500',
            'hod_id' => 'nullable|exists:users,id',
        ]);

        $department = Department::create($request->only('name', 'description', 'hod_id'));

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_department',
            'model' => 'Department',
            'model_id' => $department->id,
            'old_values' => null,
            'new_values' => ['name' => $department->name, 'description' => $department->description],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.departments')->with('success', "Department \"{$department->name}\" created successfully.");
    }

    public function updateDepartment(Request $request, $id)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $department = Department::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $id,
            'description' => 'nullable|string|max:500',
            'hod_id' => 'nullable|exists:users,id',
        ]);

        $oldValues = ['name' => $department->name, 'description' => $department->description, 'hod_id' => $department->hod_id];

        $department->update($request->only('name', 'description', 'hod_id'));

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_department',
            'model' => 'Department',
            'model_id' => $department->id,
            'old_values' => $oldValues,
            'new_values' => ['name' => $department->name, 'description' => $department->description, 'hod_id' => $department->hod_id],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.departments')->with('success', "Department \"{$department->name}\" updated successfully.");
    }

    public function deleteDepartment($id)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $department = Department::withCount('users')->findOrFail($id);

        if ($department->users_count > 0) {
            return redirect()->route('admin.departments')->with('error', "Cannot delete \"{$department->name}\" because it still has {$department->users_count} user(s) assigned.");
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_department',
            'model' => 'Department',
            'model_id' => $department->id,
            'old_values' => ['name' => $department->name, 'description' => $department->description],
            'new_values' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $departmentName = $department->name;
        $department->delete();

        return redirect()->route('admin.departments')->with('success', "Department \"{$departmentName}\" deleted successfully.");
    }

    public function assignHod(Request $request, $departmentId)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'hod_id' => 'nullable|exists:users,id',
        ]);

        $department = Department::findOrFail($departmentId);
        $department->update(['hod_id' => $request->hod_id]);

        return redirect()->back()->with('success', "HOD for {$department->name} updated successfully.");
    }
}
