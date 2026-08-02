<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Report Bug / Feedback') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Success message --}}
            @if(session('success'))
                <div class="rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/30 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-green-700 dark:text-green-300">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/30 px-5 py-4">
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-red-700 dark:text-red-300">Please fix the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 ml-8 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('feedback.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="page_url" value="{{ url()->previous() }}">

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">

                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3V3zm6 12l2-2 4 4m-4-8h.01"/>
                            </svg>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Tell us what happened</h3>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">This is a temporary tool to collect bug reports and feedback.</p>
                    </div>

                    <div class="p-6 space-y-6">

                        {{-- Type --}}
                        <div>
                            <x-input-label for="type" :value="__('Type')" class="mb-1.5" />
                            <select id="type" name="type" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm transition-colors duration-200">
                                <option value="bug" {{ old('type') === 'bug' ? 'selected' : '' }}>Bug</option>
                                <option value="feedback" {{ old('type') === 'feedback' ? 'selected' : '' }}>Feedback</option>
                            </select>
                        </div>

                        {{-- Title --}}
                        <div>
                            <x-input-label for="title" :value="__('Title')" class="mb-1.5" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full rounded-xl"
                                :value="old('title')" required maxlength="255" placeholder="e.g. Clock-in button not responding" />
                        </div>

                        {{-- Description --}}
                        <div>
                            <x-input-label for="description" :value="__('Description')" class="mb-1.5" />
                            <textarea id="description" name="description" rows="5" required maxlength="5000"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm transition-colors duration-200"
                                placeholder="Describe the issue or your feedback in detail...">{{ old('description') }}</textarea>
                        </div>

                        {{-- Image --}}
                        <div>
                            <x-input-label for="image" :value="__('Screenshot / Image (optional)')" class="mb-1.5" />
                            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png"
                                class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 dark:file:bg-indigo-900/40 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/60">
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">JPG or PNG, up to 4 MB.</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-end gap-4">
                        <a href="{{ url()->previous() }}" class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl transition-all duration-200">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 px-6 rounded-xl transition-all duration-200 hover:shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Submit Report
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
