<x-filament-panels::page>
    <div class="overflow-x-auto rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10">
        <table class="w-full text-sm text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-white/5">
                    <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-white/10 whitespace-nowrap">
                        Employee
                    </th>
                    @foreach ($days as $dayNum => $dayName)
                        <th class="px-3 py-3 font-semibold text-center border-b border-l border-gray-200 dark:border-white/10 whitespace-nowrap {{ in_array((int) $dayNum, [0, 6]) ? 'text-amber-600 dark:text-amber-400' : 'text-gray-700 dark:text-gray-200' }}">
                            {{ \Illuminate\Support\Str::substr($dayName, 0, 3) }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-4 py-3 font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-white/10 whitespace-nowrap">
                            {{ $row['name'] }}
                        </td>
                        @foreach ($days as $dayNum => $dayName)
                            @php($cell = $row['byDay'][$dayNum] ?? null)
                            <td class="px-2 py-2 text-center align-middle border-b border-l border-gray-200 dark:border-white/10">
                                @if ($cell)
                                    <a href="{{ \App\Filament\Resources\ShiftAssignmentResource::getUrl('edit', ['record' => $cell['id']]) }}"
                                       title="Edit assignment"
                                       class="inline-flex flex-col items-center gap-0.5 rounded-lg px-2 py-1 min-w-[72px] transition hover:ring-2 {{ $cell['flexible'] ? 'bg-purple-50 dark:bg-purple-500/10 ring-1 ring-purple-200 dark:ring-purple-500/30' : 'bg-primary-50 dark:bg-primary-500/10 ring-1 ring-primary-200 dark:ring-primary-500/30' }}">
                                        <span class="font-semibold text-gray-800 dark:text-gray-100 leading-tight">{{ $cell['shift'] }}</span>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight">{{ $cell['hours'] }}</span>
                                    </a>
                                @else
                                    <span class="text-xs text-gray-300 dark:text-gray-600">Off</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($days) + 1 }}" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                            No employees found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400">
        One row per employee; columns are Sun–Sat. Click a shift to edit it, or use <strong>New assignment</strong> to add days. Blank days are rest/off days.
    </p>
</x-filament-panels::page>
