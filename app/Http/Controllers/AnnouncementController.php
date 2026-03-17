<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->isSuperAdmin() || $user->isAdmin();

        if ($isAdmin) {
            $announcements = Announcement::with(['creator', 'department'])
                ->orderByDesc('created_at')
                ->get();

            $departments = Department::all();
            $roles = Role::all();

            return view('announcements.admin', compact('announcements', 'departments', 'roles'));
        }

        $announcements = Announcement::with('creator')
            ->active()
            ->forUser($user)
            ->orderByRaw("FIELD(priority, 'Urgent', 'High', 'Normal', 'Low')")
            ->orderByDesc('publish_date')
            ->get();

        return view('announcements.index', compact('announcements'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'priority' => 'required|in:Low,Normal,High,Urgent',
            'target' => 'required|in:All,Department,Role',
            'department_id' => 'nullable|required_if:target,Department|exists:departments,id',
            'target_role' => 'nullable|required_if:target,Role|string|max:255',
            'publish_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = $user->id;
        $validated['is_active'] = $request->boolean('is_active');

        if ($validated['target'] !== 'Department') {
            $validated['department_id'] = null;
        }
        if ($validated['target'] !== 'Role') {
            $validated['target_role'] = null;
        }

        Announcement::create($validated);

        return redirect()->route('announcements.index')->with('success', 'Announcement created successfully.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'priority' => 'required|in:Low,Normal,High,Urgent',
            'target' => 'required|in:All,Department,Role',
            'department_id' => 'nullable|required_if:target,Department|exists:departments,id',
            'target_role' => 'nullable|required_if:target,Role|string|max:255',
            'publish_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($validated['target'] !== 'Department') {
            $validated['department_id'] = null;
        }
        if ($validated['target'] !== 'Role') {
            $validated['target_role'] = null;
        }

        $announcement->update($validated);

        return redirect()->route('announcements.index')->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted successfully.');
    }
}
