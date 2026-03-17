<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
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

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Total Users --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-500">Total Users</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $totalUsers }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="h-1 bg-blue-500"></div>
                </div>

                {{-- Total Attendances --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-500">Total Attendances</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $totalAttendances }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="h-1 bg-green-500"></div>
                </div>

                {{-- Pending Leaves --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10a2 2 0 002 2h4a2 2 0 002-2V11M9 11h6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-gray-500">Pending Leaves</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $pendingLeaves }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="h-1 bg-amber-500"></div>
                </div>

                {{-- Recent Audits --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center">
                                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <p class="text-sm font-medium text-gray-500">Recent Audits</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $recentAudits->count() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="h-1 bg-purple-500"></div>
                    </div>
                @endif
            </div>

            {{-- Analytics Charts --}}
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Attendance Trend --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Monthly Attendance Trend</h3>
                    <canvas id="attendanceChart" height="200"></canvas>
                </div>

                {{-- Department Distribution --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Employees by Department</h3>
                    <canvas id="departmentChart" height="200"></canvas>
                </div>

                {{-- Leave Status --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Leave Status Breakdown</h3>
                    <canvas id="leaveChart" height="200"></canvas>
                </div>

                {{-- Today's Attendance --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Today's Attendance</h3>
                    <canvas id="todayChart" height="200"></canvas>
                </div>
            </div>
            @endif

            {{-- Quick Actions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('admin.users') }}" class="group flex items-center gap-3 bg-white border border-gray-200 rounded-xl p-4 hover:border-blue-300 hover:shadow-md transition-all duration-200">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Manage Users</p>
                                <p class="text-xs text-gray-500">View and manage user accounts</p>
                            </div>
                        </a>
                        <a href="{{ route('admin.attendances') }}" class="group flex items-center gap-3 bg-white border border-gray-200 rounded-xl p-4 hover:border-green-300 hover:shadow-md transition-all duration-200">
                            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition-colors">
                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">View Attendances</p>
                                <p class="text-xs text-gray-500">Track employee attendance</p>
                            </div>
                        </a>
                        <a href="{{ route('admin.leaves') }}" class="group flex items-center gap-3 bg-white border border-gray-200 rounded-xl p-4 hover:border-amber-300 hover:shadow-md transition-all duration-200">
                            <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                                <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10a2 2 0 002 2h4a2 2 0 002-2V11M9 11h6"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Manage Leaves</p>
                                <p class="text-xs text-gray-500">Handle leave requests</p>
                            </div>
                        </a>
                        <a href="{{ route('leave.approvals') }}" class="group flex items-center gap-3 bg-white border border-gray-200 rounded-xl p-4 hover:border-purple-300 hover:shadow-md transition-all duration-200">
                            <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center group-hover:bg-purple-100 transition-colors">
                                <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Approve Leaves</p>
                                <p class="text-xs text-gray-500">Review pending approvals</p>
                            </div>
                        </a>
                        <a href="{{ route('admin.audit-logs') }}" class="group flex items-center gap-3 bg-white border border-gray-200 rounded-xl p-4 hover:border-red-300 hover:shadow-md transition-all duration-200">
                            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center group-hover:bg-red-100 transition-colors">
                                <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Audit Logs</p>
                                <p class="text-xs text-gray-500">View system activity logs</p>
                            </div>
                        </a>
                        <a href="{{ route('admin.reports') }}" class="group flex items-center gap-3 bg-white border border-gray-200 rounded-xl p-4 hover:border-indigo-300 hover:shadow-md transition-all duration-200">
                            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                                <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Reports</p>
                                <p class="text-xs text-gray-500">Generate and view reports</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Recent Audit Logs Table --}}
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900">Recent Audit Logs</h3>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gray-50/80">
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Model</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($recentAudits as $audit)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $audit->user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $audit->action }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $audit->model }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $audit->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        // Attendance Trend
        new Chart(document.getElementById('attendanceChart'), {
            type: 'line',
            data: {
                labels: @json($attendanceMonths),
                datasets: [{
                    label: 'Attendance Records',
                    data: @json($attendanceCounts),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4f46e5',
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // Department Distribution
        new Chart(document.getElementById('departmentChart'), {
            type: 'doughnut',
            data: {
                labels: @json($departmentLabels),
                datasets: [{
                    data: @json($departmentData),
                    backgroundColor: ['#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#6366f1'],
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Leave Status
        new Chart(document.getElementById('leaveChart'), {
            type: 'bar',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    label: 'Leave Requests',
                    data: [{{ $leaveStats['Approved'] }}, {{ $leaveStats['Pending'] }}, {{ $leaveStats['Rejected'] }}],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderRadius: 8,
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // Today's Attendance
        new Chart(document.getElementById('todayChart'), {
            type: 'pie',
            data: {
                labels: ['Present', 'Absent/Not Clocked In'],
                datasets: [{
                    data: [{{ $presentToday }}, {{ $absentToday }}],
                    backgroundColor: ['#10b981', '#e5e7eb'],
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    </script>
    @endif
</x-app-layout>