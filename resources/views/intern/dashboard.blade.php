<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Intern Dashboard') }}
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

            <!-- Gradient Hero / Live Clock -->
            <div class="rounded-2xl shadow-sm overflow-hidden bg-gradient-to-r from-indigo-600 to-purple-700">
                <div class="px-6 py-8 text-center">
                    <p class="text-indigo-200 text-sm font-medium uppercase tracking-wider mb-1">Current Time</p>
                    <div id="live-clock" class="text-5xl font-mono font-bold text-white mb-2"></div>
                    <p class="text-indigo-200 text-sm">{{ now('Asia/Kuala_Lumpur')->format('l, F j, Y') }}</p>
                    <p class="text-indigo-300/70 text-xs mt-1">Malaysia Time (MYT)</p>
                </div>
            </div>

            <!-- Internship Overview -->
            <div class="rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                    <h3 class="text-base font-semibold text-gray-800">Internship Overview</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Supervisor Info -->
                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-5">
                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Supervisor</h4>
                            @if(Auth::user()->supervisor)
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                                        <span class="text-sm text-gray-600">Name:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ Auth::user()->supervisor->name }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                        <span class="text-sm text-gray-600">Email:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ Auth::user()->supervisor->email }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15"/></svg>
                                        <span class="text-sm text-gray-600">Department:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ Auth::user()->supervisor->department->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            @else
                                <p class="text-sm text-gray-400 italic">No supervisor assigned.</p>
                            @endif
                        </div>

                        <!-- Internship Period -->
                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-5">
                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Internship Period</h4>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                    <span class="text-sm text-gray-600">Start:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ Auth::user()->internship_start_date ? Auth::user()->internship_start_date->format('M d, Y') : 'N/A' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                                    <span class="text-sm text-gray-600">End:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ Auth::user()->internship_end_date ? Auth::user()->internship_end_date->format('M d, Y') : 'N/A' }}</span>
                                </div>
                                <div class="flex items-center gap-2 pt-1">
                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="text-sm text-gray-600">Status:</span>
                                    @if(Auth::user()->internship_end_date && Auth::user()->internship_end_date->isPast())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">Completed</span>
                                    @elseif(Auth::user()->internship_start_date && Auth::user()->internship_start_date->isFuture())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">Upcoming</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Active</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Statistics -->
            <div class="rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                        <h3 class="text-base font-semibold text-gray-800">Report Statistics</h3>
                    </div>
                    <a href="{{ route('reports.create') }}" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition duration-200 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Submit New Report
                    </a>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="rounded-xl bg-indigo-50 border border-indigo-100 p-4 text-center">
                            <div class="text-2xl font-bold text-indigo-600">{{ $reportStats['total'] }}</div>
                            <div class="text-xs font-medium text-indigo-500/80 mt-1">Total</div>
                        </div>
                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-4 text-center">
                            <div class="text-2xl font-bold text-gray-600">{{ $reportStats['draft'] }}</div>
                            <div class="text-xs font-medium text-gray-500/80 mt-1">Draft</div>
                        </div>
                        <div class="rounded-xl bg-amber-50 border border-amber-100 p-4 text-center">
                            <div class="text-2xl font-bold text-amber-600">{{ $reportStats['pending'] }}</div>
                            <div class="text-xs font-medium text-amber-500/80 mt-1">Pending</div>
                        </div>
                        <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-4 text-center">
                            <div class="text-2xl font-bold text-emerald-600">{{ $reportStats['signed'] }}</div>
                            <div class="text-xs font-medium text-emerald-500/80 mt-1">Signed</div>
                        </div>
                        <div class="rounded-xl bg-red-50 border border-red-100 p-4 text-center">
                            <div class="text-2xl font-bold text-red-600">{{ $reportStats['rejected'] + $reportStats['revised'] }}</div>
                            <div class="text-xs font-medium text-red-500/80 mt-1">Rejected</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Reports -->
            <div class="rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        <h3 class="text-base font-semibold text-gray-800">My Reports</h3>
                    </div>
                    <a href="{{ route('reports.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 transition">View All &rarr;</a>
                </div>
                <div class="p-6">
                    @if(session('success'))
                        <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-4 text-sm">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50/80">
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($reports as $report)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $report->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $report->created_at->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($report->status == 'signed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Signed</span>
                                            @elseif($report->status == 'pending')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">Pending</span>
                                            @elseif($report->status == 'draft')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-500/20">Draft</span>
                                            @elseif($report->status == 'revised')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20">Revised</span>
                                            @elseif($report->status == 'rejected')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">Rejected</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="{{ route('reports.show', $report->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>

                                            @if($report->status == 'draft' || $report->status == 'revised' || $report->status == 'rejected')
                                                {{-- Draft/Revised/Rejected: can edit and submit --}}
                                                <a href="{{ route('reports.edit', $report->id) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                                <form action="{{ route('reports.submit', $report->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-emerald-600 hover:text-emerald-900" onclick="return confirm('Submit this report to your supervisor?')">
                                                        Submit
                                                    </button>
                                                </form>
                                            @endif

                                            @if($report->status == 'signed' && $report->signed_path)
                                                {{-- Signed: can download signed copy --}}
                                                <a href="{{ route('reports.download-signed', $report->id) }}" class="text-emerald-600 hover:text-emerald-900 font-semibold">Download Signed</a>
                                            @endif

                                            <a href="{{ route('reports.download', $report->id) }}" class="text-gray-600 hover:text-gray-900">Download Original</a>

                                            @if($report->status == 'draft')
                                                <form action="{{ route('reports.destroy', $report->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this report?')">Delete</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>

                                    {{-- Show supervisor comments for rejected/revised reports --}}
                                    @if(($report->status == 'rejected' || $report->status == 'revised') && $report->comments)
                                        <tr class="bg-red-50/60">
                                            <td colspan="4" class="px-6 py-3 text-sm text-red-700">
                                                <div class="flex items-start gap-2">
                                                    <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 011.037-.443 48.282 48.282 0 005.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
                                                    <span><strong>Supervisor Comments:</strong> {{ $report->comments }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                            No reports yet.
                                            <a href="{{ route('reports.create') }}" class="text-indigo-600 hover:text-indigo-800 ml-1 font-medium">Submit your first report</a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Today's Attendance -->
            <div class="rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-base font-semibold text-gray-800">Today's Attendance</h3>
                </div>
                <div class="p-6">
                    @if($todayAttendance)
                        @if($todayAttendance->is_wfh)
                            <div class="mb-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                                    Working From Home
                                </span>
                            </div>
                        @endif
                        @if($todayAttendance->location_flagged)
                            <div class="mb-4 flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl text-sm">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                Location flagged: {{ $todayAttendance->location_flag_reason }}
                            </div>
                        @endif
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="rounded-xl bg-gray-50 border border-gray-100 p-4">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Clock In</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $todayAttendance->clock_in ? $todayAttendance->clock_in->format('H:i') : 'Not yet' }}</p>
                                @if($todayAttendance->clock_in_address)
                                    <p class="text-xs text-gray-400 mt-1">{{ $todayAttendance->clock_in_address }}</p>
                                @endif
                            </div>
                            <div class="rounded-xl bg-gray-50 border border-gray-100 p-4">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Clock Out</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $todayAttendance->clock_out ? $todayAttendance->clock_out->format('H:i') : 'Not yet' }}</p>
                                @if($todayAttendance->clock_out_address)
                                    <p class="text-xs text-gray-400 mt-1">{{ $todayAttendance->clock_out_address }}</p>
                                @endif
                            </div>
                            <div class="rounded-xl bg-gray-50 border border-gray-100 p-4">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Total Hours</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $todayAttendance->formatted_work_hours }}</p>
                            </div>
                        </div>
                        @if($todayAttendance->breaks->count() > 0)
                            <div class="mt-5">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Breaks</h4>
                                <div class="space-y-2">
                                    @foreach($todayAttendance->breaks as $break)
                                        <div class="flex items-center gap-3 text-sm bg-gray-50 border border-gray-100 rounded-lg px-4 py-2.5">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold">{{ $loop->iteration }}</span>
                                            <span class="text-gray-700">{{ $break->break_in->format('H:i') }} - {{ $break->break_out ? $break->break_out->format('H:i') : 'Ongoing' }}</span>
                                            <span class="text-gray-400">({{ $break->duration_minutes ? number_format($break->duration_minutes / 60, 2) . 'h' : 'N/A' }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-6">
                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-gray-400 text-sm">No attendance record for today.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                    <h3 class="text-base font-semibold text-gray-800">Quick Actions</h3>
                </div>
                <div class="p-6">
                    <!-- WFH Toggle -->
                    <div class="mb-5 flex items-center gap-3" id="wfh-toggle-container">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" id="wfh-checkbox" class="sr-only peer" onchange="toggleWfh()">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ms-3 text-sm font-medium text-gray-700">Working From Home (WFH)</span>
                        </label>
                        <span id="wfh-badge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20 hidden">WFH Mode</span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <button id="clock-in-btn" class="group flex flex-col items-center justify-center gap-2 bg-white border-2 border-emerald-200 hover:border-emerald-400 hover:bg-emerald-50 disabled:border-gray-200 disabled:bg-gray-50 disabled:cursor-not-allowed text-emerald-700 disabled:text-gray-400 font-semibold py-5 px-4 rounded-xl transition duration-200" onclick="clockIn()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                            <span class="text-sm">Clock In</span>
                        </button>
                        <button id="clock-out-btn" class="group flex flex-col items-center justify-center gap-2 bg-white border-2 border-red-200 hover:border-red-400 hover:bg-red-50 disabled:border-gray-200 disabled:bg-gray-50 disabled:cursor-not-allowed text-red-700 disabled:text-gray-400 font-semibold py-5 px-4 rounded-xl transition duration-200" onclick="clockOut()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                            <span class="text-sm">Clock Out</span>
                        </button>
                        <button id="break-in-btn" class="group flex flex-col items-center justify-center gap-2 bg-white border-2 border-amber-200 hover:border-amber-400 hover:bg-amber-50 disabled:border-gray-200 disabled:bg-gray-50 disabled:cursor-not-allowed text-amber-700 disabled:text-gray-400 font-semibold py-5 px-4 rounded-xl transition duration-200" onclick="breakIn()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5"/></svg>
                            <span class="text-sm">Start Break</span>
                        </button>
                        <button id="break-out-btn" class="group flex flex-col items-center justify-center gap-2 bg-white border-2 border-orange-200 hover:border-orange-400 hover:bg-orange-50 disabled:border-gray-200 disabled:bg-gray-50 disabled:cursor-not-allowed text-orange-700 disabled:text-gray-400 font-semibold py-5 px-4 rounded-xl transition duration-200" onclick="breakOut()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                            <span class="text-sm">End Break</span>
                        </button>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                        <span><strong>Status:</strong> <span id="action-status">Loading...</span></span>
                    </div>
                </div>
            </div>

            <!-- Recent Attendances -->
            <div class="rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                    <h3 class="text-base font-semibold text-gray-800">Recent Attendances</h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50/80">
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Clock In</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Clock Out</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hours</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($recentAttendances as $attendance)
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $attendance->date->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $attendance->formatted_work_hours }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Leave Status -->
            <div class="rounded-2xl shadow-sm border border-gray-100 bg-white overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3.75a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 5.25c0 .372-.052.732-.144 1.076a11.876 11.876 0 01-3.668 5.724M10.5 15.75H6a3.75 3.75 0 01-3.75-3.75V9.75A.75.75 0 013 9h3a.75.75 0 01.75.75v.75a.75.75 0 01-.75.75H4.5"/></svg>
                    <h3 class="text-base font-semibold text-gray-800">Leave Status</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-50">
                                <span class="text-lg font-bold text-indigo-600">{{ $pendingLeaves }}</span>
                            </div>
                            <p class="text-sm text-gray-600">pending leave {{ $pendingLeaves == 1 ? 'application' : 'applications' }}</p>
                        </div>
                        <a href="{{ route('leave.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition">
                            View Leaves
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
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
                            successCallback({ lat: null, lng: null, accuracy: null, is_mock: false });
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
            getLocation((loc) => {
                showStatus('Clocking you in...', 'info');
                fetch('{{ route("clock.clock-in") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        lat: loc.lat, lng: loc.lng,
                        accuracy: loc.accuracy, is_mock: loc.is_mock, is_wfh: isWfh
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
                .catch(() => {
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
            getLocation((loc) => {
                showStatus('Clocking you out...', 'info');
                fetch('{{ route("clock.clock-out") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        lat: loc.lat, lng: loc.lng,
                        accuracy: loc.accuracy, is_mock: loc.is_mock
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
                .catch(() => {
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
            getLocation((loc) => {
                showStatus('Starting your break...', 'info');
                fetch('{{ route("clock.break-in") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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
                .catch(() => {
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
            getLocation((loc) => {
                showStatus('Ending your break...', 'info');
                fetch('{{ route("clock.break-out") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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
                .catch(() => {
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
                    data.breaks.forEach(breakItem => {
                        if (!breakItem.break_out) {
                            currentBreakId = breakItem.id;
                            document.getElementById('break-in-btn').disabled = true;
                            statusText = 'On break - ready to end break';
                        }
                    });
                }
                document.getElementById('action-status').textContent = statusText;
            });

        // Live clock
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
        document.addEventListener('DOMContentLoaded', function() {
            updateClock();
            setInterval(updateClock, 1000);
        });
    </script>
</x-app-layout>
