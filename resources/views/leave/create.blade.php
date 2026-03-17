<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Apply for Leave') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Main Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- Section Header --}}
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">New Leave Application</h3>
                    </div>
                </div>

                {{-- Form --}}
                <div class="p-6">
                    <form method="POST" action="{{ route('leave.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        {{-- Leave Type --}}
                        <div>
                            <x-input-label for="type" :value="__('Leave Type')" class="mb-1.5" />
                            <select name="type" id="type" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm transition-colors duration-200" required>
                                <option value="">Select Type</option>
                                <option value="AL">Annual Leave</option>
                                <option value="MC">Medical Certificate</option>
                                <option value="Emergency">Emergency Leave</option>
                                <option value="Intern">Intern Leave</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        {{-- Date Fields --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="start_date" :value="__('Start Date')" class="mb-1.5" />
                                <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full rounded-xl" :value="old('start_date')" required />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="end_date" :value="__('End Date')" class="mb-1.5" />
                                <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full rounded-xl" :value="old('end_date')" required />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Reason --}}
                        <div>
                            <x-input-label for="reason" :value="__('Reason')" class="mb-1.5" />
                            <textarea id="reason" name="reason" rows="4" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm transition-colors duration-200 resize-none" required>{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        {{-- Document Upload --}}
                        <div>
                            <x-input-label for="document" :value="__('Supporting Document (optional)')" class="mb-1.5" />
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 border-dashed rounded-xl hover:border-blue-400 transition-colors duration-200">
                                <div class="space-y-2 text-center">
                                    <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <div class="text-sm text-gray-600">
                                        <label for="document" class="relative cursor-pointer rounded-md font-semibold text-blue-600 hover:text-blue-500 transition-colors duration-150">
                                            <span>Upload a file</span>
                                            <input type="file" id="document" name="document" class="sr-only" accept=".pdf,.jpg,.jpeg,.png" />
                                        </label>
                                        <span class="pl-1">or drag and drop</span>
                                    </div>
                                    <p class="text-xs text-gray-400">PDF, JPG, JPEG, PNG up to 2MB</p>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('document')" class="mt-2" />
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('leave.index') }}" class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all duration-200">
                                Cancel
                            </a>
                            <x-primary-button class="rounded-xl px-6 py-2.5">
                                {{ __('Submit Application') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
