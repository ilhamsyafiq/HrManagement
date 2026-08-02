<x-filament-panels::page>
    @php($m = $this->getMatrix())

    <div class="fi-section rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10">
                        <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-800 px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 whitespace-nowrap">Employee</th>
                        @foreach ($m['days'] as $i => $day)
                            <th class="px-3 py-3 font-semibold text-center whitespace-nowrap {{ in_array((int) $i, [0, 6]) ? 'text-amber-600 dark:text-amber-400' : 'text-gray-700 dark:text-gray-200' }}">
                                {{ \Illuminate\Support\Str::substr($day, 0, 3) }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($m['users'] as $u)
                        <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="sticky left-0 z-10 bg-white dark:bg-gray-900 px-4 py-3 font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $u['name'] }}</td>
                            @foreach ($m['days'] as $i => $day)
                                @php($cell = $u['byDay'][$i] ?? null)
                                <td class="px-2 py-2 text-center align-middle">
                                    @if ($cell)
                                        <a href="{{ \App\Filament\Resources\ShiftAssignmentResource::getUrl('edit', ['record' => $cell['id']]) }}"
                                           class="inline-flex flex-col items-center gap-0.5 rounded-lg px-2 py-1 min-w-[72px] {{ $cell['flexible'] ? 'bg-purple-50 dark:bg-purple-500/10 ring-1 ring-purple-200 dark:ring-purple-500/30' : 'bg-primary-50 dark:bg-primary-500/10 ring-1 ring-primary-200 dark:ring-primary-500/30' }} hover:ring-2 transition"
                                           title="Edit assignment">
                                            <span class="font-semibold text-gray-800 dark:text-gray-100 leading-tight">{{ $cell['shift'] }}</span>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight">{{ $cell['hours'] }}</span>
                                        </a>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600 text-xs">Off</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($m['days']) + 1 }}" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
                                No shift assignments yet. Use <strong>New assignment</strong> to build the roster.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400">
        Each employee appears once; columns are Sun–Sat. Click a shift to edit it, or use <strong>New assignment</strong> to add days. Blank days are rest/off days.
    </p>
</x-filament-panels::page>
