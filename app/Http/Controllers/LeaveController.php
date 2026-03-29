<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $leaves = Leave::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(20);

        return view('leave.index', compact('leaves'));
    }

    public function create()
    {
        return view('leave.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:AL,MC,Emergency,Intern',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $documentPath = null;

        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('leaves', 'public');
        }

        $leave = Leave::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'document_path' => $documentPath,
        ]);

        return redirect()->route('leave.index')->with('success', 'Leave application submitted successfully');
    }

    public function show($id)
    {
        $leave = Leave::findOrFail($id);
        $this->authorize('view', $leave);

        return view('leave.show', compact('leave'));
    }

    public function approve(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);
        $user = Auth::user();

        $this->authorize('approve', $leave);

        $oldStatus = $leave->status;

        // Two-tier approval for intern leaves:
        // Supervisor approves Pending -> "Supervisor Approved"
        // Admin/Super Admin approves "Supervisor Approved" -> "Approved"
        if ($user->isSupervisor() && $leave->user->isIntern()) {
            $newStatus = 'Supervisor Approved';
            $message = 'Leave approved by supervisor. Awaiting admin final approval.';
        } else {
            $newStatus = 'Approved';
            $message = 'Leave approved successfully.';
        }

        $leave->update([
            'status' => $newStatus,
            'approved_by' => $user->id,
            'approved_at' => now('Asia/Kuala_Lumpur'),
        ]);

        // Log audit
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'approve_leave',
            'model' => 'Leave',
            'model_id' => $leave->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', $message);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $leave = Leave::findOrFail($id);
        $user = Auth::user();

        $this->authorize('approve', $leave);

        $oldStatus = $leave->status;

        $leave->update([
            'status' => 'Rejected',
            'approved_by' => $user->id,
            'approved_at' => now('Asia/Kuala_Lumpur'),
            'rejection_reason' => $request->reason,
        ]);

        // Log audit
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'reject_leave',
            'model' => 'Leave',
            'model_id' => $leave->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'Rejected'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Leave rejected');
    }

    public function downloadDocument($id)
    {
        $leave = Leave::findOrFail($id);
        $user = Auth::user();

        if (!$leave->document_path) {
            abort(404, 'No document attached to this leave application.');
        }

        // Only the leave owner, admins, super admins, or the user's supervisor may download
        $canDownload = $leave->user_id === $user->id
            || $user->isSuperAdmin()
            || $user->isAdmin()
            || ($user->isSupervisor() && $leave->user->supervisor_id === $user->id);

        if (!$canDownload) {
            abort(403);
        }

        return Storage::disk('public')->download($leave->document_path);
    }

    public function pendingApprovals()
    {
        $user = Auth::user();

        if ($user->isSupervisor()) {
            $leaves = Leave::with('user')->whereHas('user', function ($q) use ($user) {
                $q->where('supervisor_id', $user->id);
            })->where('status', 'Pending')->latest()->paginate(20);
        } elseif ($user->isAdmin() || $user->isSuperAdmin()) {
            $leaves = Leave::with('user')
                ->where(function ($query) {
                    $query->where('status', 'Pending')
                        ->whereHas('user', function ($q) {
                            $q->where('is_intern', false);
                        });
                })
                ->orWhere(function ($query) {
                    $query->where('status', 'Supervisor Approved');
                })
                ->latest()->paginate(20);
        } else {
            $leaves = collect();
        }

        return view('leave.approvals', compact('leaves'));
    }
}
