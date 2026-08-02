@php
    /**
     * @var array{columns:array<int,string>,rows:array<int,array<int,string>>} $data
     * @var string $title
     */
    $columns = $data['columns'] ?? [];
    $rows = $data['rows'] ?? [];
    $rowCount = count($rows);
@endphp

<div class="w-full" x-data>
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ $title ?? 'Report Data' }}
        </span>
        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-200">
            {{ $rowCount }} {{ \Illuminate\Support\Str::plural('record', $rowCount) }}
        </span>
    </div>

    @if ($rowCount === 0)
        <div class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-gray-300 py-12 text-center dark:border-white/10">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-8 w-8 text-gray-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
            </svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">No records for the selected filters.</p>
        </div>
    @else
        <div class="overflow-x-auto overflow-y-auto rounded-lg border border-gray-200 dark:border-white/10" style="max-height: 70vh;">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800">
                    <tr>
                        @foreach ($columns as $column)
                            <th scope="col" class="whitespace-nowrap px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                                {{ $column }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($rows as $row)
                        <tr class="odd:bg-white even:bg-gray-50 hover:bg-primary-50/50 dark:odd:bg-transparent dark:even:bg-white/5 dark:hover:bg-white/10">
                            @foreach ($row as $cell)
                                <td class="whitespace-nowrap px-3 py-2 text-gray-700 dark:text-gray-200">
                                    {{ $cell }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
