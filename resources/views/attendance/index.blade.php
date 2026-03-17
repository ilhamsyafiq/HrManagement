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
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($attendances as $attendance)
                                <tr class="hover:bg-gray-50/50 transition duration-150 {{ ($attendance->is_late || $attendance->is_early_leave) ? 'bg-red-50/40' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $attendance->date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $attendance->is_late ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                        {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}
                                        @if($attendance->is_late)
                                            <span class="inline-flex items-center ml-1 px-1.5 py-0.5 text-xs font-bold rounded bg-red-100 text-red-700">LATE ({{ $attendance->late_minutes }}min)</span>
                                        @endif
                                        @if($attendance->clock_in_address)
                                            <br><small class="text-gray-400">{{ $attendance->clock_in_address }}</small>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $attendance->is_early_leave ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                        {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}
                                        @if($attendance->is_early_leave)
                                            <span class="inline-flex items-center ml-1 px-1.5 py-0.5 text-xs font-bold rounded bg-red-100 text-red-700">EARLY ({{ $attendance->early_leave_minutes }}min)</span>
                                        @endif
                                        @if($attendance->clock_out_address)
                                            <br><small class="text-gray-400">{{ $attendance->clock_out_address }}</small>
                                        @endif
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
                                            @if($attendance->is_edited)
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">Edited</span>
                                            @endif
                                            @if(!$attendance->is_late && !$attendance->is_early_leave && !$attendance->is_wfh && !$attendance->is_edited)
                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">On Time</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if(!$attendance->clock_out)
                                            <button onclick="editAttendance({{ $attendance->id }})" class="inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800 font-medium transition duration-150">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                Edit
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @if($attendance->breaks->count() > 0)
                                    <tr>
                                        <td colspan="6" class="px-6 py-3 bg-gray-50/50">
                                            <div class="text-sm text-gray-600">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <strong class="text-gray-700">Breaks:</strong>
                                                </div>
                                                @foreach($attendance->breaks as $break)
                                                    <div class="ml-6 py-0.5">
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

    <!-- Edit Attendance Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto max-w-md">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <h3 class="text-base font-semibold text-gray-900">Edit Attendance</h3>
                    </div>
                </div>
                <div class="p-6">
                    <form id="editForm" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Clock In Time</label>
                            <input type="time" name="clock_in" id="clock_in" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Clock Out Time</label>
                            <input type="time" name="clock_out" id="clock_out" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Reason</label>
                            <textarea name="reason" id="reason" required class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm" rows="3" placeholder="Please provide a reason for the edit..."></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Document (optional)</label>
                            <input type="file" name="document" id="document" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition duration-150">
                        </div>
                        <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                            <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition duration-150">Cancel</button>
                            <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm">Save Changes</button>
                        </div>
                    </form>
                </div>
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