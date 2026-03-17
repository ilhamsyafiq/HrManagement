<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Messages') }}
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

            {{-- Main Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- Header with Compose Button --}}
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">Messages</h3>
                        @if($unreadCount > 0)
                            <span class="inline-flex items-center justify-center px-2.5 py-0.5 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-700">
                                {{ $unreadCount }} unread
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('messages.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all duration-200 hover:shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Compose New Message
                    </a>
                </div>

                {{-- Tabs --}}
                <div class="border-b border-gray-100">
                    <div class="flex">
                        <button onclick="switchTab('inbox')" id="tab-inbox" class="tab-btn px-6 py-3 text-sm font-medium border-b-2 border-indigo-500 text-indigo-600 transition-colors duration-200">
                            Inbox
                            @if($unreadCount > 0)
                                <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 text-xs font-bold rounded-full bg-indigo-100 text-indigo-700">{{ $unreadCount }}</span>
                            @endif
                        </button>
                        <button onclick="switchTab('sent')" id="tab-sent" class="tab-btn px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors duration-200">
                            Sent
                        </button>
                    </div>
                </div>

                {{-- Inbox Tab --}}
                <div id="panel-inbox" class="tab-panel">
                    @forelse($inbox as $msg)
                        @php
                            $hasUnread = ($msg->receiver_id === auth()->id() && !$msg->is_read) || $msg->replies->count() > 0;
                            $lastReply = $msg->replies->sortByDesc('created_at')->first();
                            $displayTime = $lastReply ? $lastReply->created_at : $msg->created_at;
                            $otherUser = $msg->sender_id === auth()->id() ? $msg->receiver : $msg->sender;
                        @endphp
                        <a href="{{ route('messages.show', $msg) }}" class="block px-6 py-4 border-b border-gray-50 hover:bg-gray-50/60 transition-colors duration-150 {{ $hasUnread ? 'bg-indigo-50/50' : '' }}">
                            <div class="flex items-start gap-4">
                                {{-- Avatar --}}
                                <div class="shrink-0 w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-sm font-semibold text-indigo-600">{{ strtoupper(substr($otherUser->name ?? 'U', 0, 1)) }}</span>
                                </div>
                                {{-- Content --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            @if($hasUnread)
                                                <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                                            @endif
                                            <span class="text-sm font-semibold text-gray-900 truncate">{{ $otherUser->name ?? 'Unknown User' }}</span>
                                        </div>
                                        <span class="text-xs text-gray-400 shrink-0">{{ $displayTime->diffForHumans() }}</span>
                                    </div>
                                    @if($msg->subject)
                                        <p class="text-sm font-medium text-gray-700 truncate mt-0.5">{{ $msg->subject }}</p>
                                    @endif
                                    <p class="text-sm text-gray-500 truncate mt-0.5">{{ Str::limit($msg->body, 80) }}</p>
                                    @if($msg->replies->count() > 0 || $msg->replies()->count() > 0)
                                        <span class="inline-flex items-center gap-1 mt-1 text-xs text-gray-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                            </svg>
                                            {{ $msg->replies()->count() }} {{ Str::plural('reply', $msg->replies()->count()) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-gray-500">Your inbox is empty.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Sent Tab --}}
                <div id="panel-sent" class="tab-panel hidden">
                    @forelse($sent as $msg)
                        <a href="{{ route('messages.show', $msg) }}" class="block px-6 py-4 border-b border-gray-50 hover:bg-gray-50/60 transition-colors duration-150">
                            <div class="flex items-start gap-4">
                                {{-- Avatar --}}
                                <div class="shrink-0 w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                    <span class="text-sm font-semibold text-gray-500">{{ strtoupper(substr($msg->receiver->name ?? 'U', 0, 1)) }}</span>
                                </div>
                                {{-- Content --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-sm font-semibold text-gray-900 truncate">To: {{ $msg->receiver->name ?? 'Unknown User' }}</span>
                                        <span class="text-xs text-gray-400 shrink-0">{{ $msg->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if($msg->subject)
                                        <p class="text-sm font-medium text-gray-700 truncate mt-0.5">{{ $msg->subject }}</p>
                                    @endif
                                    <p class="text-sm text-gray-500 truncate mt-0.5">{{ Str::limit($msg->body, 80) }}</p>
                                    @if($msg->replies()->count() > 0)
                                        <span class="inline-flex items-center gap-1 mt-1 text-xs text-gray-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                            </svg>
                                            {{ $msg->replies()->count() }} {{ Str::plural('reply', $msg->replies()->count()) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <p class="text-sm text-gray-500">No sent messages yet.</p>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Hide all panels
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
            // Reset all tabs
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('border-indigo-500', 'text-indigo-600');
                b.classList.add('border-transparent', 'text-gray-500');
            });

            // Show selected panel
            document.getElementById('panel-' + tab).classList.remove('hidden');
            // Activate selected tab
            const activeTab = document.getElementById('tab-' + tab);
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-indigo-500', 'text-indigo-600');
        }
    </script>
</x-app-layout>
