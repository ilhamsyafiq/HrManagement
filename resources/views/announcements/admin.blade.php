<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Announcements') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Message --}}
            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-green-700">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-red-700">Please fix the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Header with Create Button --}}
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">All Announcements</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Manage company-wide and targeted announcements</p>
                </div>
                <button onclick="openCreateModal()" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all duration-200 hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Announcement
                </button>
            </div>

            {{-- Announcements Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Target</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Publish Date</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created By</th>
                                <th class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($announcements as $announcement)
                                @php
                                    $priorityColors = match($announcement->priority) {
                                        'Urgent' => 'bg-red-50 text-red-700 ring-red-600/20',
                                        'High' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
                                        'Normal' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                        'Low' => 'bg-gray-50 text-gray-600 ring-gray-500/20',
                                        default => 'bg-gray-50 text-gray-600 ring-gray-500/20',
                                    };

                                    $isCurrentlyActive = $announcement->is_active
                                        && $announcement->publish_date <= now()
                                        && (!$announcement->expiry_date || $announcement->expiry_date >= now());

                                    $targetLabel = $announcement->target;
                                    if ($announcement->target === 'Department' && $announcement->department) {
                                        $targetLabel = $announcement->department->name;
                                    } elseif ($announcement->target === 'Role' && $announcement->target_role) {
                                        $targetLabel = $announcement->target_role;
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $announcement->title }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ Str::limit($announcement->content, 60) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full {{ $priorityColors }} ring-1 ring-inset">
                                            {{ $announcement->priority }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-700">{{ $targetLabel }}</div>
                                        @if($announcement->target !== 'All')
                                            <div class="text-xs text-gray-400">{{ $announcement->target }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $announcement->publish_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $announcement->expiry_date ? $announcement->expiry_date->format('M d, Y') : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($isCurrentlyActive)
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5 animate-pulse"></span>
                                                Active
                                            </span>
                                        @elseif(!$announcement->is_active)
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-50 text-gray-500 ring-1 ring-inset ring-gray-500/20">
                                                Inactive
                                            </span>
                                        @elseif($announcement->publish_date > now())
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20">
                                                Scheduled
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-50 text-gray-500 ring-1 ring-inset ring-gray-500/20">
                                                Expired
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $announcement->creator->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="openEditModal({{ $announcement->id }}, {{ json_encode([
                                                'title' => $announcement->title,
                                                'content' => $announcement->content,
                                                'priority' => $announcement->priority,
                                                'target' => $announcement->target,
                                                'department_id' => $announcement->department_id,
                                                'target_role' => $announcement->target_role,
                                                'publish_date' => $announcement->publish_date->format('Y-m-d'),
                                                'expiry_date' => $announcement->expiry_date ? $announcement->expiry_date->format('Y-m-d') : '',
                                                'is_active' => $announcement->is_active,
                                            ]) }})" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                Edit
                                            </button>
                                            <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this announcement?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-red-700 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-400">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                        </svg>
                                        No announcements yet. Create your first announcement to get started.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Announcement Modal --}}
    <div id="createAnnouncementModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeCreateModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 z-10">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Create Announcement</h3>
                    <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('announcements.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Announcement title">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                        <textarea name="content" rows="4" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Write your announcement content here..."></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <select name="priority" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="Low">Low</option>
                                <option value="Normal" selected>Normal</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Target Audience</label>
                            <select name="target" id="create_target" onchange="toggleTargetFields('create')" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="All">All Employees</option>
                                <option value="Department">Specific Department</option>
                                <option value="Role">Specific Role</option>
                            </select>
                        </div>
                    </div>
                    <div id="create_department_field" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <select name="department_id" id="create_department_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="create_role_field" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="target_role" id="create_target_role" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Publish Date</label>
                            <input type="date" name="publish_date" required value="{{ now()->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date (Optional)</label>
                            <input type="date" name="expiry_date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" id="create_is_active" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="create_is_active" class="text-sm text-gray-700">Active (visible to targeted users)</label>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="closeCreateModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Create Announcement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Announcement Modal --}}
    <div id="editAnnouncementModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeEditModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 z-10">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Announcement</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form id="editAnnouncementForm" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" id="edit_title" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                        <textarea name="content" id="edit_content" rows="4" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <select name="priority" id="edit_priority" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="Low">Low</option>
                                <option value="Normal">Normal</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Target Audience</label>
                            <select name="target" id="edit_target" onchange="toggleTargetFields('edit')" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="All">All Employees</option>
                                <option value="Department">Specific Department</option>
                                <option value="Role">Specific Role</option>
                            </select>
                        </div>
                    </div>
                    <div id="edit_department_field" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <select name="department_id" id="edit_department_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="edit_role_field" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="target_role" id="edit_target_role" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Publish Date</label>
                            <input type="date" name="publish_date" id="edit_publish_date" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date (Optional)</label>
                            <input type="date" name="expiry_date" id="edit_expiry_date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" id="edit_is_active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="edit_is_active" class="text-sm text-gray-700">Active (visible to targeted users)</label>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">Update Announcement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('createAnnouncementModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createAnnouncementModal').classList.add('hidden');
        }

        function openEditModal(id, data) {
            var form = document.getElementById('editAnnouncementForm');
            form.action = '/announcements/' + id;

            document.getElementById('edit_title').value = data.title;
            document.getElementById('edit_content').value = data.content;
            document.getElementById('edit_priority').value = data.priority;
            document.getElementById('edit_target').value = data.target;
            document.getElementById('edit_department_id').value = data.department_id || '';
            document.getElementById('edit_target_role').value = data.target_role || '';
            document.getElementById('edit_publish_date').value = data.publish_date;
            document.getElementById('edit_expiry_date').value = data.expiry_date || '';
            document.getElementById('edit_is_active').checked = data.is_active;

            toggleTargetFields('edit');
            document.getElementById('editAnnouncementModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editAnnouncementModal').classList.add('hidden');
        }

        function toggleTargetFields(prefix) {
            var target = document.getElementById(prefix + '_target').value;
            var deptField = document.getElementById(prefix + '_department_field');
            var roleField = document.getElementById(prefix + '_role_field');

            if (target === 'Department') {
                deptField.classList.remove('hidden');
                roleField.classList.add('hidden');
            } else if (target === 'Role') {
                deptField.classList.add('hidden');
                roleField.classList.remove('hidden');
            } else {
                deptField.classList.add('hidden');
                roleField.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>
