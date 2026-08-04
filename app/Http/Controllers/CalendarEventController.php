<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $month = $request->get('month', now('Asia/Kuala_Lumpur')->month);
        $year = $request->get('year', now('Asia/Kuala_Lumpur')->year);

        $upcomingEvents = $user->calendarEvents()
            ->upcoming()
            ->limit(5)
            ->get();

        $isSupervisor = $user->isSuperAdmin() || $user->isAdmin() || $user->isSupervisor();

        return view('calendar.index', compact('month', 'year', 'upcomingEvents', 'isSupervisor'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_time' => 'nullable|date_format:H:i',
            'type' => 'required|in:Personal,Meeting,Deadline,Reminder,Other',
            'description' => 'nullable|string|max:1000',
            'notify_supervisor' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['notify_supervisor'] = $request->boolean('notify_supervisor');
        $validated['reminder_sent'] = false;

        CalendarEvent::create($validated);

        return redirect()->route('calendar.index', [
            'month' => Carbon::parse($validated['event_date'])->month,
            'year' => Carbon::parse($validated['event_date'])->year,
        ])->with('success', 'Event created successfully.');
    }

    public function update(Request $request, CalendarEvent $event)
    {
        if ($event->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_time' => 'nullable|date_format:H:i',
            'type' => 'required|in:Personal,Meeting,Deadline,Reminder,Other',
            'description' => 'nullable|string|max:1000',
            'notify_supervisor' => 'boolean',
        ]);

        $validated['notify_supervisor'] = $request->boolean('notify_supervisor');

        $event->update($validated);

        return redirect()->route('calendar.index', [
            'month' => Carbon::parse($validated['event_date'])->month,
            'year' => Carbon::parse($validated['event_date'])->year,
        ])->with('success', 'Event updated successfully.');
    }

    public function destroy(CalendarEvent $event)
    {
        if ($event->user_id !== auth()->id()) {
            abort(403);
        }

        $eventDate = $event->event_date;
        $event->delete();

        return redirect()->route('calendar.index', [
            'month' => $eventDate->month,
            'year' => $eventDate->year,
        ])->with('success', 'Event deleted successfully.');
    }

    public function eventsData(Request $request)
    {
        $user = auth()->user();
        $month = $request->get('month', now('Asia/Kuala_Lumpur')->month);
        $year = $request->get('year', now('Asia/Kuala_Lumpur')->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // User's own events
        $myEvents = CalendarEvent::where('user_id', $user->id)
            ->whereBetween('event_date', [$startDate, $endDate])
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'event_date' => $event->event_date->format('Y-m-d'),
                    'event_time' => $event->event_time,
                    'type' => $event->type,
                    'notify_supervisor' => $event->notify_supervisor,
                    'is_own' => true,
                    'user_name' => null,
                ];
            });

        // Subordinate events (for supervisors/admins)
        $subordinateEvents = collect();
        $isSupervisor = $user->isSuperAdmin() || $user->isAdmin() || $user->isSupervisor();

        if ($isSupervisor) {
            $subordinateIds = $user->isSuperAdmin() || $user->isAdmin()
                ? \App\Models\User::where('id', '!=', $user->id)->limit(1000)->pluck('id')
                : $user->subordinates()->pluck('id');

            $subordinateEvents = CalendarEvent::whereIn('user_id', $subordinateIds)
                ->where('notify_supervisor', true)
                ->whereBetween('event_date', [$startDate, $endDate])
                ->with('user:id,name')
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'description' => $event->description,
                        'event_date' => $event->event_date->format('Y-m-d'),
                        'event_time' => $event->event_time,
                        'type' => $event->type,
                        'notify_supervisor' => $event->notify_supervisor,
                        'is_own' => false,
                        'user_name' => $event->user->name ?? 'Unknown',
                    ];
                });
        }

        // Holidays for this month
        $holidays = Holiday::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->map(function ($holiday) {
                return [
                    'id' => $holiday->id,
                    'title' => $holiday->name,
                    'date' => $holiday->date->format('Y-m-d'),
                    'type' => $holiday->type,
                    'description' => $holiday->description,
                    'is_recurring' => (bool) $holiday->is_recurring,
                    'is_holiday' => true,
                ];
            });

        // Leaves overlapping this month: the user's own, plus their team's when
        // they manage anyone (direct reports / a department they head) or are admin.
        $leaveUserIds = collect([$user->id]);
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $leaveUserIds = \App\Models\User::pluck('id');
        } else {
            $teamIds = $user->subordinates()->pluck('id');
            $headedDeptIds = \App\Models\Department::where('hod_id', $user->id)->pluck('id');
            if ($headedDeptIds->isNotEmpty()) {
                $teamIds = $teamIds->merge(\App\Models\User::whereIn('department_id', $headedDeptIds)->pluck('id'));
            }
            $leaveUserIds = $leaveUserIds->merge($teamIds);
        }

        $leaveLabels = ['AL' => 'Annual Leave', 'MC' => 'Medical Leave', 'Emergency' => 'Emergency Leave', 'Intern' => 'Intern Leave'];
        $leaves = \App\Models\Leave::whereIn('user_id', $leaveUserIds->unique())
            ->whereIn('status', ['Approved', 'Pending'])
            ->whereDate('start_date', '<=', $endDate->toDateString())
            ->whereDate('end_date', '>=', $startDate->toDateString())
            ->with('user:id,name')
            ->get()
            ->map(function ($leave) use ($user, $leaveLabels) {
                return [
                    'id' => $leave->id,
                    'title' => $leaveLabels[$leave->type] ?? $leave->type,
                    'type' => $leave->type,
                    'status' => $leave->status,
                    'start_date' => $leave->start_date->format('Y-m-d'),
                    'end_date' => $leave->end_date->format('Y-m-d'),
                    'is_own' => $leave->user_id === $user->id,
                    'user_name' => $leave->user->name ?? 'Unknown',
                ];
            })
            ->values();

        return response()->json([
            'events' => $myEvents->merge($subordinateEvents)->values(),
            'holidays' => $holidays,
            'leaves' => $leaves,
            'month' => (int) $month,
            'year' => (int) $year,
        ]);
    }
}
