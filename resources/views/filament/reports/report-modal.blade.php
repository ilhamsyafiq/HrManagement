<div class="w-full" x-data="{ tab: 'data' }">
    {{-- Segmented tab switch --}}
    <div class="mb-4 inline-flex rounded-lg bg-gray-100 p-1 dark:bg-white/5">
        <button type="button" @click="tab = 'data'"
            :class="tab === 'data' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
            class="inline-flex items-center gap-1.5 rounded-md px-4 py-1.5 text-sm font-medium transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            Data
        </button>
        <button type="button" @click="tab = 'pdf'"
            :class="tab === 'pdf' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
            class="inline-flex items-center gap-1.5 rounded-md px-4 py-1.5 text-sm font-medium transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
            PDF
        </button>
    </div>

    <div x-show="tab === 'data'">
        @include('filament.reports.data-table', ['data' => $data, 'title' => $title])
    </div>

    <div x-show="tab === 'pdf'" x-cloak>
        @include('filament.reports.pdf-preview', ['url' => $url])
    </div>
</div>
