<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Employee Profile') }} - {{ $employee->name }}
            </h2>
            <a href="{{ $isAdmin ? route('employee-profile.edit', $employee->id) : route('employee-profile.edit') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 px-4 rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Profile
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded-xl border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-900/30 px-5 py-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-green-700 dark:text-green-300">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Personal Info --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Profile Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 text-center">
                    <div class="w-24 h-24 mx-auto rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center overflow-hidden">
                        @if($employee->profile && $employee->profile?->profile_photo)
                            <img src="{{ Storage::url($employee->profile?->profile_photo) }}" alt="Profile" class="w-full h-full object-cover">
                        @else
                            <span class="text-3xl font-bold text-indigo-600 dark:text-indigo-300">{{ strtoupper(substr($employee->name, 0, 2)) }}</span>
                        @endif
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-gray-900 dark:text-gray-100">{{ $employee->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $employee->profile?->job_title ?? 'No title set' }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $employee->department->name ?? 'No department' }}</p>
                    <div class="mt-3">
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                            {{ $employee->role->name ?? 'N/A' }}
                        </span>
                    </div>
                    @if($employee->profile && $employee->profile?->hire_date)
                        <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Joined {{ $employee->profile?->hire_date->format('M d, Y') }}</p>
                    @endif
                </div>

                {{-- Personal Details --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Personal Information</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><span class="text-gray-500 dark:text-gray-400">Email:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->email }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Phone:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->profile?->phone ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">IC Number:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->profile?->ic_number ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Date of Birth:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->profile?->date_of_birth?->format('M d, Y') ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Gender:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->profile?->gender ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Marital Status:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->profile?->marital_status ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Shift:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->shift?->name ? $employee->shift->name . ' (' . $employee->shift->start_time->format('H:i') . ' – ' . $employee->shift->end_time->format('H:i') . ')' : '—' }}</span></div>
                        <div class="sm:col-span-2"><span class="text-gray-500 dark:text-gray-400">Address:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">
                            @if($employee->profile && $employee->profile?->address)
                                {{ $employee->profile?->address }}, {{ $employee->profile?->city }}, {{ $employee->profile?->state }} {{ $employee->profile?->postcode }}, {{ $employee->profile?->country }}
                            @else
                                -
                            @endif
                        </span></div>
                    </div>
                </div>
            </div>

            {{-- Emergency Contact & Banking --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Emergency Contact</h3>
                    </div>
                    <div class="p-6 space-y-3 text-sm">
                        <div><span class="text-gray-500 dark:text-gray-400">Name:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->profile?->emergency_contact_name ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Phone:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->profile?->emergency_contact_phone ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Relationship:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->profile?->emergency_contact_relationship ?? '-' }}</span></div>
                    </div>
                </div>

                @if($isAdmin)
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Banking & Statutory</h3>
                    </div>
                    <div class="p-6 space-y-3 text-sm">
                        <div><span class="text-gray-500 dark:text-gray-400">Bank:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->profile?->bank_name ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Account No:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->profile?->bank_account_number ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">EPF No:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->profile?->epf_number ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">SOCSO No:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->profile?->socso_number ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Tax No:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->profile?->tax_number ?? '-' }}</span></div>
                        <div><span class="text-gray-500 dark:text-gray-400">Basic Salary:</span> <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $employee->profile?->basic_salary ? 'RM ' . number_format($employee->profile?->basic_salary, 2) : '-' }}</span></div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Documents --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Documents</h3>
                    <button onclick="document.getElementById('uploadDocModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium py-2 px-4 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Upload Document
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-50/80 dark:bg-gray-700">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">File</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Expiry</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                            @forelse($employee->employeeDocuments as $doc)
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $doc->title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">{{ $doc->category }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $doc->file_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        @if($doc->expiry_date)
                                            <span class="{{ $doc->expiry_date->isPast() ? 'text-red-600 dark:text-red-400 font-medium' : '' }}">
                                                {{ $doc->expiry_date->format('M d, Y') }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm flex gap-2">
                                        <a href="{{ route('employee-profile.document.download', $doc) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">Download</a>
                                        <form action="{{ route('employee-profile.document.delete', $doc) }}" method="POST" onsubmit="return confirm('Delete this document?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400 dark:text-gray-500">No documents uploaded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Employment History --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Employment History</h3>
                    @if($isAdmin)
                        <button onclick="document.getElementById('addHistoryModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium py-2 px-4 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Record
                        </button>
                    @endif
                </div>
                <div class="p-6">
                    @forelse($employee->employmentHistories as $history)
                        <div class="relative pl-8 pb-6 {{ !$loop->last ? 'border-l-2 border-gray-200 dark:border-gray-700' : '' }}">
                            <div class="absolute left-0 top-0 w-4 h-4 rounded-full bg-indigo-500 -translate-x-[7px]"></div>
                            <div class="mb-1">
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $history->action }}</span>
                                <span class="ml-2 text-xs text-gray-400 dark:text-gray-500">{{ $history->effective_date->format('M d, Y') }}</span>
                            </div>
                            @if($history->position)
                                <p class="text-sm text-gray-600 dark:text-gray-400">Position: {{ $history->position }}</p>
                            @endif
                            @if($history->department)
                                <p class="text-sm text-gray-600 dark:text-gray-400">Department: {{ $history->department }}</p>
                            @endif
                            @if($history->salary)
                                <p class="text-sm text-gray-600 dark:text-gray-400">Salary: RM {{ number_format($history->salary, 2) }}</p>
                            @endif
                            @if($history->remarks)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $history->remarks }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-4">No employment history records.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Upload Document Modal --}}
    <div id="uploadDocModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900/80" onclick="document.getElementById('uploadDocModal').classList.add('hidden')"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6 z-10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Upload Document</h3>
                <form action="{{ route('employee-profile.document.store', $employee->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                        <input type="text" name="title" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                        <select name="category" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="Contract">Contract</option>
                            <option value="Certificate">Certificate</option>
                            <option value="ID">ID Document</option>
                            <option value="Resume">Resume/CV</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">File</label>
                        <input type="file" name="file" required class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 dark:file:bg-indigo-900/40 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/60">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expiry Date (Optional)</label>
                        <input type="date" name="expiry_date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes (Optional)</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('uploadDocModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Add History Modal --}}
    @if($isAdmin)
    <div id="addHistoryModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900/80" onclick="document.getElementById('addHistoryModal').classList.add('hidden')"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6 z-10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Add Employment History</h3>
                <form action="{{ route('employee-profile.history.store', $employee->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Action</label>
                        <select name="action" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="Hired">Hired</option>
                            <option value="Promoted">Promoted</option>
                            <option value="Transferred">Transferred</option>
                            <option value="Salary Adjustment">Salary Adjustment</option>
                            <option value="Warning">Warning</option>
                            <option value="Resigned">Resigned</option>
                            <option value="Terminated">Terminated</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Position</label>
                        <input type="text" name="position" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department</label>
                        <input type="text" name="department" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Salary (RM)</label>
                        <input type="number" name="salary" step="0.01" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Effective Date</label>
                        <input type="date" name="effective_date" required value="{{ date('Y-m-d') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Remarks</label>
                        <textarea name="remarks" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('addHistoryModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Add Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</x-app-layout>
