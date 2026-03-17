<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Holiday Calendar') }}
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

            {{-- Year Navigation & Add Button --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('holidays.index', ['year' => $year - 1]) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        {{ $year - 1 }}
                    </a>
                    <span class="text-2xl font-bold text-gray-800">{{ $year }}</span>
                    <a href="{{ route('holidays.index', ['year' => $year + 1]) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        {{ $year + 1 }}
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                    <button onclick="document.getElementById('addHolidayModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all duration-200 hover:shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Holiday
                    </button>
                @endif
            </div>

            {{-- Calendar Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @for($month = 1; $month <= 12; $month++)
                    @php
                        $monthDate = \Carbon\Carbon::create($year, $month, 1);
                        $daysInMonth = $monthDate->daysInMonth;
                        $startDay = $monthDate->dayOfWeek; // 0=Sun
                        $monthHolidays = $holidays->filter(fn($h) => $h->date->month === $month);
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50/50 border-b border-gray-100">
                            <h3 class="text-sm font-bold text-gray-800">{{ $monthDate->format('F') }}</h3>
                        </div>
                        <div class="p-3">
                            {{-- Day Headers --}}
                            <div class="grid grid-cols-7 gap-0 mb-1">
                                @foreach(['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $dayLabel)
                                    <div class="text-center text-xs font-semibold text-gray-400 py-1">{{ $dayLabel }}</div>
                                @endforeach
                            </div>
                            {{-- Day Cells --}}
                            <div class="grid grid-cols-7 gap-0">
                                @for($i = 0; $i < $startDay; $i++)
                                    <div class="p-1"></div>
                                @endfor
                                @for($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $currentDate = \Carbon\Carbon::create($year, $month, $day);
                                        $holiday = $monthHolidays->first(fn($h) => $h->date->day === $day);
                                        $isToday = $currentDate->isToday();
                                        $isWeekend = $currentDate->isWeekend();
                                    @endphp
                                    <div class="relative group">
                                        <div class="flex items-center justify-center w-8 h-8 mx-auto rounded-full text-xs font-medium
                                            @if($holiday)
                                                @if($holiday->type === 'Public') bg-red-100 text-red-700
                                                @elseif($holiday->type === 'Company') bg-blue-100 text-blue-700
                                                @else bg-amber-100 text-amber-700
                                                @endif
                                            @elseif($isToday) bg-indigo-600 text-white
                                            @elseif($isWeekend) text-gray-400
                                            @else text-gray-700
                                            @endif
                                        ">
                                            {{ $day }}
                                        </div>
                                        @if($holiday)
                                            <div class="absolute z-10 hidden group-hover:block bottom-full left-1/2 -translate-x-1/2 mb-1 w-40 bg-gray-900 text-white text-xs rounded-lg py-2 px-3 shadow-lg">
                                                <p class="font-semibold">{{ $holiday->name }}</p>
                                                <p class="text-gray-300 mt-0.5">{{ $holiday->type }}</p>
                                                @if($holiday->description)
                                                    <p class="text-gray-400 mt-1">{{ $holiday->description }}</p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                        </div>
                        {{-- Month Holiday List --}}
                        @if($monthHolidays->count())
                            <div class="px-3 pb-3 space-y-1">
                                @foreach($monthHolidays as $h)
                                    <div class="flex items-center justify-between text-xs px-2 py-1.5 rounded-lg
                                        @if($h->type === 'Public') bg-red-50
                                        @elseif($h->type === 'Company') bg-blue-50
                                        @else bg-amber-50
                                        @endif
                                    ">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full shrink-0
                                                @if($h->type === 'Public') bg-red-500
                                                @elseif($h->type === 'Company') bg-blue-500
                                                @else bg-amber-500
                                                @endif
                                            "></span>
                                            <span class="font-medium text-gray-700">{{ $h->name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-500">{{ $h->date->format('d') }}</span>
                                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                                <button onclick="editHoliday({{ $h->id }}, '{{ addslashes($h->name) }}', '{{ $h->date->format('Y-m-d') }}', '{{ $h->type }}', '{{ addslashes($h->description ?? '') }}', {{ $h->is_recurring ? 'true' : 'false' }})" class="text-gray-400 hover:text-blue-600 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                                <form action="{{ route('holidays.destroy', $h) }}" method="POST" class="inline" onsubmit="return confirm('Delete this holiday?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endfor
            </div>

            {{-- Legend --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                <div class="flex flex-wrap items-center gap-6 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        <span class="text-gray-600">Public Holiday</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <span class="text-gray-600">Company Holiday</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <span class="text-gray-600">Optional Holiday</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-indigo-600"></span>
                        <span class="text-gray-600">Today</span>
                    </div>
                </div>
            </div>

            {{-- Upcoming Holidays List --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-base font-semibold text-gray-900">All Holidays in {{ $year }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Day</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Holiday</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($holidays as $holiday)
                                <tr class="hover:bg-gray-50/60 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $holiday->date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $holiday->date->format('l') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $holiday->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                            @if($holiday->type === 'Public') bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20
                                            @elseif($holiday->type === 'Company') bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20
                                            @else bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20
                                            @endif
                                        ">
                                            {{ $holiday->type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $holiday->description ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400">
                                        No holidays found for {{ $year }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Holiday Modal --}}
    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
    <div id="addHolidayModal" class="hidden fixed inset-0 z-50 overflow-y-auto" x-data>
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
                        <input type="date" name="date" required value="{{ $year }}-01-01" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
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
                        <input type="checkbox" name="is_recurring" value="1" id="is_recurring" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="is_recurring" class="text-sm text-gray-700">Recurring annually</label>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('addHolidayModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Add Holiday</button>
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
                        <input type="text" name="name" id="edit_name" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" name="date" id="edit_date" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" id="edit_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="Public">Public Holiday</option>
                            <option value="Company">Company Holiday</option>
                            <option value="Optional">Optional Holiday</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                        <textarea name="description" id="edit_description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_recurring" value="1" id="edit_is_recurring" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="edit_is_recurring" class="text-sm text-gray-700">Recurring annually</label>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('editHolidayModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Update Holiday</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editHoliday(id, name, date, type, description, isRecurring) {
            document.getElementById('editHolidayForm').action = '/holidays/' + id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_date').value = date;
            document.getElementById('edit_type').value = type;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_is_recurring').checked = isRecurring;
            document.getElementById('editHolidayModal').classList.remove('hidden');
        }
    </script>
    @endif
</x-app-layout>
