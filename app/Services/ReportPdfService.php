<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Leave;
use App\Models\User;

/**
 * Builds the HR PDF reports using FPDF and returns them as raw PDF strings.
 *
 * Each method accepts the filters it needs (date range, department id, user id)
 * so it can be called both from the old Breeze AdminController routes and the
 * new Filament Reports page without any Request dependency.
 *
 * Supervisor scoping is preserved: when the currently authenticated user is a
 * supervisor, results are limited to their subordinates (mirrors the original
 * controller behaviour).
 */
class ReportPdfService
{
    /**
     * Attendance report.
     *
     * @param array{start_date?:string,end_date?:string,department_id?:mixed,user_id?:mixed} $filters
     */
    public function attendance(array $filters): string
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

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Attendance Report', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, 'Period: ' . $startDate . ' to ' . $endDate, 0, 1, 'C');
        $pdf->Cell(0, 10, 'Generated on: ' . now()->format('Y-m-d H:i'), 0, 1, 'C');
        $pdf->Ln(10);

        // Table headers
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(20, 8, 'Date', 1);
        $pdf->Cell(35, 8, 'Employee', 1);
        $pdf->Cell(25, 8, 'Department', 1);
        $pdf->Cell(20, 8, 'Clock In', 1);
        $pdf->Cell(20, 8, 'Clock Out', 1);
        $pdf->Cell(15, 8, 'Hours', 1);
        $pdf->Cell(15, 8, 'Status', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 8);
        foreach ($attendances as $attendance) {
            $pdf->Cell(20, 6, date('Y-m-d', strtotime($attendance->date)), 1);
            $pdf->Cell(35, 6, substr($attendance->user->name, 0, 20), 1);
            $pdf->Cell(25, 6, substr($attendance->user->department->name ?? 'N/A', 0, 15), 1);
            $pdf->Cell(20, 6, $attendance->clock_in ? date('H:i', strtotime($attendance->clock_in)) : '-', 1);
            $pdf->Cell(20, 6, $attendance->clock_out ? date('H:i', strtotime($attendance->clock_out)) : '-', 1);
            $pdf->Cell(15, 6, $attendance->total_hours ?? '-', 1);
            $pdf->Cell(15, 6, $attendance->status ?? 'Present', 1);
            $pdf->Ln();
        }

