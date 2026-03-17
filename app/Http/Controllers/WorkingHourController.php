<?php

namespace App\Http\Controllers;

use App\Models\WorkingHour;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkingHourController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $defaultHours = WorkingHour::where('is_default', true)->first();
        $customHours = WorkingHour::whereNotNull('user_id')->with('user')->get();
        $employees = User::whereHas('role', fn($q) => $q->whereNotIn('name', ['Super Admin', 'Admin']))->get();

        return view('admin.working-hours', compact('defaultHours', 'customHours', 'employees'));
    }

    public function updateDefault(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'work_start' => 'required|date_format:H:i',
            'work_end' => 'required|date_format:H:i|after:work_start',
            'break_start' => 'required|date_format:H:i',
            'break_end' => 'required|date_format:H:i|after:break_start',
            'late_threshold_minutes' => 'required|integer|min:1|max:120',
            'early_leave_threshold_minutes' => 'required|integer|min:1|max:120',
        ]);

        WorkingHour::updateOrCreate(
            ['is_default' => true, 'user_id' => null],
            $request->only(['work_start', 'work_end', 'break_start', 'break_end', 'late_threshold_minutes', 'early_leave_threshold_minutes'])
        );

        return redirect()->back()->with('success', 'Default working hours updated successfully.');
    }

    public function storeCustom(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'work_start' => 'required|date_format:H:i',
            'work_end' => 'required|date_format:H:i|after:work_start',
            'break_start' => 'required|date_format:H:i',
            'break_end' => 'required|date_format:H:i|after:break_start',
            'late_threshold_minutes' => 'required|integer|min:1|max:120',
            'early_leave_threshold_minutes' => 'required|integer|min:1|max:120',
        ]);

        WorkingHour::updateOrCreate(
            ['user_id' => $request->user_id],
            $request->only(['work_start', 'work_end', 'break_start', 'break_end', 'late_threshold_minutes', 'early_leave_threshold_minutes'])
        );

        return redirect()->back()->with('success', 'Custom working hours saved.');
    }

    public function deleteCustom($id)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        WorkingHour::where('id', $id)->whereNotNull('user_id')->delete();

        return redirect()->back()->with('success', 'Custom working hours removed.');
    }
}
