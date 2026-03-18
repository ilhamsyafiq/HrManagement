<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Compose Message') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4">
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-red-700">Please fix the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside text-sm text-red-600 ml-8 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Main Card --}}
            <form method="POST" action="{{ route('messages.store') }}">
                @csrf

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    {{-- Section Header --}}
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <h3 class="text-base font-semibold text-gray-900">New Message</h3>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">

                        {{-- Recipient --}}
                        <div>
                            <label for="receiver_id" class="block text-sm font-medium text-gray-700 mb-1.5">Recipient</label>
                            <select id="receiver_id" name="receiver_id" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm transition-colors duration-200">
                                <option value="">Select a recipient...</option>
                                @if(isset($colleagues) && $colleagues->isNotEmpty())
                                    <optgroup label="My Team (Same Supervisor)">
                                        @foreach($colleagues as $recipient)
                                            <option value="{{ $recipient->id }}" {{ old('receiver_id') == $recipient->id ? 'selected' : '' }}>
                                                {{ $recipient->name }} ({{ $recipient->role->name ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if(isset($grouped))
                                    @foreach($grouped as $role => $users)
                                        <optgroup label="{{ $role }}">
                                            @foreach($users as $recipient)
                                                <option value="{{ $recipient->id }}" {{ old('receiver_id') == $recipient->id ? 'selected' : '' }}>
                                                    {{ $recipient->name }}{{ $recipient->department ? ' - ' . $recipient->department->name : '' }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @endif
                                @if(isset($byDept) && $byDept->isNotEmpty())
                                    @foreach($byDept as $dept => $users)
                                        <optgroup label="Dept: {{ $dept }}">
                                            @foreach($users as $recipient)
                                                <option value="{{ $recipient->id }}" {{ old('receiver_id') == $recipient->id ? 'selected' : '' }}>
                                                    {{ $recipient->name }} ({{ $recipient->role->name ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @endif
                            </select>
                            @if($recipients->isEmpty())
                                <p class="mt-2 text-sm text-gray-500">No recipients available. You can only message users within your organizational hierarchy.</p>
                            @endif
                        </div>

                        {{-- Subject --}}
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1.5">Subject (optional)</label>
                            <input id="subject" name="subject" type="text" value="{{ old('subject') }}" placeholder="Message subject..." class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm transition-colors duration-200">
                        </div>

                        {{-- Body --}}
                        <div>
                            <label for="body" class="block text-sm font-medium text-gray-700 mb-1.5">Message</label>
                            <textarea id="body" name="body" rows="6" required placeholder="Type your message here..." class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm transition-colors duration-200 resize-none">{{ old('body') }}</textarea>
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <a href="{{ route('messages.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors duration-200">
                            &larr; Back to Messages
                        </a>
                        <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 px-6 rounded-xl transition-all duration-200 hover:shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Send Message
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
