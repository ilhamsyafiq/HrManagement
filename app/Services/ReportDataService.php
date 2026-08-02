<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Leave;
use App\Models\User;

/**
 * Builds the on-screen DATA tables that back the Filament Reports page "View Data"
 * modals. It mirrors the exact same Eloquent queries, filters, supervisor scoping
 * and columns used by {@see ReportPdfService} so what the admin previews on screen
 * matches what the PDF will contain — but returns a normalized array structure
 * instead of a rendered PDF string:
 *
 *   ['columns' => ['Date', 'Employee', ...], 'rows' => [[...], [...]]]
 *
 * Dates are formatted as Y-m-d and times as H:i. Callers pass filters with dates
 * already normalized to Y-m-d strings (see Reports::filters()).
 */
class ReportDataService
{
    /**
     * Attendance report data.
     *
     * @param array{start_date?:string,end_date?:string,department_id?:mixed,user_id?:mixed} $filters
     * @return array{columns:array<int,string>,rows:array<int,array<int,string>>}
     */
    public function attendance(array $filters): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->format('Y-m-d');
        $departmentId = $filters['department_id'] ?? null;
        $userId = $filters['user_id'] ?? null;

        $query = Attendance::with(['user', 'user.department'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($departmentId) {
            $query->whereHas('user', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if (auth()->user() && auth()->user()->isSupervisor()) {
            $subordinateIds = auth()->user()->subordinates->pluck('id');
            $query->whereIn('user_id', $subordinateIds);
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        $rows = [];
        foreach ($attendances as $attendance) {
            $rows[] = [
                $attendance->date ? date('Y-m-d', strtotime($attendance->date)) : '-',
                $attendance->user->name ?? 'N/A',
                $attendance->user->department->name ?? 'N/A',
                $attendance->clock_in ? date('H:i', strtotime($attendance->clock_in)) : '-',
                $attendance->clock_out ? date('H:i', strtotime($attendance->clock_out)) : '-',
                (string) ($attendance->total_hours ?? '-'),
                $attendance->status ?? 'Present',
            ];
        }

        return [
            'columns' => ['Date', 'Employee', 'Department', 'Clock In', 'Clock Out', 'Hours', 'Status'],
            'rows' => $rows,
        ];
    }

    /**
     * Leave report data.
     *
     * @param array{start_date?:string,end_date?:string,department_id?:mixed,user_id?:mixed} $filters
     * @return array{columns:array<int,string>,rows:array<int,array<int,string>>}
     */
    public function leave(array $filters): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->format('Y-m-d');
        $departmentId = $filters['department_id'] ?? null;
        $userId = $filters['user_id'] ?? null;

        $query = Leave::with(['user', 'user.department', 'approver'])
            ->whereBetween('start_date', [$startDate, $endDate]);

        if ($departmentId) {
            $query->whereHas('user', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if (auth()->user() && auth()->user()->isSupervisor()) {
            $subordinateIds = auth()->user()->subordinates->pluck('id');
            $query->whereIn('user_id', $subordinateIds);
        }

        $leaves = $query->orderBy('created_at', 'desc')->get();

        $rows = [];
        foreach ($leaves as $leave) {
            $rows[] = [
                $leave->user->name ?? 'N/A',
                $leave->user->department->name ?? 'N/A',
                (string) ($leave->type ?? '-'),
                $leave->start_date ? date('Y-m-d', strtotime($leave->start_date)) : '-',
                $leave->end_date ? date('Y-m-d', strtotime($leave->end_date)) : '-',
                (string) ($leave->days ?? '-'),
                (string) ($leave->status ?? '-'),
                $leave->approver->name ?? 'N/A',
            ];
        }

        return [
            'columns' => ['Employee', 'Department', 'Type', 'Start Date', 'End Date', 'Days', 'Status', 'Approver'],
            'rows' => $rows,
        ];
    }

    /**
     * Employee report data.
     *
     * @param array{department_id?:mixed} $filters
     * @return array{columns:array<int,string>,rows:array<int,array<int,string>>}
     */
    public function employee(array $filters): array
    {
        $departmentId = $filters['department_id'] ?? null;

        $query = User::with(['role', 'department', 'supervisor']);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        // If not Super Admin, exclude Super Admin and Admin users
        if (auth()->user() && !auth()->user()->isSuperAdmin()) {
            $query->whereNotIn('role_id', [1, 2]); // Exclude Super Admin (1) and Admin (2)
        }

        // If supervisor, only show subordinates
        if (auth()->user() && auth()->user()->isSupervisor()) {
            $subordinateIds = auth()->user()->subordinates->pluck('id');
            $query->whereIn('id', $subordinateIds);
        }

        $users = $query->orderBy('name')->get();

        $rows = [];
        foreach ($users as $user) {
            $rows[] = [
                $user->name ?? 'N/A',
                $user->email ?? 'N/A',
                $user->role->name ?? 'N/A',
                $user->department->name ?? 'N/A',
                $user->supervisor->name ?? 'N/A',
                $user->is_intern ? 'Intern' : 'Employee',
            ];
        }

        return [
            'columns' => ['Name', 'Email', 'Role', 'Department', 'Supervisor', 'Status'],
            'rows' => $rows,
        ];
    }

    /**
     * Department report data. Takes no filters. One row per department/employee,
     * with the department name repeated so the flat table stays readable.
     *
     * @param array<string,mixed> $filters
     * @return array{columns:array<int,string>,rows:array<int,array<int,string>>}
     */
    public function department(array $filters = []): array
    {
        $departments = Department::with('users.role')->get();

        $rows = [];
        foreach ($departments as $department) {
            $count = $department->users->count();

            if ($count === 0) {
                $rows[] = [
                    $department->name ?? 'N/A',
                    '0',
                    '-',
                    '-',
                    '-',
                    '-',
                ];
                continue;
            }

            foreach ($department->users as $user) {
                $rows[] = [
                    $department->name ?? 'N/A',
                    (string) $count,
                    $user->name ?? 'N/A',
                    $user->email ?? 'N/A',
                    $user->role->name ?? 'N/A',
                    $user->is_intern ? 'Intern' : 'Employee',
                ];
            }
        }

        return [
            'columns' => ['Department', 'Total Employees', 'Name', 'Email', 'Role', 'Type'],
            'rows' => $rows,
        ];
    }

    /**
     * Monthly summary report data. This report is an aggregate summary rather than
     * a row-per-record listing, so it is returned as Metric / Value pairs. The
     * month/year are derived from the start_date filter to mirror the controller
     * mapping used for the PDF (AdminReportPdfController::show).
     *
     * @param array{start_date?:string,end_date?:string,department_id?:mixed,month?:mixed,year?:mixed} $filters
     * @return array{columns:array<int,string>,rows:array<int,array<int,string>>}
     */
    public function monthlySummary(array $filters): array
    {
        // Derive month/year from start_date when present (matches the PDF route),
        // otherwise fall back to explicit month/year filters or the current month.
        if (!empty($filters['start_date'])) {
            $start = $filters['start_date'];
            $month = date('m', strtotime($start));
            $year = date('Y', strtotime($start));
        } else {
            $month = $filters['month'] ?? date('m');
            $year = $filters['year'] ?? date('Y');
        }
        $departmentId = $filters['department_id'] ?? null;

        $startDate = $year . '-' . $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        // Attendance summary
        $attendanceQuery = Attendance::with('user')
            ->whereBetween('date', [$startDate, $endDate]);

        if ($departmentId) {
            $attendanceQuery->whereHas('user', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if (auth()->user() && auth()->user()->isSupervisor()) {
            $subordinateIds = auth()->user()->subordinates->pluck('id');
            $attendanceQuery->whereIn('user_id', $subordinateIds);
        }

        $attendances = $attendanceQuery->get();

        // Leave summary
        $leaveQuery = Leave::with('user')
            ->whereBetween('start_date', [$startDate, $endDate]);

        if ($departmentId) {
            $leaveQuery->whereHas('user', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if (auth()->user() && auth()->user()->isSupervisor()) {
            $subordinateIds = auth()->user()->subordinates->pluck('id');
            $leaveQuery->whereIn('user_id', $subordinateIds);
        }

        $leaves = $leaveQuery->get();

        $period = date('F Y', strtotime($startDate));

        $rows = [
            ['Period', $period],
            ['Total Attendance Records', (string) $attendances->count()],
            ['Present Days', (string) $attendances->where('status', 'Present')->count()],
            ['Absent Days', (string) $attendances->where('status', 'Absent')->count()],
            ['Total Leave Requests', (string) $leaves->count()],
            ['Approved Leaves', (string) $leaves->where('status', 'Approved')->count()],
            ['Pending Leaves', (string) $leaves->where('status', 'Pending')->count()],
            ['Rejected Leaves', (string) $leaves->where('status', 'Rejected')->count()],
        ];

        return [
            'columns' => ['Metric', 'Value'],
            'rows' => $rows,
        ];
    }

    /**
     * Audit report data.
     *
     * @param array{start_date?:string,end_date?:string} $filters
     * @return array{columns:array<int,string>,rows:array<int,array<int,string>>}
     */
    public function audit(array $filters): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->format('Y-m-d');

        $logs = AuditLog::with('user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $rows = [];
        foreach ($logs as $log) {
            $rows[] = [
                $log->created_at ? $log->created_at->format('Y-m-d H:i') : '-',
                $log->user->name ?? 'System',
                (string) ($log->action ?? '-'),
                trim($log->model . ' #' . $log->model_id),
                $log->ip_address ?? '-',
            ];
        }

        return [
            'columns' => ['Date/Time', 'User', 'Action', 'Description', 'IP Address'],
            'rows' => $rows,
        ];
    }
}
