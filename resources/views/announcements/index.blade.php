<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Announcements') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($announcements->isEmpty())
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-700 mb-1">No Announcements</h3>
                    <p class="text-sm text-gray-500">There are no active announcements at this time.</p>
                </div>
            @endif

            @foreach($announcements as $announcement)
                @php
                    $priorityConfig = match($announcement->priority) {
                        'Urgent' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'badge_bg' => 'bg-red-100', 'badge_text' => 'text-red-700', 'ring' => 'ring-red-600/20', 'accent' => 'bg-red-500'],
                        'High' => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'badge_bg' => 'bg-orange-100', 'badge_text' => 'text-orange-700', 'ring' => 'ring-orange-600/20', 'accent' => 'bg-orange-500'],
                        'Normal' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'badge_bg' => 'bg-blue-100', 'badge_text' => 'text-blue-700', 'ring' => 'ring-blue-600/20', 'accent' => 'bg-blue-500'],
                        'Low' => ['bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'badge_bg' => 'bg-gray-100', 'badge_text' => 'text-gray-600', 'ring' => 'ring-gray-500/20', 'accent' => 'bg-gray-400'],
                        default => ['bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'badge_bg' => 'bg-gray-100', 'badge_text' => 'text-gray-600', 'ring' => 'ring-gray-500/20', 'accent' => 'bg-gray-400'],
                    };
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border {{ $priorityConfig['border'] }} overflow-hidden">
                    {{-- Priority accent bar --}}
                    <div class="h-1 {{ $priorityConfig['accent'] }}"></div>

                    <div class="p-6">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full {{ $priorityConfig['badge_bg'] }} {{ $priorityConfig['badge_text'] }} ring-1 ring-inset {{ $priorityConfig['ring'] }}">
                                    @if($announcement->priority === 'Urgent')
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    @endif
                                    {{ $announcement->priority }}
                                </span>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $announcement->title }}</h3>
                            </div>
                        </div>

                        <div class="prose prose-sm max-w-none text-gray-700 mb-4">
                            {!! nl2br(e($announcement->content)) !!}
                        </div>

                        <div class="flex items-center gap-4 text-xs text-gray-500">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $announcement->publish_date->format('M d, Y') }}
                            </div>
                            @if($announcement->creator)
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    {{ $announcement->creator->name }}
                                </div>
                            @endif
                            @if($announcement->expiry_date)
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Expires {{ $announcement->expiry_date->format('M d, Y') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</x-app-layout>
