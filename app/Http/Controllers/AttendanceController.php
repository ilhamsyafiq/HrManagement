<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index()
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

        $attendances = Attendance::with('breaks')
            ->where('user_id', $targetUserId)
            ->orderBy('date', 'desc')
            ->paginate(20);

        return view('attendance.index', compact('attendances'));
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
