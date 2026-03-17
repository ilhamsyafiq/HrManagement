<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('All Attendances') }}
        </h2>
    </x-slot>

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
            @if(session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Attendance Records --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900">Attendance Records</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.attendances') }}" class="px-3 py-1.5 text-xs font-medium rounded-lg {{ !isset($filter) || !$filter ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition-colors">All</a>
                            <a href="{{ route('admin.attendances', ['filter' => 'flagged']) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg {{ ($filter ?? '') === 'flagged' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-600 hover:bg-red-100' }} transition-colors">Flagged (Late/Early)</a>
                            <a href="{{ route('admin.attendances', ['filter' => 'wfh']) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg {{ ($filter ?? '') === 'wfh' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-600 hover:bg-blue-100' }} transition-colors">WFH</a>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Clock In</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Clock Out</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hours</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Edited By</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($attendances as $attendance)
                                <tr class="hover:bg-gray-50/50 transition-colors {{ ($attendance->is_late || $attendance->is_early_leave) ? 'bg-red-50/40' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $attendance->user->name }}
                                        @if($attendance->is_wfh)
                                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">WFH</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $attendance->date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $attendance->is_late ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                        {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}
                                        @if($attendance->is_late)
                                            <span class="inline-flex items-center ml-1 px-1.5 py-0.5 text-xs font-bold rounded bg-red-100 text-red-700">LATE ({{ $attendance->late_minutes }}min)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $attendance->is_early_leave ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                                        {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}
                                        @if($attendance->is_early_leave)
                                            <span class="inline-flex items-center ml-1 px-1.5 py-0.5 text-xs font-bold rounded bg-red-100 text-red-700">EARLY ({{ $attendance->early_leave_minutes }}min)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $attendance->formatted_work_hours }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap gap-1">
                                            @if($attendance->is_late)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Late</span>
                                            @endif
                                            @if($attendance->is_early_leave)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">Early Leave</span>
                                            @endif
                                            @if($attendance->is_edited)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Edited</span>
                                            @endif
                                            @if(!$attendance->is_late && !$attendance->is_early_leave && !$attendance->is_edited)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">On Time</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $attendance->editor ? $attendance->editor->name : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <button onclick="editAttendance({{ $attendance->id }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                                @if($attendance->breaks->count() > 0)
                                    <tr>
                                        <td colspan="8" class="px-6 py-3 bg-gray-50/70">
                                            <div class="text-sm text-gray-600">
                                                <div class="flex items-center gap-1.5 mb-1">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <strong class="text-gray-700">Breaks:</strong>
                                                </div>
                                                @foreach($attendance->breaks as $break)
                                                    <div class="ml-6 text-gray-500">
                                                        Break {{ $loop->iteration }}: {{ $break->break_in->format('H:i') }} - {{ $break->break_out ? $break->break_out->format('H:i') : 'Ongoing' }}
                                                        ({{ $break->duration_minutes ? number_format($break->duration_minutes / 60, 2) . 'h' : 'N/A' }})
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Attendance Modal --}}
    <div id="editModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-0 w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Edit Attendance</h3>
                        <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Clock In Time</label>
                            <input type="time" name="clock_in" id="clock_in" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Clock Out Time</label>
                            <input type="time" name="clock_out" id="clock_out" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Reason</label>
                            <textarea name="reason" id="reason" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" rows="3" placeholder="Enter reason for editing..."></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Document (optional)</label>
                            <input type="file" name="document" id="document" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end gap-3">
                        <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editAttendance(id) {
            document.getElementById('editForm').action = `/attendance/${id}/edit`;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>
</x-app-layout>