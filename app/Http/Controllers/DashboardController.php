<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Document;
use App\Models\Leave;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::now('Asia/Kuala_Lumpur')->toDateString();

        // Today's attendance with breaks eager loaded
        $todayAttendance = Attendance::with('breaks')->where('user_id', $user->id)->where('date', $today)->first();

        // Recent attendances
        $recentAttendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(7)
            ->get();

        // Pending leaves
        $pendingLeaves = Leave::where('user_id', $user->id)->where('status', 'Pending')->count();

        // Recent leaves
        $recentLeaves = Leave::where('user_id', $user->id)->orderBy('created_at', 'desc')->take(5)->get();

        // Important announcements for popup (high priority or upcoming within 3 days)
        $popupAnnouncements = Announcement::with('creator')
            ->active()
            ->forUser($user)
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

        // Intern-specific dashboard
        if ($user->isIntern()) {
            $reports = Document::where('type', 'Internship Report')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $reportStats = [
                'total' => $reports->count(),
                'draft' => $reports->where('status', 'draft')->count(),
                'pending' => $reports->where('status', 'pending')->count(),
                'signed' => $reports->where('status', 'signed')->count(),
                'rejected' => $reports->where('status', 'rejected')->count(),
                'revised' => $reports->where('status', 'revised')->count(),
            ];

            return view('intern.dashboard', compact(
                'todayAttendance', 'recentAttendances', 'pendingLeaves', 'recentLeaves',
                'reports', 'reportStats', 'popupAnnouncements'
            ));
        }

        return view('dashboard', compact('todayAttendance', 'recentAttendances', 'pendingLeaves', 'recentLeaves', 'popupAnnouncements'));
    }

    public function showSupervisor()
    {
        $user = Auth::user();

        if ($user->isSupervisor()) {
            // Supervisors view their interns
            $interns = $user->subordinates()->where('is_intern', true)->get();
            $internRole = Role::where('name', 'Intern')->first();

            return view('supervisor.show', compact('interns', 'internRole'));
        } else {
            // Employees view their supervisor
            if (!$user->supervisor) {
                abort(404, 'No supervisor assigned.');
            }

            $supervisor = $user->supervisor;

            // Recent attendances of supervisor
            $recentAttendances = Attendance::where('user_id', $supervisor->id)
                ->orderBy('date', 'desc')
                ->take(7)
                ->get();

            // Recent leaves of supervisor
            $recentLeaves = Leave::where('user_id', $supervisor->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $internRole = Role::where('name', 'Intern')->first();
            return view('supervisor.show', compact('supervisor', 'recentAttendances', 'recentLeaves', 'internRole'));
        }
    }
}
