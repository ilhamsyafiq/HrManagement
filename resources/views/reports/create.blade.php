<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Submit Internship Report') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden rounded-2xl shadow-sm border border-gray-100">
                <!-- Section Header -->
                <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Report Details') }}</h3>
                    </div>
                </div>

                <div class="p-6">
                    <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="space-y-5">
                            <div>
                                <x-input-label for="title" :value="__('Report Title')" />
                                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="file" :value="__('Report File')" />
                                <input id="file" name="file" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept=".pdf,.doc,.docx" required />
                                <p class="mt-1 text-sm text-gray-600">Accepted formats: PDF, DOC, DOCX. Maximum size: 10MB.</p>
                                <x-input-error :messages="$errors->get('file')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 mt-6">
                            <a href="{{ route('reports.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition duration-150">Cancel</a>

                            <x-primary-button>
                                {{ __('Submit Report') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
