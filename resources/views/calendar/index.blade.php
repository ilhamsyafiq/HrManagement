<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Calendar') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Message --}}
            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-green-700">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Month Navigation --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @php
                        $prevMonth = $month - 1;
                        $prevYear = $year;
                        if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
                        $nextMonth = $month + 1;
                        $nextYear = $year;
                        if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
                    @endphp
                    <a href="{{ route('calendar.index', ['month' => $prevMonth, 'year' => $prevYear]) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Prev
                    </a>
                    <span class="text-2xl font-bold text-gray-800" id="calendarTitle">
                        {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                    </span>
                    <a href="{{ route('calendar.index', ['month' => $nextMonth, 'year' => $nextYear]) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Next
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    @if($isSupervisor)
                        <button onclick="openAddHolidayModal()" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all duration-200 hover:shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Holiday
                        </button>
                    @endif
                    <button onclick="openAddModal()" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all duration-200 hover:shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Event
                    </button>
                </div>
            </div>

            {{-- Calendar Grid --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                {{-- Day Headers --}}
                <div class="grid grid-cols-7 bg-gray-50/80 border-b border-gray-100">
                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayLabel)
                        <div class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider py-3">{{ $dayLabel }}</div>
                    @endforeach
                </div>
                {{-- Day Cells --}}
                <div id="calendarGrid" class="grid grid-cols-7 divide-x divide-gray-50">
                    {{-- Populated by JavaScript --}}
                </div>
            </div>

            {{-- Legend --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <div class="flex flex-wrap items-center gap-6 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <span class="text-gray-600">Public Holiday</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-teal-500"></span>
                        <span class="text-gray-600">Company Holiday</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <span class="text-gray-600">Optional Holiday</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <span class="text-gray-600">My Events</span>
                    </div>
                    @if($isSupervisor)
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                        <span class="text-gray-600">Subordinate Events</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                        <span class="text-gray-600">Today</span>
                    </div>
                </div>
            </div>

            {{-- Upcoming Events --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-base font-semibold text-gray-900">Upcoming Events</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($upcomingEvents as $event)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50/60 transition-colors duration-150">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-12 text-center">
                                    <div class="text-xs font-semibold text-gray-500 uppercase">{{ $event->event_date->format('M') }}</div>
                                    <div class="text-lg font-bold text-gray-900">{{ $event->event_date->format('d') }}</div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $event->title }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full
                                            @if($event->type === 'Personal') bg-blue-50 text-blue-700
                                            @elseif($event->type === 'Meeting') bg-indigo-50 text-indigo-700
                                            @elseif($event->type === 'Deadline') bg-red-50 text-red-700
                                            @elseif($event->type === 'Reminder') bg-amber-50 text-amber-700
                                            @else bg-gray-100 text-gray-700
                                            @endif
                                        ">{{ $event->type }}</span>
                                        @if($event->event_time)
                                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($event->event_time)->format('g:i A') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $event->event_date->format('l') }}
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-sm text-gray-400">
                            No upcoming events.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    {{-- Add Event Modal --}}
    <div id="addEventModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeAddModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Event</h3>
                <form action="{{ route('calendar.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" id="add_title" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Event title">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input type="date" name="event_date" id="add_event_date" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Time (Optional)</label>
                            <input type="time" name="event_time" id="add_event_time" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" id="add_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="Personal">Personal</option>
                            <option value="Meeting">Meeting</option>
                            <option value="Deadline">Deadline</option>
                            <option value="Reminder">Reminder</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                        <textarea name="description" id="add_description" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Brief description..."></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="notify_supervisor" value="1" id="add_notify_supervisor" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="add_notify_supervisor" class="text-sm text-gray-700">Notify my supervisor</label>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="closeAddModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Add Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- View Event Modal --}}
    <div id="viewEventModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeViewModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4" id="view_modal_title">Event Details</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Title</span>
                        <p class="text-sm text-gray-900 mt-0.5" id="view_title"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</span>
                            <p class="text-sm text-gray-900 mt-0.5" id="view_date"></p>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Time</span>
                            <p class="text-sm text-gray-900 mt-0.5" id="view_time"></p>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</span>
                        <p class="mt-0.5" id="view_type_container"></p>
                    </div>
                    <div id="view_description_section">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</span>
                        <p class="text-sm text-gray-700 mt-0.5" id="view_description"></p>
                    </div>
                    <div id="view_user_section" class="hidden">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Created by</span>
                        <p class="text-sm text-gray-900 mt-0.5" id="view_user_name"></p>
                    </div>
                    <div id="view_notify_section">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Supervisor Notification</span>
                        <p class="text-sm text-gray-700 mt-0.5" id="view_notify"></p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-5" id="view_actions">
                    <button type="button" onclick="closeViewModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Close</button>
                    <button type="button" onclick="openEditFromView()" id="btn_edit_event" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Edit</button>
                    <form id="deleteEventForm" method="POST" class="inline" onsubmit="return confirm('Delete this event?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Event Modal --}}
    <div id="editEventModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeEditModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Event</h3>
                <form id="editEventForm" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" id="edit_title" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input type="date" name="event_date" id="edit_event_date" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Time (Optional)</label>
                            <input type="time" name="event_time" id="edit_event_time" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" id="edit_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="Personal">Personal</option>
                            <option value="Meeting">Meeting</option>
                            <option value="Deadline">Deadline</option>
                            <option value="Reminder">Reminder</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                        <textarea name="description" id="edit_description" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="notify_supervisor" value="1" id="edit_notify_supervisor" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="edit_notify_supervisor" class="text-sm text-gray-700">Notify my supervisor</label>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Update Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- View Holiday Modal --}}
    <div id="viewHolidayModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('viewHolidayModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Holiday Details</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Holiday</span>
                        <p class="text-sm text-gray-900 mt-0.5" id="holiday_view_title"></p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</span>
                        <p class="text-sm text-gray-900 mt-0.5" id="holiday_view_date"></p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</span>
                        <p class="mt-0.5" id="holiday_view_type_container"></p>
                    </div>
                    <div id="holiday_view_desc_section">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</span>
                        <p class="text-sm text-gray-700 mt-0.5" id="holiday_view_description"></p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-5">
                    <button type="button" onclick="document.getElementById('viewHolidayModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Close</button>
                    @if($isSupervisor)
                        <button type="button" onclick="editHolidayFromView()" id="btn_edit_holiday" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Edit</button>
                        <form id="deleteHolidayForm" method="POST" class="inline" onsubmit="return confirm('Delete this holiday?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Add Holiday Modal --}}
    @if($isSupervisor)
    <div id="addHolidayModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('addHolidayModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Holiday</h3>
                <form action="{{ route('holidays.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Holiday Name</label>
                        <input type="text" name="name" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="e.g. Hari Raya Aidilfitri">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" name="date" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="Public">Public Holiday</option>
                            <option value="Company">Company Holiday</option>
                            <option value="Optional">Optional Holiday</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                        <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Brief description..."></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_recurring" value="1" id="holiday_is_recurring" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="holiday_is_recurring" class="text-sm text-gray-700">Recurring annually</label>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('addHolidayModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition">Add Holiday</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Holiday Modal --}}
    <div id="editHolidayModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('editHolidayModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Holiday</h3>
                <form id="editHolidayForm" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Holiday Name</label>
                        <input type="text" name="name" id="edit_holiday_name" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" name="date" id="edit_holiday_date" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" id="edit_holiday_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="Public">Public Holiday</option>
                            <option value="Company">Company Holiday</option>
                            <option value="Optional">Optional Holiday</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                        <textarea name="description" id="edit_holiday_description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_recurring" value="1" id="edit_holiday_is_recurring" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="edit_holiday_is_recurring" class="text-sm text-gray-700">Recurring annually</label>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('editHolidayModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Update Holiday</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
        const currentMonth = {{ $month }};
        const currentYear = {{ $year }};
        let calendarEvents = [];
        let calendarHolidays = [];
        let currentViewEvent = null;
        let currentViewHoliday = null;

        const todayStr = new Date().toISOString().split('T')[0];

        // Fetch events and holidays data
        function loadCalendarData() {
            fetch(`{{ route('calendar.events-data') }}?month=${currentMonth}&year=${currentYear}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                calendarEvents = data.events;
                calendarHolidays = data.holidays;
                renderCalendar();
            })
            .catch(error => {
                console.error('Error loading calendar data:', error);
                renderCalendar();
            });
        }

        function renderCalendar() {
            const grid = document.getElementById('calendarGrid');
            grid.innerHTML = '';

            const firstDay = new Date(currentYear, currentMonth - 1, 1);
            const lastDay = new Date(currentYear, currentMonth, 0);
            const daysInMonth = lastDay.getDate();

            // Monday = 0, Sunday = 6
            let startDay = firstDay.getDay() - 1;
            if (startDay < 0) startDay = 6;

            const totalCells = Math.ceil((startDay + daysInMonth) / 7) * 7;

            for (let i = 0; i < totalCells; i++) {
                const dayNum = i - startDay + 1;
                const cell = document.createElement('div');
                cell.className = 'min-h-[100px] border-b border-gray-50 p-2';

                if (dayNum < 1 || dayNum > daysInMonth) {
                    cell.classList.add('bg-gray-50/30');
                    grid.appendChild(cell);
                    continue;
                }

                const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
                const isToday = dateStr === todayStr;
                const isWeekend = (i % 7 === 5) || (i % 7 === 6); // Sat or Sun

                // Day number
                const dayLabel = document.createElement('div');
                dayLabel.className = 'flex items-center justify-between mb-1';

                const dayNumber = document.createElement('span');
                dayNumber.textContent = dayNum;
                if (isToday) {
                    dayNumber.className = 'inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-bold';
                } else if (isWeekend) {
                    dayNumber.className = 'text-xs font-medium text-gray-400';
                } else {
                    dayNumber.className = 'text-xs font-medium text-gray-700';
                }
                dayLabel.appendChild(dayNumber);

                // Add button for this day
                const addBtn = document.createElement('button');
                addBtn.className = 'opacity-0 group-hover:opacity-100 text-gray-400 hover:text-indigo-600 transition';
                addBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>';
                addBtn.onclick = function(e) {
                    e.stopPropagation();
                    openAddModal(dateStr);
                };
                dayLabel.appendChild(addBtn);

                cell.appendChild(dayLabel);
                cell.className += ' group cursor-pointer hover:bg-indigo-50/30 transition-colors duration-150';
                cell.onclick = function() { openAddModal(dateStr); };

                // Holidays for this day
                const dayHolidays = calendarHolidays.filter(h => h.date === dateStr);
                dayHolidays.forEach(holiday => {
                    const tag = document.createElement('div');
                    let bgClass = 'bg-green-100 text-green-800';
                    if (holiday.type === 'Company') bgClass = 'bg-teal-100 text-teal-800';
                    else if (holiday.type === 'Optional') bgClass = 'bg-amber-100 text-amber-800';
                    tag.className = `text-xs px-1.5 py-0.5 rounded ${bgClass} mb-1 truncate cursor-pointer font-medium`;
                    tag.textContent = holiday.title;
                    tag.onclick = function(e) {
                        e.stopPropagation();
                        viewHoliday(holiday);
                    };
                    cell.appendChild(tag);
                });

                // Events for this day
                const dayEvents = calendarEvents.filter(ev => ev.event_date === dateStr);
                dayEvents.forEach(event => {
                    const tag = document.createElement('div');
                    let bgClass = event.is_own ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800';
                    tag.className = `text-xs px-1.5 py-0.5 rounded ${bgClass} mb-1 truncate cursor-pointer font-medium`;
                    let label = event.title;
                    if (!event.is_own && event.user_name) {
                        label = event.user_name + ': ' + event.title;
                    }
                    tag.textContent = label;
                    tag.onclick = function(e) {
                        e.stopPropagation();
                        viewEvent(event);
                    };
                    cell.appendChild(tag);
                });

                grid.appendChild(cell);
            }
        }

        // Modal helpers
        function openAddModal(dateStr) {
            document.getElementById('add_title').value = '';
            document.getElementById('add_event_date').value = dateStr || '';
            document.getElementById('add_event_time').value = '';
            document.getElementById('add_type').value = 'Personal';
            document.getElementById('add_description').value = '';
            document.getElementById('add_notify_supervisor').checked = false;
            document.getElementById('addEventModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addEventModal').classList.add('hidden');
        }

        function viewEvent(event) {
            currentViewEvent = event;
            document.getElementById('view_title').textContent = event.title;

            const dateObj = new Date(event.event_date + 'T00:00:00');
            document.getElementById('view_date').textContent = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

            if (event.event_time) {
                const timeParts = event.event_time.split(':');
                const timeDate = new Date();
                timeDate.setHours(parseInt(timeParts[0]), parseInt(timeParts[1]));
                document.getElementById('view_time').textContent = timeDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            } else {
                document.getElementById('view_time').textContent = 'All day';
            }

            // Type badge
            const typeContainer = document.getElementById('view_type_container');
            let typeBadgeClass = 'bg-gray-100 text-gray-700';
            if (event.type === 'Personal') typeBadgeClass = 'bg-blue-50 text-blue-700';
            else if (event.type === 'Meeting') typeBadgeClass = 'bg-indigo-50 text-indigo-700';
            else if (event.type === 'Deadline') typeBadgeClass = 'bg-red-50 text-red-700';
            else if (event.type === 'Reminder') typeBadgeClass = 'bg-amber-50 text-amber-700';
            typeContainer.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full ${typeBadgeClass}">${event.type}</span>`;

            // Description
            const descSection = document.getElementById('view_description_section');
            if (event.description) {
                document.getElementById('view_description').textContent = event.description;
                descSection.classList.remove('hidden');
            } else {
                descSection.classList.add('hidden');
            }

            // User name (for subordinate events)
            const userSection = document.getElementById('view_user_section');
            if (!event.is_own && event.user_name) {
                document.getElementById('view_user_name').textContent = event.user_name;
                userSection.classList.remove('hidden');
            } else {
                userSection.classList.add('hidden');
            }

            // Notify supervisor
            document.getElementById('view_notify').textContent = event.notify_supervisor ? 'Yes' : 'No';

            // Show/hide edit and delete buttons
            const btnEdit = document.getElementById('btn_edit_event');
            const deleteForm = document.getElementById('deleteEventForm');
            if (event.is_own) {
                btnEdit.classList.remove('hidden');
                deleteForm.classList.remove('hidden');
                deleteForm.action = `/calendar/${event.id}`;
            } else {
                btnEdit.classList.add('hidden');
                deleteForm.classList.add('hidden');
            }

            document.getElementById('viewEventModal').classList.remove('hidden');
        }

        function closeViewModal() {
            document.getElementById('viewEventModal').classList.add('hidden');
            currentViewEvent = null;
        }

        function openEditFromView() {
            if (!currentViewEvent) return;
            closeViewModal();

            const event = currentViewEvent;
            document.getElementById('editEventForm').action = `/calendar/${event.id}`;
            document.getElementById('edit_title').value = event.title;
            document.getElementById('edit_event_date').value = event.event_date;
            document.getElementById('edit_event_time').value = event.event_time || '';
            document.getElementById('edit_type').value = event.type;
            document.getElementById('edit_description').value = event.description || '';
            document.getElementById('edit_notify_supervisor').checked = event.notify_supervisor;

            document.getElementById('editEventModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editEventModal').classList.add('hidden');
        }

        function viewHoliday(holiday) {
            currentViewHoliday = holiday;
            document.getElementById('holiday_view_title').textContent = holiday.title;

            const dateObj = new Date(holiday.date + 'T00:00:00');
            document.getElementById('holiday_view_date').textContent = dateObj.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

            const typeContainer = document.getElementById('holiday_view_type_container');
            let typeBadgeClass = 'bg-green-50 text-green-700';
            if (holiday.type === 'Company') typeBadgeClass = 'bg-teal-50 text-teal-700';
            else if (holiday.type === 'Optional') typeBadgeClass = 'bg-amber-50 text-amber-700';
            typeContainer.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full ${typeBadgeClass}">${holiday.type} Holiday</span>`;

            const descSection = document.getElementById('holiday_view_desc_section');
            if (holiday.description) {
                document.getElementById('holiday_view_description').textContent = holiday.description;
                descSection.classList.remove('hidden');
            } else {
                descSection.classList.add('hidden');
            }

            // Set delete form action
            const deleteForm = document.getElementById('deleteHolidayForm');
            if (deleteForm) {
                deleteForm.action = `/admin/holidays/${holiday.id}`;
            }

            document.getElementById('viewHolidayModal').classList.remove('hidden');
        }

        function openAddHolidayModal() {
            document.getElementById('addHolidayModal').classList.remove('hidden');
        }

        function editHolidayFromView() {
            if (!currentViewHoliday) return;
            const holiday = currentViewHoliday;

            // Close view modal
            document.getElementById('viewHolidayModal').classList.add('hidden');

            // Populate edit form
            document.getElementById('editHolidayForm').action = `/admin/holidays/${holiday.id}`;
            document.getElementById('edit_holiday_name').value = holiday.title || '';
            document.getElementById('edit_holiday_date').value = holiday.date || '';
            document.getElementById('edit_holiday_type').value = holiday.type || 'Public';
            document.getElementById('edit_holiday_description').value = holiday.description || '';
            document.getElementById('edit_holiday_is_recurring').checked = holiday.is_recurring || false;

            // Open edit modal
            document.getElementById('editHolidayModal').classList.remove('hidden');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadCalendarData();
        });
    </script>
</x-app-layout>
