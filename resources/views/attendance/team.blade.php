<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Team Attendance') }}</h2>
    </x-slot>

    @php
        // Solid cell colors for the matrix markers.
        $cell = [
            'emerald' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
            'blue'    => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
            'rose'    => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
            'amber'   => 'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-300',
            'indigo'  => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300',
            'purple'  => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
            'slate'   => 'bg-slate-100 text-slate-400 dark:bg-slate-800/60 dark:text-slate-500',
            'red'     => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
            'sky'     => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
            'neutral' => 'text-gray-300 dark:text-gray-600',
        ];
        $viewerId = auth()->id();
        $viewer = auth()->user();
        $daysInMonth = $month->daysInMonth;
        $prev = $month->copy()->subMonth()->format('Y-m');
        $next = $month->copy()->addMonth()->format('Y-m');
    @endphp

    <div class="py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Toolbar --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 px-4 sm:px-6 py-4 flex flex-col lg:flex-row lg:items-center gap-3 lg:justify-between">
                <div class="flex items-center gap-2">
                    <a href="{{ route('attendance.team', array_filter(['department' => $departmentId, 'month' => $prev])) }}" class="p-2 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700" title="Previous month">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <form method="GET" action="{{ route('attendance.team') }}" class="flex items-center gap-2">
                        <input type="month" name="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()" class="rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm font-semibold">
                        @if($isAdmin)
                            <select name="department" onchange="this.form.submit()" class="rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm">
                                <option value="">All departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected($departmentId == $dept->id)>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </form>
                    <a href="{{ route('attendance.team', array_filter(['department' => $departmentId, 'month' => $next])) }}" class="p-2 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700" title="Next month">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <span class="text-sm text-gray-400 ml-1">{{ $rows->count() }} {{ Str::plural('member', $rows->count()) }}@if(!$isAdmin && $viewer->department) · {{ $viewer->department->name }}@endif</span>
                </div>
                <a href="{{ route('attendance.index', ['month' => $month->format('Y-m')]) }}" class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg self-start lg:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    My Attendance
                </a>
            </div>

            {{-- Matrix --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                @if($rows->isEmpty())
                    <div class="px-6 py-12 text-center text-sm text-gray-400">No team members to show.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full table-fixed min-w-[1000px] border-collapse text-xs">
                            <thead>
                                <tr>
                                    <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-700/60 px-3 py-2 text-left font-semibold text-gray-500 uppercase tracking-wider w-[160px] border-b border-gray-100 dark:border-gray-700">Member</th>
                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                        @php $dow = $month->copy()->day($d)->dayOfWeek; $isWknd = in_array($dow, [0, 6]); @endphp
                                        <th class="px-0 py-1 text-center font-medium border-b border-gray-100 dark:border-gray-700 {{ $isWknd ? 'bg-slate-50 dark:bg-slate-800/40 text-slate-400' : 'text-gray-500' }}">
                                            <div class="text-[9px] uppercase">{{ substr(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][$dow], 0, 1) }}</div>
                                            <div class="font-semibold">{{ $d }}</div>
                                        </th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $row)
                                    @php
                                        $ownRow = $row['user']->id === $viewerId;
                                        // Drill into a member's calendar only where the personal page allows it
                                        // (self, or a supervisor viewing their own intern) — avoids a 403.
                                        $canDrill = $ownRow || ($viewer->isSupervisor() && $row['user']->supervisor_id === $viewerId && $row['user']->is_intern);
                                        $revealType = $showLeaveType || $ownRow;
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20">
                                        <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 px-3 py-1.5 border-b border-gray-50 dark:border-gray-700/60 whitespace-nowrap">
                                            @if($canDrill)
                                                <a href="{{ route('attendance.index', ['user' => $row['user']->id, 'month' => $month->format('Y-m')]) }}" class="font-medium text-gray-800 dark:text-gray-200 hover:text-indigo-600 hover:underline">{{ $row['user']->name }}</a>
                                            @else
                                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $row['user']->name }}</span>
                                            @endif
                                            @if($ownRow)<span class="ml-1 text-[10px] text-indigo-500 font-semibold">(you)</span>@endif
                                        </td>
                                        @for($d = 1; $d <= $daysInMonth; $d++)
                                            @php
                                                $key = $month->copy()->day($d)->toDateString();
                                                $day = $row['calendar']['days'][$key] ?? null;
                                                $pending = $day && ($day['pending'] ?? false);
                                                $isWknd = $day ? in_array($day['dow'], [0, 6]) : false;
                                                // Hide leave type from same-dept peers (privacy); keep it for managers/self.
                                                $isLeave = $day && $day['type'] === 'leave';
                                                $mColor = $isLeave && ! $revealType ? 'sky' : ($day['color'] ?? 'neutral');
                                                $mShort = $isLeave && ! $revealType ? 'L' : ($day['short'] ?? '');
                                                $mLabel = $isLeave && ! $revealType ? 'On leave' : ($day['label'] ?? '');
                                            @endphp
                                            <td class="p-0.5 text-center border-b border-gray-50 dark:border-gray-700/60 {{ $isWknd ? 'bg-slate-50/60 dark:bg-slate-800/20' : '' }}">
                                                @if($day && $day['type'] !== 'upcoming')
                                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded text-[10px] font-bold {{ $cell[$mColor] }} {{ $pending ? 'ring-1 ring-dashed ring-current opacity-70' : '' }}" title="{{ $key }} · {{ $mLabel }}">{{ $mShort ?: '·' }}</span>
                                                @else
                                                    <span class="inline-block w-6 h-6"></span>
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                                {{-- On-leave count per day (clash spotting) --}}
                                <tr class="bg-gray-50/70 dark:bg-gray-700/30">
                                    <td class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-700/60 px-3 py-1.5 font-semibold text-gray-500 uppercase tracking-wider text-[10px]">On leave</td>
                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                        @php $c = $leaveCounts[$d] ?? 0; @endphp
                                        <td class="text-center py-1 {{ $c > 1 ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 font-bold' : ($c === 1 ? 'text-gray-500' : 'text-gray-300 dark:text-gray-600') }}" @if($c > 1) title="{{ $c }} people on leave — possible clash" @endif>{{ $c ?: '' }}</td>
                                    @endfor
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Legend --}}
                    <div class="flex flex-wrap gap-x-4 gap-y-1.5 px-4 py-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                        @php
                            $legend = $showLeaveType
                                ? [['emerald','P','Present'],['blue','AL','Annual'],['rose','MC','Medical'],['amber','EL','Emergency'],['indigo','IL','Intern'],['purple','PH','Holiday'],['slate','·','Off'],['red','✕','Absent']]
                                : [['emerald','P','Present'],['sky','L','On leave'],['purple','PH','Holiday'],['slate','·','Off'],['red','✕','Absent']];
                        @endphp
                        @foreach($legend as [$c,$code,$l])
                            <span class="inline-flex items-center gap-1.5"><span class="inline-flex items-center justify-center w-5 h-5 rounded text-[9px] font-bold {{ $cell[$c] }}">{{ $code }}</span>{{ $l }}</span>
                        @endforeach
                        <span class="inline-flex items-center gap-1.5"><span class="w-4 h-4 rounded border border-dashed border-gray-400"></span>Pending</span>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
