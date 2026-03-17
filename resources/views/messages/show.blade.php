<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $message->subject ?? 'Message Thread' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Message --}}
            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-green-700">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Back Link --}}
            <div>
                <a href="{{ route('messages.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Messages
                </a>
            </div>

            {{-- Thread Header --}}
            @if($message->subject)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $message->subject }}</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Conversation between {{ $message->sender->name ?? 'Unknown' }} and {{ $message->receiver->name ?? 'Unknown' }}
                    </p>
                </div>
            @endif

            {{-- Messages Thread --}}
            <div class="space-y-4">

                {{-- Original Message --}}
                @php $isCurrentUser = $message->sender_id === auth()->id(); @endphp
                <div class="flex {{ $isCurrentUser ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] {{ $isCurrentUser ? 'order-2' : '' }}">
                        <div class="flex items-center gap-2 mb-1 {{ $isCurrentUser ? 'justify-end' : '' }}">
                            @if(!$isCurrentUser)
                                <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-xs font-semibold text-gray-600">{{ strtoupper(substr($message->sender->name ?? 'U', 0, 1)) }}</span>
                                </div>
                            @endif
                            <span class="text-xs font-medium text-gray-500">{{ $message->sender->name ?? 'Unknown' }}</span>
                            <span class="text-xs text-gray-400">{{ $message->created_at->format('M d, Y g:i A') }}</span>
                            @if($isCurrentUser)
                                <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-xs font-semibold text-indigo-600">{{ strtoupper(substr($message->sender->name ?? 'U', 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="rounded-2xl px-5 py-3 {{ $isCurrentUser ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-100 shadow-sm text-gray-800' }}">
                            <p class="text-sm whitespace-pre-wrap">{{ $message->body }}</p>
                        </div>
                    </div>
                </div>

                {{-- Replies --}}
                @foreach($message->replies as $reply)
                    @php $isCurrentUser = $reply->sender_id === auth()->id(); @endphp
                    <div class="flex {{ $isCurrentUser ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%]">
                            <div class="flex items-center gap-2 mb-1 {{ $isCurrentUser ? 'justify-end' : '' }}">
                                @if(!$isCurrentUser)
                                    <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-xs font-semibold text-gray-600">{{ strtoupper(substr($reply->sender->name ?? 'U', 0, 1)) }}</span>
                                    </div>
                                @endif
                                <span class="text-xs font-medium text-gray-500">{{ $reply->sender->name ?? 'Unknown' }}</span>
                                <span class="text-xs text-gray-400">{{ $reply->created_at->format('M d, Y g:i A') }}</span>
                                @if($isCurrentUser)
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-xs font-semibold text-indigo-600">{{ strtoupper(substr($reply->sender->name ?? 'U', 0, 1)) }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="rounded-2xl px-5 py-3 {{ $isCurrentUser ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-100 shadow-sm text-gray-800' }}">
                                <p class="text-sm whitespace-pre-wrap">{{ $reply->body }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>

            {{-- Reply Form --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">Reply</h3>
                    </div>
                </div>
                <form method="POST" action="{{ route('messages.reply', $message) }}">
                    @csrf
                    <div class="p-6">
                        <textarea name="body" rows="3" required placeholder="Type your reply..." class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm transition-colors duration-200 resize-none">{{ old('body') }}</textarea>
                        @error('body')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 px-6 rounded-xl transition-all duration-200 hover:shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Send Reply
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
