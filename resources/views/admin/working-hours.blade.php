<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Working Hours Configuration') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium text-green-700">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Default Working Hours --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Default Working Hours
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">Applies to all employees unless custom hours are set</p>
                </div>
                <form method="POST" action="{{ route('admin.working-hours.update-default') }}" class="p-6">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Work Start</label>
                            <input type="time" name="work_start" value="{{ $defaultHours->work_start ?? '09:00' }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Work End</label>
                            <input type="time" name="work_end" value="{{ $defaultHours->work_end ?? '17:30' }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div class="hidden md:block"></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Break Start</label>
                            <input type="time" name="break_start" value="{{ $defaultHours->break_start ?? '13:00' }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Break End</label>
                            <input type="time" name="break_end" value="{{ $defaultHours->break_end ?? '14:00' }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        <div class="hidden md:block"></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Late Threshold (minutes)</label>
                            <input type="number" name="late_threshold_minutes" value="{{ $defaultHours->late_threshold_minutes ?? 15 }}" min="1" max="120" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                            <p class="text-xs text-gray-400 mt-1">Clock-in after this many minutes = Late</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Early Leave Threshold (minutes)</label>
                            <input type="number" name="early_leave_threshold_minutes" value="{{ $defaultHours->early_leave_threshold_minutes ?? 15 }}" min="1" max="120" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                            <p class="text-xs text-gray-400 mt-1">Clock-out before this many minutes = Early Leave</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Default Hours
                        </button>
                    </div>
                </form>
            </div>

            {{-- Custom Working Hours --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Custom Employee Working Hours
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Override default hours for specific employees</p>
                    </div>
                    <button onclick="document.getElementById('customModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Set Custom Hours
                    </button>
                </div>

                @if($customHours->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr class="bg-gray-50/80">
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Employee</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Work Hours</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Break</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thresholds</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($customHours as $wh)
                                    <tr class="hover:bg-gray-50/60">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $wh->user->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ \Carbon\Carbon::parse($wh->work_start)->format('g:i A') }} - {{ \Carbon\Carbon::parse($wh->work_end)->format('g:i A') }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ \Carbon\Carbon::parse($wh->break_start)->format('g:i A') }} - {{ \Carbon\Carbon::parse($wh->break_end)->format('g:i A') }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">Late: {{ $wh->late_threshold_minutes }}min / Early: {{ $wh->early_leave_threshold_minutes }}min</td>
                                        <td class="px-6 py-4 text-sm">
                                            <form method="POST" action="{{ route('admin.working-hours.delete-custom', $wh->id) }}" class="inline" onsubmit="return confirm('Remove custom hours for this employee?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <p class="text-sm text-gray-400">No custom working hours set. All employees use the default schedule.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Custom Hours Modal --}}
    <div id="customModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="document.getElementById('customModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-5">Set Custom Working Hours</h3>
                <form method="POST" action="{{ route('admin.working-hours.store-custom') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                            <select name="user_id" required class="w-full rounded-xl border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                <option value="">Select Employee</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->role->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Work Start</label>
                                <input type="time" name="work_start" value="09:00" class="w-full rounded-xl border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Work End</label>
                                <input type="time" name="work_end" value="17:30" class="w-full rounded-xl border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Break Start</label>
                                <input type="time" name="break_start" value="13:00" class="w-full rounded-xl border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Break End</label>
                                <input type="time" name="break_end" value="14:00" class="w-full rounded-xl border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Late Threshold (min)</label>
                                <input type="number" name="late_threshold_minutes" value="15" min="1" max="120" class="w-full rounded-xl border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Early Leave (min)</label>
                                <input type="number" name="early_leave_threshold_minutes" value="15" min="1" max="120" class="w-full rounded-xl border-gray-300" required>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('customModal').classList.add('hidden')" class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-xl">Save Custom Hours</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
