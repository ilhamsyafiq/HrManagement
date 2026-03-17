<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ auth()->user()->isIntern() ? __('My Reports') : __('Intern Reports') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <h3 class="text-base font-semibold text-gray-900">
                            {{ auth()->user()->isIntern() ? 'My Reports' : 'Intern Reports' }}
                        </h3>
                    </div>
                    @if(auth()->user()->isIntern())
                        <a href="{{ route('reports.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition duration-150 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Submit New Report
                        </a>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50/80">
                                @if(auth()->user()->isSupervisor())
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Intern</th>
                                @endif
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Uploaded Date</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($reports as $report)
                                <tr class="hover:bg-gray-50/50 transition duration-150">
                                    @if(auth()->user()->isSupervisor())
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $report->user->name ?? 'N/A' }}</td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $report->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $report->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($report->status == 'signed')
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Signed</span>
                                        @elseif($report->status == 'pending')
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">Pending</span>
                                        @elseif($report->status == 'draft')
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-600/20">Draft</span>
                                        @elseif($report->status == 'revised')
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20">Revised</span>
                                        @elseif($report->status == 'rejected')
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('reports.show', $report->id) }}" class="text-indigo-600 hover:text-indigo-800 transition duration-150">View</a>
                                            <a href="{{ route('reports.download', $report->id) }}" class="text-gray-600 hover:text-gray-800 transition duration-150">Download</a>

                                            {{-- Intern actions --}}
                                            @if(auth()->user()->isIntern())
                                                @if(in_array($report->status, ['draft', 'revised', 'rejected']))
                                                    <a href="{{ route('reports.edit', $report->id) }}" class="text-blue-600 hover:text-blue-800 transition duration-150">Edit</a>
                                                    <form action="{{ route('reports.submit', $report->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-emerald-600 hover:text-emerald-800 transition duration-150" onclick="return confirm('Submit this report to your supervisor?')">Submit</button>
                                                    </form>
                                                @endif
                                                @if($report->status == 'signed' && $report->signed_path)
                                                    <a href="{{ route('reports.download-signed', $report->id) }}" class="text-emerald-600 hover:text-emerald-800 font-semibold transition duration-150">Download Signed</a>
                                                @endif
                                                @if($report->status == 'draft')
                                                    <form action="{{ route('reports.destroy', $report->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-800 transition duration-150" onclick="return confirm('Delete this report?')">Delete</button>
                                                    </form>
                                                @endif
                                            @endif

                                            {{-- Supervisor actions --}}
                                            @if(auth()->user()->isSupervisor())
                                                @if($report->status == 'pending')
                                                    <a href="{{ route('reports.sign.form', $report->id) }}" class="text-emerald-600 hover:text-emerald-800 font-semibold transition duration-150">Sign</a>
                                                @endif
                                                @if($report->status == 'signed' && $report->signed_path)
                                                    <a href="{{ route('reports.download-signed', $report->id) }}" class="text-emerald-600 hover:text-emerald-800 transition duration-150">Download Signed</a>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                {{-- Show comments for rejected/revised reports --}}
                                @if(($report->status == 'rejected' || $report->status == 'revised') && $report->comments)
                                    <tr class="bg-red-50/50">
                                        <td colspan="{{ auth()->user()->isSupervisor() ? 5 : 4 }}" class="px-6 py-3">
                                            <div class="flex items-start gap-2">
                                                <svg class="w-4 h-4 text-red-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                                <p class="text-sm text-red-700"><span class="font-semibold">Supervisor Comments:</span> {{ $report->comments }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->isSupervisor() ? 5 : 4 }}" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <p class="mt-3 text-sm text-gray-500">
                                            No reports found.
                                            @if(auth()->user()->isIntern())
                                                <a href="{{ route('reports.create') }}" class="text-indigo-600 hover:text-indigo-800 font-medium ml-1">Submit your first report</a>
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>