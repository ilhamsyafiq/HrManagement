<?php

namespace App\Http\Controllers;

use App\Services\AttendanceService;
use Illuminate\Http\Request;

class ClockController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index()
    {
        return view('clock.index');
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric',
            'is_mock' => 'nullable|boolean',
            'is_wfh' => 'nullable|boolean',
        ]);

        try {
            $attendance = $this->attendanceService->clockIn(
                $request->lat,
                $request->lng,
                $request->accuracy,
                (bool) $request->is_mock,
                (bool) $request->is_wfh
            );
            return response()->json(['success' => true, 'attendance' => $attendance]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric',
            'is_mock' => 'nullable|boolean',
        ]);

        try {
            $attendance = $this->attendanceService->clockOut(
                $request->lat,
                $request->lng,
                $request->accuracy,
                (bool) $request->is_mock
            );
            return response()->json(['success' => true, 'attendance' => $attendance]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function breakIn(Request $request)
    {
        $request->validate([
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
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
}
