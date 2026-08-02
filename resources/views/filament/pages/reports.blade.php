<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Report Filters
        </x-slot>
        <x-slot name="description">
            Set the date range, department and employee, then choose a report to download.
        </x-slot>

        {{ $this->form }}
    </x-filament::section>
</x-filament-panels::page>