        return $pdf->Output('S');
    }

    /**
     * Leave report.
     *
     * @param array{start_date?:string,end_date?:string,department_id?:mixed,user_id?:mixed} $filters
     */
    public function leave(array $filters): string
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

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Leave Report', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, 'Period: ' . $startDate . ' to ' . $endDate, 0, 1, 'C');
        $pdf->Cell(0, 10, 'Generated on: ' . now()->format('Y-m-d H:i'), 0, 1, 'C');
        $pdf->Ln(10);

        // Table headers
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(30, 8, 'Employee', 1);
        $pdf->Cell(25, 8, 'Department', 1);
        $pdf->Cell(20, 8, 'Type', 1);
        $pdf->Cell(20, 8, 'Start Date', 1);
        $pdf->Cell(20, 8, 'End Date', 1);
        $pdf->Cell(15, 8, 'Days', 1);
        $pdf->Cell(20, 8, 'Status', 1);
        $pdf->Cell(25, 8, 'Approver', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 8);
        foreach ($leaves as $leave) {
            $pdf->Cell(30, 6, substr($leave->user->name, 0, 20), 1);
            $pdf->Cell(25, 6, substr($leave->user->department->name ?? 'N/A', 0, 15), 1);
            $pdf->Cell(20, 6, substr($leave->type, 0, 15), 1);
            $pdf->Cell(20, 6, date('Y-m-d', strtotime($leave->start_date)), 1);
            $pdf->Cell(20, 6, date('Y-m-d', strtotime($leave->end_date)), 1);
            $pdf->Cell(15, 6, $leave->days, 1);
            $pdf->Cell(20, 6, $leave->status, 1);
            $pdf->Cell(25, 6, substr($leave->approver->name ?? 'N/A', 0, 20), 1);
            $pdf->Ln();
        }

        return $pdf->Output('S');
    }

    /**
     * Employee report.
     *
     * @param array{department_id?:mixed} $filters
     */
    public function employee(array $filters): string
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

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Employee Report', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, 'Generated on: ' . now()->format('Y-m-d H:i'), 0, 1, 'C');
        $pdf->Ln(10);

        // Table headers
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(35, 8, 'Name', 1);
        $pdf->Cell(40, 8, 'Email', 1);
        $pdf->Cell(20, 8, 'Role', 1);
        $pdf->Cell(25, 8, 'Department', 1);
        $pdf->Cell(35, 8, 'Supervisor', 1);
        $pdf->Cell(15, 8, 'Status', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 8);
        foreach ($users as $user) {
            $pdf->Cell(35, 6, substr($user->name, 0, 25), 1);
            $pdf->Cell(40, 6, substr($user->email, 0, 30), 1);
            $pdf->Cell(20, 6, substr($user->role->name ?? 'N/A', 0, 15), 1);
            $pdf->Cell(25, 6, substr($user->department->name ?? 'N/A', 0, 20), 1);
            $pdf->Cell(35, 6, substr($user->supervisor->name ?? 'N/A', 0, 25), 1);
            $pdf->Cell(15, 6, $user->is_intern ? 'Intern' : 'Employee', 1);
            $pdf->Ln();
        }

        return $pdf->Output('S');
    }

    /**
     * Department report. Takes no filters.
     */
    public function department(array $filters = []): string
    {
        $departments = Department::with('users.role')->get();

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Department Report', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, 'Generated on: ' . now()->format('Y-m-d H:i'), 0, 1, 'C');
        $pdf->Ln(10);

        foreach ($departments as $department) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Department: ' . $department->name, 0, 1);
            $pdf->Cell(0, 10, 'Total Employees: ' . $department->users->count(), 0, 1);
            $pdf->Ln(5);

            if ($department->users->count() > 0) {
                // Table headers
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(35, 8, 'Name', 1);
                $pdf->Cell(40, 8, 'Email', 1);
                $pdf->Cell(20, 8, 'Role', 1);
                $pdf->Cell(15, 8, 'Type', 1);
                $pdf->Ln();

                $pdf->SetFont('Arial', '', 8);
                foreach ($department->users as $user) {
                    $pdf->Cell(35, 6, substr($user->name, 0, 25), 1);
                    $pdf->Cell(40, 6, substr($user->email, 0, 30), 1);
                    $pdf->Cell(20, 6, substr($user->role->name ?? 'N/A', 0, 15), 1);
                    $pdf->Cell(15, 6, $user->is_intern ? 'Intern' : 'Employee', 1);
                    $pdf->Ln();
                }
            }
            $pdf->Ln(10);
        }

        return $pdf->Output('S');
    }

    /**
     * Monthly summary report.
     *
     * @param array{month?:mixed,year?:mixed,department_id?:mixed} $filters
     */
    public function monthlySummary(array $filters): string
    {
        $month = $filters['month'] ?? date('m');
        $year = $filters['year'] ?? date('Y');
        $departmentId = $filters['department_id'] ?? null;

        $startDate = $year . '-' . $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        // Get attendance summary
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

        // Get leave summary
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

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Monthly Summary Report', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, 'Period: ' . date('F Y', strtotime($startDate)), 0, 1, 'C');
        $pdf->Cell(0, 10, 'Generated on: ' . now()->format('Y-m-d H:i'), 0, 1, 'C');
        $pdf->Ln(10);

        // Attendance Summary
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Attendance Summary', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, 'Total Attendance Records: ' . $attendances->count(), 0, 1);
        $pdf->Cell(0, 10, 'Present Days: ' . $attendances->where('status', 'Present')->count(), 0, 1);
        $pdf->Cell(0, 10, 'Absent Days: ' . $attendances->where('status', 'Absent')->count(), 0, 1);
        $pdf->Ln(5);

        // Leave Summary
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Leave Summary', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, 'Total Leave Requests: ' . $leaves->count(), 0, 1);
        $pdf->Cell(0, 10, 'Approved Leaves: ' . $leaves->where('status', 'Approved')->count(), 0, 1);
        $pdf->Cell(0, 10, 'Pending Leaves: ' . $leaves->where('status', 'Pending')->count(), 0, 1);
        $pdf->Cell(0, 10, 'Rejected Leaves: ' . $leaves->where('status', 'Rejected')->count(), 0, 1);

        return $pdf->Output('S');
    }

    /**
     * Audit report.
     *
     * @param array{start_date?:string,end_date?:string} $filters
     */
    public function audit(array $filters): string
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->format('Y-m-d');

        $logs = AuditLog::with('user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Audit Report', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, 'Period: ' . $startDate . ' to ' . $endDate, 0, 1, 'C');
        $pdf->Cell(0, 10, 'Generated on: ' . now()->format('Y-m-d H:i'), 0, 1, 'C');
        $pdf->Ln(10);

        // Table headers
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(25, 8, 'Date/Time', 1);
        $pdf->Cell(30, 8, 'User', 1);
        $pdf->Cell(25, 8, 'Action', 1);
        $pdf->Cell(50, 8, 'Description', 1);
        $pdf->Cell(30, 8, 'IP Address', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 8);
        foreach ($logs as $log) {
            $pdf->Cell(25, 6, $log->created_at->format('m/d/Y H:i'), 1);
            $pdf->Cell(30, 6, substr($log->user->name ?? 'System', 0, 20), 1);
            $pdf->Cell(25, 6, substr($log->action, 0, 20), 1);
            $pdf->Cell(50, 6, substr($log->model . ' #' . $log->model_id, 0, 40), 1);
            $pdf->Cell(30, 6, $log->ip_address ?? '-', 1);
            $pdf->Ln();
        }

        return $pdf->Output('S');
    }
}
