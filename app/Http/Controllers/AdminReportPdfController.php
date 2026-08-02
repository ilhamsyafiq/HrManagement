<?php

namespace App\Http\Controllers;

use App\Services\ReportPdfService;
use Illuminate\Http\Request;

/**
 * Streams the admin statistics PDFs (Attendance / Leave / Employee / Department
 * / Monthly Summary / Audit) INLINE so the Filament Reports page can preview them
 * in a modal before the admin downloads or prints. Pass ?download=1 to force an
 * attachment download instead. Restricted to Admin / Super Admin.
 */
class AdminReportPdfController extends Controller
{
    public function show(Request $request, string $type, ReportPdfService $service)
    {
        $user = auth()->user();
        if (! $user || ! ($user->isAdmin() || $user->isSuperAdmin())) {
            abort(403);
        }

        // Normalise dates to Y-m-d (the picker can emit a full datetime) so the
        // report "Period" line and the date-range query stay date-only.
        $toDate = fn ($v) => $v ? date('Y-m-d', strtotime($v)) : null;

        $filters = [
            'start_date' => $toDate($request->query('start_date')),
            'end_date' => $toDate($request->query('end_date')),
            'department_id' => $request->query('department_id'),
            'user_id' => $request->query('user_id'),
        ];

        switch ($type) {
            case 'attendance':
                $pdf = $service->attendance($filters);
                $name = 'attendance_report';
                break;
            case 'leave':
                $pdf = $service->leave($filters);
                $name = 'leave_report';
                break;
            case 'employee':
                $pdf = $service->employee($filters);
                $name = 'employee_report';
                break;
            case 'department':
                $pdf = $service->department($filters);
                $name = 'department_report';
                break;
            case 'monthlySummary':
                $start = $filters['start_date'] ?: now()->startOfMonth()->format('Y-m-d');
                $pdf = $service->monthlySummary([
                    'month' => date('m', strtotime($start)),
                    'year' => date('Y', strtotime($start)),
                    'department_id' => $filters['department_id'],
                ]);
                $name = 'monthly_summary';
                break;
            case 'audit':
                $pdf = $service->audit($filters);
                $name = 'audit_report';
                break;
            default:
                abort(404);
        }

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';
        $filename = $name . '_' . date('Y-m-d') . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
        ]);
    }
}
