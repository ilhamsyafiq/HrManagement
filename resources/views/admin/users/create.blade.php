<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create User') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <h3 class="text-base font-semibold text-gray-900">New User Information</h3>
                    </div>
                </div>

                <div class="p-6">
                    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password" :value="__('Password')" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="role_id" :value="__('Role')" />
                            <select name="role_id" id="role_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm" required>
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" data-role-name="{{ $role->name }}" {{ (old('role_id') == $role->id || request()->get('role_id') == $role->id) ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1.5 text-xs text-gray-500">
                                <strong>Employee</strong> - Regular employee |
                                <strong>Intern</strong> - Intern with supervisor & internship dates |
                                <strong>Supervisor</strong> - Can manage interns
                            </p>
                            <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="department_id" :value="__('Department')" />
                            <select name="department_id" id="department_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
                        </div>

                        {{-- Supervisor assignment - shown for Employee and Intern roles --}}
                        <div id="supervisor_container" style="display: none;">
                            <x-input-label for="supervisor_id" :value="__('Assign Supervisor')" />
                            <select name="supervisor_id" id="supervisor_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                                <option value="">Select Supervisor</option>
                                @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}" {{ (old('supervisor_id') == $supervisor->id || request()->get('supervisor_id') == $supervisor->id) ? 'selected' : '' }}>{{ $supervisor->name }} ({{ $supervisor->department->name ?? 'No Dept' }})</option>
                                @endforeach
                            </select>
                            <p class="mt-1.5 text-xs text-gray-500" id="supervisor_hint"></p>
                            <x-input-error :messages="$errors->get('supervisor_id')" class="mt-2" />
                        </div>

                        {{-- Hidden is_intern field - auto-set by JS based on role --}}
                        <input type="hidden" name="is_intern" id="is_intern" value="{{ old('is_intern', 0) }}">

                        {{-- Intern-specific fields --}}
                        <div id="intern-fields" class="{{ old('is_intern') ? '' : 'hidden' }}">
                            <div class="bg-blue-50/70 rounded-xl border border-blue-100 p-5">
                                <div class="flex items-center gap-2 mb-4">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <h4 class="font-semibold text-blue-800">Internship Details</h4>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="internship_start_date" :value="__('Internship Start Date')" />
                                        <x-text-input id="internship_start_date" name="internship_start_date" type="date" class="mt-1 block w-full" :value="old('internship_start_date')" />
                                        <x-input-error :messages="$errors->get('internship_start_date')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="internship_end_date" :value="__('Internship End Date')" />
                                        <x-text-input id="internship_end_date" name="internship_end_date" type="date" class="mt-1 block w-full" :value="old('internship_end_date')" />
                                        <x-input-error :messages="$errors->get('internship_end_date')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <a href="{{ route('admin.users') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition duration-150">Cancel</a>
                            <input type="submit" value="{{ __('Create User') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wide focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150 cursor-pointer shadow-sm">
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        function getSelectedRoleName() {
            const select = document.getElementById('role_id');
            const selected = select.options[select.selectedIndex];
            return selected ? (selected.dataset.roleName || '') : '';
        }

        function updateFormFields() {
            const roleName = getSelectedRoleName();
            const supervisorContainer = document.getElementById('supervisor_container');
            const supervisorHint = document.getElementById('supervisor_hint');
            const internFields = document.getElementById('intern-fields');
            const isInternField = document.getElementById('is_intern');

            if (roleName === 'Intern') {
                // Intern: show supervisor (required) + internship fields
                supervisorContainer.style.display = '';
                supervisorHint.textContent = 'Required - Intern must have a supervisor assigned.';
                internFields.classList.remove('hidden');
                isInternField.value = '1';
            } else if (roleName === 'Employee') {
                // Employee: show supervisor (optional)
                supervisorContainer.style.display = '';
                supervisorHint.textContent = 'Optional - Assign a supervisor to this employee.';
                internFields.classList.add('hidden');
                isInternField.value = '0';
            } else {
                // Supervisor, Admin, Super Admin: hide supervisor and intern fields
                supervisorContainer.style.display = 'none';
                internFields.classList.add('hidden');
                isInternField.value = '0';
            }
        }

        document.getElementById('role_id').addEventListener('change', updateFormFields);
        document.addEventListener('DOMContentLoaded', updateFormFields);
    </script>
</x-app-layout>