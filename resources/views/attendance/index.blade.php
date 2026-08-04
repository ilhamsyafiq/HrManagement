<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            @if(request()->has('user') && request('user') != auth()->id())
                {{ $targetUser->name }} — {{ __('Attendance') }}
            @else
                {{ __('My Attendance') }}
            @endif
        </h2>
    </x-slot>

    @php
        // color key -> tile classes (light + dark)
        $palette = [
            'emerald' => 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-900/20 dark:border-emerald-800/60 dark:text-emerald-300',
            'blue'    => 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-900/20 dark:border-blue-800/60 dark:text-blue-300',
            'rose'    => 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-900/20 dark:border-rose-800/60 dark:text-rose-300',
            'amber'   => 'bg-amber-50 border-amber-200 text-amber-900 dark:bg-amber-900/20 dark:border-amber-800/60 dark:text-amber-300',
            'indigo'  => 'bg-indigo-50 border-indigo-200 text-indigo-800 dark:bg-indigo-900/20 dark:border-indigo-800/60 dark:text-indigo-300',
            'purple'  => 'bg-purple-50 border-purple-200 text-purple-800 dark:bg-purple-900/20 dark:border-purple-800/60 dark:text-purple-300',
            'slate'   => 'bg-slate-100 border-slate-200 text-slate-500 dark:bg-slate-800/40 dark:border-slate-700 dark:text-slate-400',
            'red'     => 'bg-red-50 border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-800/60 dark:text-red-300',
            'neutral' => 'bg-white border-gray-200 text-gray-400 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-500',
        ];
        $prev = $month->copy()->subMonth()->format('Y-m');
        $next = $month->copy()->addMonth()->format('Y-m');
        $userParam = (request()->has('user') && request('user') != auth()->id()) ? ['user' => request('user')] : [];
        $s = $view === 'calendar' ? $calendar['summary'] : null;
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Toolbar --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-between">
                <div class="flex items-center gap-2">
                    <a href="{{ route('attendance.index', array_merge($userParam, ['view' => $view, 'month' => $prev])) }}"
                       class="p-2 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700" title="Previous month">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <form method="GET" action="{{ route('attendance.index') }}" class="contents">
                        @foreach($userParam as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                        <input type="hidden" name="view" value="{{ $view }}">
                        <input type="month" name="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()"
                               class="rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm font-semibold">
                    </form>
                    <a href="{{ route('attendance.index', array_merge($userParam, ['view' => $view, 'month' => $next])) }}"
                       class="p-2 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700" title="Next month">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Calendar / List toggle --}}
                    <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden text-sm">
                        <a href="{{ route('attendance.index', array_merge($userParam, ['view' => 'calendar', 'month' => $month->format('Y-m')])) }}"
                           class="px-3 py-1.5 font-medium {{ $view === 'calendar' ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">Calendar</a>
                        <a href="{{ route('attendance.index', array_merge($userParam, ['view' => 'list', 'month' => $month->format('Y-m')])) }}"
                           class="px-3 py-1.5 font-medium {{ $view === 'list' ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">List</a>
                    </div>
                    @if($canViewTeam)
                        <a href="{{ route('attendance.team', ['month' => $month->format('Y-m')]) }}"
                           class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z"/></svg>
                            Team
                        </a>
                    @endif
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Dashboard
                    </a>
                </div>
            </div>

            @if($view === 'calendar')
                {{-- Summary strip --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                    @php
                        $cards = [
                            ['Worked', $s['worked'], 'text-emerald-600'],
                            ['Annual (AL)', $s['al'], 'text-blue-600'],
                            ['Medical (MC)', $s['mc'], 'text-rose-600'],
                            ['Emergency', $s['emergency'], 'text-amber-600'],
                            ['Absent', $s['absent'], 'text-red-600'],
                            ['Late', $s['late'], 'text-orange-600'],
                            ['OT (hrs)', round($s['ot_hours'], 1), 'text-purple-600'],
                        ];
                    @endphp
                    @foreach($cards as [$label, $val, $tone])
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 px-4 py-3">
                            <div class="text-2xl font-bold {{ $tone }}">{{ $val }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $label }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Calendar grid --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-3 sm:p-5">
                    <div class="grid grid-cols-7 gap-1 sm:gap-2 mb-2">
                        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow)
                            <div class="text-center text-xs font-semibold text-gray-400 uppercase tracking-wider py-1">{{ $dow }}</div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-7 gap-1 sm:gap-2">
                        @for($i = 0; $i < $calendar['leading']; $i++)
                            <div class="min-h-[70px] sm:min-h-[92px] rounded-lg border border-transparent"></div>
                        @endfor
                        @foreach($calendar['days'] as $day)
                            <div class="min-h-[70px] sm:min-h-[92px] rounded-lg border p-1.5 sm:p-2 flex flex-col gap-1 overflow-hidden {{ $palette[$day['color']] }} {{ ($day['type'] === 'leave' && ($day['pending'] ?? false)) ? 'border-dashed' : '' }} {{ $day['is_today'] ? 'ring-2 ring-indigo-500 ring-offset-1 dark:ring-offset-gray-800' : '' }}">
                                <div class="flex items-start justify-between gap-1">
                                    <span class="text-xs font-bold leading-none">{{ $day['day'] }}</span>
                                    @if($day['type'] === 'leave' && ($day['pending'] ?? false))
                                        <span class="shrink-0 text-[9px] leading-none font-bold uppercase px-1 py-0.5 rounded bg-white/70 dark:bg-black/30">Pending</span>
                                    @endif
                                </div>
                                <div class="mt-auto min-w-0 text-[11px] sm:text-xs font-semibold leading-tight truncate" title="{{ $day['label'] }}">
                                    @switch($day['type'])
                                        @case('worked') {{ $day['meta']['hours'] }} @break
                                        @case('leave') {{ $day['short'] }} @break
                                        @case('holiday') {{ $day['meta']['name'] }} @break
                                        @case('off') <span class="opacity-70">Off</span> @break
                                        @case('absent') Absent @break
                                        @default <span class="opacity-40">·</span>
                                    @endswitch
                                </div>
                                @if($day['type'] === 'worked' && !empty($day['meta']['badges']))
                                    <div class="hidden sm:block text-[10px] opacity-80 truncate leading-none">{{ implode(' · ', $day['meta']['badges']) }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Legend --}}
                    <div class="flex flex-wrap gap-x-4 gap-y-1.5 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                        @foreach([['emerald','Worked'],['blue','Annual (AL)'],['rose','Medical (MC)'],['amber','Emergency'],['purple','Public Holiday'],['slate','Off / Weekend'],['red','Absent']] as [$c,$l])
                            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded {{ $palette[$c] }} border"></span>{{ $l }}</span>
                        @endforeach
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded border border-dashed border-gray-400"></span>Pending leave</span>
                    </div>
                </div>
            @else
                {{-- Legacy flat list --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                            <thead>
                                <tr class="bg-gray-50/80 dark:bg-gray-700/40">
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Clock In</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Clock Out</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Hours</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-700/60">
                                @forelse($attendances as $attendance)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 {{ ($attendance->is_late || $attendance->is_early_leave) ? 'bg-red-50/40 dark:bg-red-900/10' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                            {{ $attendance->date->format('M d, Y') }}
                                            @if($attendance->breaks->count() > 0)
                                                <span class="ml-1 text-xs text-gray-400">&middot; {{ $attendance->breaks->count() }} {{ Str::plural('break', $attendance->breaks->count()) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $attendance->is_late ? 'text-red-600 font-semibold' : 'text-gray-900 dark:text-gray-200' }}">{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $attendance->is_early_leave ? 'text-red-600 font-semibold' : 'text-gray-900 dark:text-gray-200' }}">{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">{{ $attendance->formatted_work_hours }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-wrap gap-1">
                                                @if($attendance->is_wfh)<span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20">WFH</span>@endif
                                                @if($attendance->is_late)<span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">Late</span>@endif
                                                @if($attendance->is_early_leave)<span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-600/20">Early Leave</span>@endif
                                                @if($attendance->overtime_hours > 0)<span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-600/20">OT {{ $attendance->formatted_overtime }}</span>@endif
                                                @if($attendance->is_edited)<span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">Edited</span>@endif
                                                @if(!$attendance->is_late && !$attendance->is_early_leave && !$attendance->is_wfh && !$attendance->is_edited && !($attendance->overtime_hours > 0))<span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">On Time</span>@endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">No attendance records.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">{{ $attendances->links() }}</div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
