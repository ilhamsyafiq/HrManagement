<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show($id)
    {
        $current = Auth::user();
        $user = User::findOrFail($id);

        // Allow supervisors to view their interns, admins, or the user themself
        if (!($current->isSuperAdmin() || $current->isAdmin() || $current->id === $user->id || ($current->isSupervisor() && $user->supervisor_id === $current->id))) {
            abort(403);
        }

        $recentAttendances = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->take(7)
            ->get();

        $recentLeaves = Leave::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('user.show', compact('user', 'recentAttendances', 'recentLeaves'));
    }
}
