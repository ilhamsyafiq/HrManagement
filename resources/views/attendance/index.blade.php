<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if(request()->has('user'))
                {{ __('Intern Attendance Records') }}
            @else
                {{ __('Attendance Records') }}
            @endif
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h3 class="text-base font-semibold text-gray-900">
                            @if(request()->has('user') && request('user') != auth()->id())
                                Intern Attendance History
                            @else
                                Your Attendance History
                            @endif
                        </h3>
                    </div>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back to Dashboard
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Clock In</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Clock Out</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Hours</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($attendances as $attendance)
                                <tr class="hover:bg-gray-50/50 transition duration-150 {{ ($attendance->is_late || $attendance->is_early_leave) ? 'bg-red-50/40' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $attendance->date->format('M d, Y') }}
                                        @if($attendance->breaks->count() > 0)
                                            <span class="ml-1 text-xs text-gray-400">&middot; {{ $attendance->breaks->count() }} {{ Str::plural('break', $attendance->breaks->count()) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $attendance->is_late ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                        {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $attendance->is_early_leave ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                        {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $attendance->formatted_work_hours }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap gap-1">
                                            @if($attendance->is_wfh)
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20">WFH</span>
                                            @endif
                                            @if($attendance->is_late)
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">Late</span>
                                            @endif
                                            @if($attendance->is_early_leave)
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-600/20">Early Leave</span>
                                            @endif
                                            @if($attendance->overtime_hours > 0)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-600/20">
                                                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                                    OT {{ $attendance->formatted_overtime }}
                                                </span>
                                            @endif
                                            @if($attendance->is_edited)
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">Edited</span>
                                            @endif
                                            @if(!$attendance->is_late && !$attendance->is_early_leave && !$attendance->is_wfh && !$attendance->is_edited && !($attendance->overtime_hours > 0))
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">On Time</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">
                                        No attendance records.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $attendances->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>