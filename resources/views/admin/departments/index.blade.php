<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Departments') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Add Department Card -->
            <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <h3 class="text-base font-semibold text-gray-900">All Departments</h3>
                    </div>
                    <button onclick="document.getElementById('addDepartmentModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Department
                    </button>
                </div>

                <div class="overflow-x-auto">
                    @if($departments->count() > 0)
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr class="bg-gray-50/80">
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Head of Dept</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Users</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($departments as $department)
                                    <tr class="hover:bg-gray-50/50 transition duration-150" id="row-{{ $department->id }}">
                                        <!-- Display Mode -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 display-cell-{{ $department->id }}">{{ $department->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 display-cell-{{ $department->id }}">{{ $department->description ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 display-cell-{{ $department->id }}">
                                            @if($department->hod)
                                                <span class="inline-flex items-center gap-1.5">
                                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/20">{{ $department->hod->name }}</span>
                                                </span>
                                            @else
                                                <span class="text-gray-400">Not assigned</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm display-cell-{{ $department->id }}">
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">{{ $department->users_count }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium display-cell-{{ $department->id }}">
                                            <div class="flex items-center gap-3">
                                                <button onclick="toggleEdit({{ $department->id }})" class="inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800 font-medium transition duration-150">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    Edit
                                                </button>
                                                <form method="POST" action="{{ route('admin.departments.delete', $department->id) }}" onsubmit="return confirm('Are you sure you want to delete the department &quot;{{ addslashes($department->name) }}&quot;? This action cannot be undone.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center gap-1.5 text-red-600 hover:text-red-800 font-medium transition duration-150">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>

                                        <!-- Edit Mode (hidden by default) -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm edit-cell-{{ $department->id }} hidden" colspan="5">
                                            <form method="POST" action="{{ route('admin.departments.update', $department->id) }}" class="flex flex-wrap items-end gap-4">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-500 mb-1">Name</label>
                                                    <input type="text" name="name" value="{{ $department->name }}" required
                                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm w-40">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                                                    <input type="text" name="description" value="{{ $department->description }}"
                                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm w-48">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-500 mb-1">Head of Dept</label>
                                                    <select name="hod_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm w-44">
                                                        <option value="">-- None --</option>
                                                        @foreach($users as $u)
                                                            <option value="{{ $u->id }}" {{ $department->hod_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm">
                                                        Save
                                                    </button>
                                                    <button type="button" onclick="toggleEdit({{ $department->id }})" class="inline-flex items-center gap-1.5 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition duration-150">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-12 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <p>No departments found.</p>
                            <p class="text-sm mt-1">Click "Add Department" to create one.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Add Department Modal -->
    <div id="addDepartmentModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('addDepartmentModal').classList.add('hidden')"></div>

            <div class="relative bg-white rounded-2xl shadow-xl border border-gray-100 w-full max-w-lg p-6 z-10">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Add New Department</h3>
                    <button onclick="document.getElementById('addDepartmentModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.departments.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Department Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="e.g. Human Resources"
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm"
                                value="{{ old('name') }}">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="3" placeholder="Brief description of the department..."
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">{{ old('description') }}</textarea>
                            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Head of Department</label>
                            <select name="hod_id" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                                <option value="">-- Select HOD (optional) --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                            @error('hod_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('addDepartmentModal').classList.add('hidden')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition duration-150">
                            Cancel
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Create Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleEdit(id) {
            document.querySelectorAll('.display-cell-' + id).forEach(el => el.classList.toggle('hidden'));
            document.querySelectorAll('.edit-cell-' + id).forEach(el => el.classList.toggle('hidden'));
        }
    </script>
</x-app-layout>
