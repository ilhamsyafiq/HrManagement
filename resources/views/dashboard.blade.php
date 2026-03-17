<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    {{-- Announcement Popup Modal --}}
    @if(isset($popupAnnouncements) && $popupAnnouncements->count() > 0)
    <div id="announcementPopup" class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: true }" x-show="show" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="show = false; document.getElementById('announcementPopup').remove()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden z-10">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-600 to-purple-600">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <h3 class="text-lg font-semibold text-white">Important Announcements</h3>
                        </div>
                        <button @click="show = false; document.getElementById('announcementPopup').remove()" class="text-white/70 hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <div class="max-h-[60vh] overflow-y-auto divide-y divide-gray-100">
                    @foreach($popupAnnouncements as $ann)
                        <div class="px-6 py-4">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
                                    @if($ann->priority === 'Urgent') bg-red-100 text-red-700
                                    @elseif($ann->priority === 'High') bg-orange-100 text-orange-700
                                    @else bg-blue-100 text-blue-700 @endif
                                ">{{ $ann->priority }}</span>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-gray-900">{{ $ann->title }}</h4>
                                    <p class="text-sm text-gray-600 mt-1 line-clamp-3">{!! nl2br(e(Str::limit($ann->content, 200))) !!}</p>
                                    <p class="text-xs text-gray-400 mt-2">{{ \Carbon\Carbon::parse($ann->publish_date)->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <a href="{{ route('announcements.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 transition">View All Announcements</a>
                    <button @click="show = false; document.getElementById('announcementPopup').remove()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Dismiss</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Gradient Hero Card with Live Clock -->
            <div class="rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-700 shadow-lg overflow-hidden">
                <div class="p-8 text-center">
                    <div class="flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-white/80 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-white/90">Current Time</h3>
                    </div>
                    <div id="live-clock" class="text-5xl font-mono font-bold text-white mb-3 tracking-wide"></div>
                    <div class="text-sm text-white/70">{{ now('Asia/Kuala_Lumpur')->format('l, F j, Y') }}</div>
                    <div class="text-xs text-white/50 mt-1">Malaysia Time (MYT)</div>

                    @if($todayAttendance)
                        <div class="mt-4 inline-flex items-center px-4 py-1.5 rounded-full text-sm font-medium
                            @if($todayAttendance->clock_out) bg-white/20 text-white
                            @elseif($todayAttendance->clock_in) bg-green-400/20 text-green-100
                            @else bg-white/20 text-white @endif">
                            <span class="w-2 h-2 rounded-full mr-2
                                @if($todayAttendance->clock_out) bg-gray-300
                                @elseif($todayAttendance->clock_in) bg-green-400 animate-pulse
                                @else bg-white @endif"></span>
                            @if($todayAttendance->clock_out) Day Completed
                            @elseif($todayAttendance->clock_in) Currently Working
                            @else Ready @endif
                        </div>
                    @else
                        <div class="mt-4 inline-flex items-center px-4 py-1.5 rounded-full text-sm font-medium bg-white/20 text-white">
                            <span class="w-2 h-2 rounded-full mr-2 bg-white"></span>
                            Not Clocked In
                        </div>
                    @endif
                </div>
            </div>

            <!-- Today's Attendance -->
            <div class="rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center">
                    <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-800">Today's Attendance</h3>
                </div>
                <div class="p-6">
                    @if($todayAttendance)
                        @if($todayAttendance->is_wfh)
                            <div class="mb-4">
                                <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/>
                                    </svg>
                                    Working From Home
                                </span>
                            </div>
                        @endif
                        @if($todayAttendance->location_flagged)
                            <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl text-sm flex items-start">
                                <svg class="w-5 h-5 mr-2 mt-0.5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                <span>Location flagged: {{ $todayAttendance->location_flag_reason }}</span>
                            </div>
                        @endif
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Clock In</div>
                                <div class="text-lg font-semibold text-gray-900">{{ $todayAttendance->clock_in ? $todayAttendance->clock_in->format('H:i') : 'Not yet' }}</div>
                                @if($todayAttendance->clock_in_address)
                                    <div class="text-xs text-gray-500 mt-1">{{ $todayAttendance->clock_in_address }}</div>
                                @endif
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Clock Out</div>
                                <div class="text-lg font-semibold text-gray-900">{{ $todayAttendance->clock_out ? $todayAttendance->clock_out->format('H:i') : 'Not yet' }}</div>
                                @if($todayAttendance->clock_out_address)
                                    <div class="text-xs text-gray-500 mt-1">{{ $todayAttendance->clock_out_address }}</div>
                                @endif
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Hours</div>
                                <div class="text-lg font-semibold text-gray-900">{{ $todayAttendance->formatted_work_hours }}</div>
                            </div>
                        </div>
                        @if($todayAttendance->breaks->count() > 0)
                            <div class="mt-5">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Breaks</h4>
                                <div class="space-y-2">
                                    @foreach($todayAttendance->breaks as $break)
                                        <div class="flex items-center text-sm bg-gray-50 rounded-lg px-4 py-2">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-semibold mr-3">{{ $loop->iteration }}</span>
                                            <span class="text-gray-700">{{ $break->break_in->format('H:i') }} - {{ $break->break_out ? $break->break_out->format('H:i') : 'Ongoing' }}</span>
                                            <span class="ml-auto text-gray-500">{{ $break->duration_minutes ? number_format($break->duration_minutes / 60, 2) . 'h' : 'N/A' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-6 text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm">No attendance record for today.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center">
                    <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-800">Quick Actions</h3>
                </div>
                <div class="p-6">
                    <!-- WFH Toggle -->
                    <div class="mb-5 flex items-center" id="wfh-toggle-container">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" id="wfh-checkbox" class="sr-only peer" onchange="toggleWfh()">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-700">Working From Home (WFH)</span>
                        </label>
                        <span id="wfh-badge" class="ml-3 inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 hidden">WFH Mode</span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <button id="clock-in-btn" class="group flex flex-col items-center justify-center bg-white border border-gray-200 hover:border-green-300 hover:bg-green-50 disabled:bg-gray-50 disabled:border-gray-100 disabled:cursor-not-allowed rounded-xl py-4 px-4 transition duration-200 shadow-sm" onclick="clockIn()">
                            <div class="w-10 h-10 rounded-full bg-green-100 group-hover:bg-green-200 group-disabled:bg-gray-100 flex items-center justify-center mb-2 transition-colors">
                                <svg class="w-5 h-5 text-green-600 group-disabled:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-700 group-disabled:text-gray-400">Clock In</span>
                        </button>
                        <button id="clock-out-btn" class="group flex flex-col items-center justify-center bg-white border border-gray-200 hover:border-red-300 hover:bg-red-50 disabled:bg-gray-50 disabled:border-gray-100 disabled:cursor-not-allowed rounded-xl py-4 px-4 transition duration-200 shadow-sm" onclick="clockOut()">
                            <div class="w-10 h-10 rounded-full bg-red-100 group-hover:bg-red-200 group-disabled:bg-gray-100 flex items-center justify-center mb-2 transition-colors">
                                <svg class="w-5 h-5 text-red-600 group-disabled:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-700 group-disabled:text-gray-400">Clock Out</span>
                        </button>
                        <button id="break-in-btn" class="group flex flex-col items-center justify-center bg-white border border-gray-200 hover:border-amber-300 hover:bg-amber-50 disabled:bg-gray-50 disabled:border-gray-100 disabled:cursor-not-allowed rounded-xl py-4 px-4 transition duration-200 shadow-sm" onclick="breakIn()">
                            <div class="w-10 h-10 rounded-full bg-amber-100 group-hover:bg-amber-200 group-disabled:bg-gray-100 flex items-center justify-center mb-2 transition-colors">
                                <svg class="w-5 h-5 text-amber-600 group-disabled:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-700 group-disabled:text-gray-400">Start Break</span>
                        </button>
                        <button id="break-out-btn" class="group flex flex-col items-center justify-center bg-white border border-gray-200 hover:border-orange-300 hover:bg-orange-50 disabled:bg-gray-50 disabled:border-gray-100 disabled:cursor-not-allowed rounded-xl py-4 px-4 transition duration-200 shadow-sm" onclick="breakOut()">
                            <div class="w-10 h-10 rounded-full bg-orange-100 group-hover:bg-orange-200 group-disabled:bg-gray-100 flex items-center justify-center mb-2 transition-colors">
                                <svg class="w-5 h-5 text-orange-600 group-disabled:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-700 group-disabled:text-gray-400">End Break</span>
                        </button>
                    </div>
                    <div class="mt-4 text-sm text-gray-600 bg-gray-50 rounded-lg px-4 py-2.5">
                        <span class="font-medium text-gray-500">Status:</span> <span id="action-status">Loading...</span>
                    </div>
                </div>
            </div>

            <!-- Recent Attendances -->
            <div class="rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center">
                    <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-800">Recent Attendances</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Clock In</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Clock Out</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hours</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($recentAttendances as $attendance)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-3.5 whitespace-nowrap text-sm font-medium text-gray-900">{{ $attendance->date->format('M d, Y') }}</td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-sm text-gray-600">{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}</td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-sm text-gray-600">{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}</td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-sm text-gray-600">{{ $attendance->formatted_work_hours }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Leave Status -->
            <div class="rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center">
                    <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-800">Leave Status</h3>
                </div>
                <div class="p-6 flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="text-sm text-gray-600">You have</span>
                        <span class="inline-flex items-center justify-center mx-2 px-2.5 py-0.5 rounded-full text-sm font-semibold {{ $pendingLeaves > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">{{ $pendingLeaves }}</span>
                        <span class="text-sm text-gray-600">pending leave application{{ $pendingLeaves !== 1 ? 's' : '' }}.</span>
                    </div>
                    <a href="{{ route('leave.index') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                        View Leaves
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            @if(Auth::user()->isIntern())
            <!-- Supervisor Information -->
            <div class="rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center">
                    <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-800">Supervisor Information</h3>
                </div>
                <div class="p-6">
                    @if(Auth::user()->supervisor)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Name</div>
                                <div class="text-sm font-semibold text-gray-900">{{ Auth::user()->supervisor->name }}</div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Email</div>
                                <div class="text-sm font-semibold text-gray-900">{{ Auth::user()->supervisor->email }}</div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Department</div>
                                <div class="text-sm font-semibold text-gray-900">{{ Auth::user()->supervisor->department->name ?? 'N/A' }}</div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4 flex items-center">
                                <a href="{{ route('supervisor.show') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                                    View Supervisor Details
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <p class="text-sm">No supervisor assigned.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Internship Details -->
            <div class="rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center">
                    <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-800">Internship Details</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Start Date</div>
                            <div class="text-sm font-semibold text-gray-900">{{ Auth::user()->internship_start_date ? Auth::user()->internship_start_date->format('M d, Y') : 'N/A' }}</div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">End Date</div>
                            <div class="text-sm font-semibold text-gray-900">{{ Auth::user()->internship_end_date ? Auth::user()->internship_end_date->format('M d, Y') : 'N/A' }}</div>
                        </div>
                        <div class="md:col-span-2 bg-gray-50 rounded-xl p-4">
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Status</div>
                            @if(Auth::user()->internship_end_date && Auth::user()->internship_end_date->isPast())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">Completed</span>
                            @elseif(Auth::user()->internship_start_date && Auth::user()->internship_start_date->isFuture())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Upcoming</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">Active</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        let currentBreakId = null;
        let isProcessing = false;
        let isWfh = false;

        function showStatus(message, type = 'info') {
            const statusDiv = document.getElementById('action-status');
            const colors = {
                success: 'text-green-600',
                error: 'text-red-600',
                info: 'text-blue-600',
                warning: 'text-yellow-600'
            };
            statusDiv.innerHTML = `<span class="${colors[type]} font-medium">${message}</span>`;
        }

        function hideStatus() {
            setTimeout(() => {
                document.getElementById('action-status').innerHTML = 'Ready';
            }, 3000);
        }

        function setButtonLoading(buttonId, loading = true) {
            const button = document.getElementById(buttonId);
            if (loading) {
                button.disabled = true;
                button.innerHTML = '<span class="animate-spin">&#8987;</span> Processing...';
                button.classList.add('opacity-75', 'cursor-not-allowed');
            } else {
                button.disabled = false;
                const action = buttonId.includes('break-in') ? 'Start Break' :
                             buttonId.includes('break-out') ? 'End Break' :
                             buttonId.includes('clock-in') ? 'Clock In' : 'Clock Out';
                button.innerHTML = action;
                button.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        }

        function toggleWfh() {
            isWfh = document.getElementById('wfh-checkbox').checked;
            const badge = document.getElementById('wfh-badge');
            if (isWfh) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        function getLocation(successCallback, errorCallback = null) {
            if (navigator.geolocation) {
                showStatus('Getting your location...', 'info');
                navigator.geolocation.getCurrentPosition(
                    position => {
                        hideStatus();
                        successCallback({
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                            accuracy: position.coords.accuracy,
                            is_mock: false
                        });
                    },
                    error => {
                        hideStatus();
                        if (isWfh) {
                            showStatus('Location unavailable. Proceeding with WFH mode.', 'info');
                            successCallback({ lat: null, lng: null, accuracy: null, is_mock: false });
                        } else if (errorCallback) {
                            errorCallback(error);
                        } else {
                            showStatus('Location required. Please enable GPS or toggle WFH mode.', 'error');
                            errorCallback ? errorCallback(error) : successCallback({ lat: null, lng: null, accuracy: null, is_mock: false });
                        }
                    },
                    { timeout: 10000, enableHighAccuracy: true }
                );
            } else {
                if (isWfh) {
                    successCallback({ lat: null, lng: null, accuracy: null, is_mock: false });
                } else if (errorCallback) {
                    errorCallback(new Error('Geolocation is not supported by this browser'));
                } else {
                    showStatus('Geolocation not supported. Please toggle WFH mode if working remotely.', 'error');
                    successCallback({ lat: null, lng: null, accuracy: null, is_mock: false });
                }
            }
        }

        function clockIn() {
            if (isProcessing) return;
            isProcessing = true;
            setButtonLoading('clock-in-btn', true);
            showStatus('Getting your location...', 'info');

            getLocation((loc) => {
                showStatus('Clocking you in...', 'info');
                fetch('{{ route("clock.clock-in") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        lat: loc.lat,
                        lng: loc.lng,
                        accuracy: loc.accuracy,
                        is_mock: loc.is_mock,
                        is_wfh: isWfh
                    })
                })
                .then(response => response.json())
                .then(data => {
                    setButtonLoading('clock-in-btn', false);
                    isProcessing = false;
                    if (data.success) {
                        showStatus('Successfully clocked in!', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showStatus('Error: ' + data.message, 'error');
                        hideStatus();
                    }
                })
                .catch(error => {
                    setButtonLoading('clock-in-btn', false);
                    isProcessing = false;
                    showStatus('Network error. Please try again.', 'error');
                    hideStatus();
                });
            }, (error) => {
                setButtonLoading('clock-in-btn', false);
                isProcessing = false;
                showStatus('Location required for clock in. Enable GPS or use WFH mode.', 'error');
                hideStatus();
            });
        }

        function clockOut() {
            if (isProcessing) return;
            isProcessing = true;
            setButtonLoading('clock-out-btn', true);
            showStatus('Getting your location...', 'info');

            getLocation((loc) => {
                showStatus('Clocking you out...', 'info');
                fetch('{{ route("clock.clock-out") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        lat: loc.lat,
                        lng: loc.lng,
                        accuracy: loc.accuracy,
                        is_mock: loc.is_mock
                    })
                })
                .then(response => response.json())
                .then(data => {
                    setButtonLoading('clock-out-btn', false);
                    isProcessing = false;
                    if (data.success) {
                        showStatus('Successfully clocked out!', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showStatus('Error: ' + data.message, 'error');
                        hideStatus();
                    }
                })
                .catch(error => {
                    setButtonLoading('clock-out-btn', false);
                    isProcessing = false;
                    showStatus('Network error. Please try again.', 'error');
                    hideStatus();
                });
            }, (error) => {
                setButtonLoading('clock-out-btn', false);
                isProcessing = false;
                showStatus('Location required for clock out. Enable GPS.', 'error');
                hideStatus();
            });
        }

        function breakIn() {
            if (isProcessing) return;
            isProcessing = true;
            setButtonLoading('break-in-btn', true);
            showStatus('Getting your location...', 'info');

            getLocation((loc) => {
                showStatus('Starting your break...', 'info');
                fetch('{{ route("clock.break-in") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ lat: loc.lat, lng: loc.lng })
                })
                .then(response => response.json())
                .then(data => {
                    setButtonLoading('break-in-btn', false);
                    isProcessing = false;
                    if (data.success) {
                        currentBreakId = data.break.id;
                        showStatus('Break started successfully!', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showStatus('Error: ' + data.message, 'error');
                        hideStatus();
                    }
                })
                .catch(error => {
                    setButtonLoading('break-in-btn', false);
                    isProcessing = false;
                    showStatus('Network error. Please try again.', 'error');
                    hideStatus();
                });
            });
        }

        function breakOut() {
            if (!currentBreakId) {
                showStatus('No active break found.', 'error');
                hideStatus();
                return;
            }
            if (isProcessing) return;
            isProcessing = true;
            setButtonLoading('break-out-btn', true);
            showStatus('Getting your location...', 'info');

            getLocation((loc) => {
                showStatus('Ending your break...', 'info');
                fetch('{{ route("clock.break-out") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ lat: loc.lat, lng: loc.lng, break_id: currentBreakId })
                })
                .then(response => response.json())
                .then(data => {
                    setButtonLoading('break-out-btn', false);
                    isProcessing = false;
                    if (data.success) {
                        showStatus('Break ended successfully!', 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showStatus('Error: ' + data.message, 'error');
                        hideStatus();
                    }
                })
                .catch(error => {
                    setButtonLoading('break-out-btn', false);
                    isProcessing = false;
                    showStatus('Network error. Please try again.', 'error');
                    hideStatus();
                });
            });
        }

        // Load today's attendance on page load
        fetch('{{ route("attendance.today") }}')
            .then(response => response.json())
            .then(data => {
                let statusText = 'Ready to clock in';
                let activeBreakId = null;

                if (data.attendance) {
                    if (data.attendance.clock_in && !data.attendance.clock_out) {
                        document.getElementById('clock-in-btn').disabled = true;
                        document.getElementById('wfh-toggle-container').style.display = 'none';
                        statusText = 'Clocked in - ready to clock out';
                    }
                    if (data.attendance.clock_out) {
                        document.getElementById('clock-out-btn').disabled = true;
                        document.getElementById('break-in-btn').disabled = true;
                        document.getElementById('break-out-btn').disabled = true;
                        document.getElementById('wfh-toggle-container').style.display = 'none';
                        statusText = 'Day completed - all actions disabled';
                    }
                    // Find active break
                    data.breaks.forEach(breakItem => {
                        if (!breakItem.break_out) {
                            activeBreakId = breakItem.id;
                            currentBreakId = breakItem.id;
                            document.getElementById('break-in-btn').disabled = true;
                            statusText = 'On break - ready to end break';
                        }
                    });
                }

                document.getElementById('action-status').textContent = statusText;
            });

        // Live clock functionality
        function updateClock() {
            const now = new Date();
            const options = {
                timeZone: 'Asia/Kuala_Lumpur',
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.getElementById('live-clock').textContent = now.toLocaleTimeString('en-US', options);
        }

        // Initialize clock and update every second
        document.addEventListener('DOMContentLoaded', function() {
            updateClock();
            setInterval(updateClock, 1000);
        });
    </script>
</x-app-layout>
