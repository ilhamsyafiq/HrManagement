<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Office Locations & Geofencing') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <!-- Geofence Status -->
            <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">Geofence Settings</h3>
                        @if($geofenceEnabled)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Enabled</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">Disabled</span>
                        @endif
                    </div>
                </div>
                <div class="p-6">
                    <div class="text-sm text-gray-600 space-y-1">
                        <p><strong>Status:</strong> {{ $geofenceEnabled ? 'Active - employees must be near an office to clock in/out' : 'Inactive - no location restriction' }}</p>
                        <p><strong>Default Radius:</strong> {{ $defaultRadius }}m</p>
                        <p class="text-xs text-gray-400">To enable/disable geofencing, set <code class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">GEOFENCE_ENABLED=true/false</code> in your .env file.</p>
                    </div>
                </div>
            </div>

            <!-- Add New Office -->
            <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">Add Office Location</h3>
                    </div>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.office-locations.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input type="text" name="name" required placeholder="e.g. Main Office"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm"
                                    value="{{ old('name') }}">
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                                <input type="number" step="any" name="latitude" required placeholder="e.g. 3.1390"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm"
                                    value="{{ old('latitude') }}">
                                @error('latitude') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                                <input type="number" step="any" name="longitude" required placeholder="e.g. 101.6869"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm"
                                    value="{{ old('longitude') }}">
                                @error('longitude') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Radius (meters)</label>
                                <input type="number" name="radius" required placeholder="200" value="{{ old('radius', 200) }}"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                                @error('radius') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Add Office Location
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Office Locations List -->
            <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">Office Locations</h3>
                    </div>
                </div>
                <div class="p-6">
                    @if($offices->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-gray-50/80">
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Latitude</th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Longitude</th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Radius (m)</th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($offices as $office)
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <form method="POST" action="{{ route('admin.office-locations.update', $office->id) }}" class="flex items-center space-x-2" id="edit-form-{{ $office->id }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="name" value="{{ $office->name }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm w-32">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <input type="number" step="any" name="latitude" value="{{ $office->latitude }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm w-28">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <input type="number" step="any" name="longitude" value="{{ $office->longitude }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm w-28">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <input type="number" name="radius" value="{{ $office->radius }}" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm w-20">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <label class="flex items-center">
                                                        <input type="checkbox" name="is_active" value="1" {{ $office->is_active ? 'checked' : '' }}
                                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                        <span class="ml-2 text-sm">
                                                            @if($office->is_active)
                                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Active</span>
                                                            @else
                                                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">Inactive</span>
                                                            @endif
                                                        </span>
                                                    </label>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                    <button type="submit" class="text-indigo-600 hover:text-indigo-800 font-medium">Save</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.office-locations.delete', $office->id) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this office location?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p>No office locations configured.</p>
                            <p class="text-sm mt-1">Add an office location above, or the system will fall back to the default location in the config file.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Flagged Attendances -->
            <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">Flagged Attendance Records</h3>
                    </div>
                </div>
                <div class="p-6">
                    @php
                        $flaggedAttendances = \App\Models\Attendance::with('user')
                            ->where('location_flagged', true)
                            ->orderBy('date', 'desc')
                            ->take(20)
                            ->get();
                    @endphp

                    @if($flaggedAttendances->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-gray-50/80">
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Employee</th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">WFH</th>
                                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Flag Reason</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($flaggedAttendances as $att)
                                        <tr class="hover:bg-gray-50/50 transition-colors bg-amber-50/50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $att->user->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $att->date->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($att->is_wfh)
                                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20">WFH</span>
                                                @else
                                                    <span class="text-gray-400">No</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm text-red-600">{{ $att->location_flag_reason }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No flagged attendance records.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
