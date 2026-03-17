<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now('Asia/Kuala_Lumpur')->year);
        $holidays = Holiday::inYear($year)->get();

        return view('holidays.index', compact('holidays', 'year'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:Public,Company,Optional',
            'description' => 'nullable|string|max:1000',
            'is_recurring' => 'boolean',
        ]);

        $validated['created_by'] = $user->id;
        $validated['is_recurring'] = $request->boolean('is_recurring');

        Holiday::create($validated);

        return redirect()->route('holidays.index')->with('success', 'Holiday added successfully.');
    }

    public function update(Request $request, Holiday $holiday)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:Public,Company,Optional',
            'description' => 'nullable|string|max:1000',
            'is_recurring' => 'boolean',
        ]);

        $validated['is_recurring'] = $request->boolean('is_recurring');

        $holiday->update($validated);

        return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $holiday->delete();

        return redirect()->route('holidays.index')->with('success', 'Holiday deleted successfully.');
    }

    public function calendarData(Request $request)
    {
        $year = $request->get('year', now('Asia/Kuala_Lumpur')->year);
        $holidays = Holiday::inYear($year)->get()->map(function ($holiday) {
            return [
                'id' => $holiday->id,
                'title' => $holiday->name,
                'date' => $holiday->date->format('Y-m-d'),
                'type' => $holiday->type,
                'description' => $holiday->description,
            ];
        });

        return response()->json($holidays);
    }
}
