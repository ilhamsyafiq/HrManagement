<x-filament-panels::page>
    {{-- Filters --}}
    <x-filament::section>
        <x-slot name="heading">Report filters</x-slot>
        <x-slot name="description">These apply to whichever report you open below.</x-slot>

        {{ $this->form }}
    </x-filament::section>

    {{-- Report cards --}}
    <div>
        <h2 class="mb-1 text-base font-semibold text-gray-950 dark:text-white">Available reports</h2>
        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Open a report to review its data on screen, then print or download the PDF.</p>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->reportCards() as $card)
                <div class="group flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm ring-1 ring-transparent transition hover:border-primary-300 hover:ring-primary-100 dark:border-white/10 dark:bg-gray-900 dark:hover:border-primary-500/40 dark:hover:ring-0">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary-50 text-primary-600 transition group-hover:bg-primary-100 dark:bg-primary-500/10 dark:text-primary-400">
                        <x-filament::icon :icon="$card['icon']" class="h-6 w-6" />
                    </span>

                    <h3 class="mt-4 text-base font-semibold text-gray-950 dark:text-white">{{ $card['label'] }}</h3>
                    <p class="mt-1 flex-1 text-sm leading-relaxed text-gray-500 dark:text-gray-400">{{ $card['desc'] }}</p>

                    <div class="mt-4">
                        {{ $this->{$card['action']} }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
