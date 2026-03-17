<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('All Claims') }}
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

            @if(session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-red-700">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Main Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- Section Header with Filters --}}
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                            </svg>
                            <h3 class="text-base font-semibold text-gray-900">Claims Management</h3>
                        </div>

                        {{-- Status Filter --}}
                        <form method="GET" action="{{ route('claims.index') }}" class="flex items-center gap-3">
                            <select name="status" onchange="this.form.submit()" class="text-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm transition-colors duration-200">
                                <option value="">All Statuses</option>
                                <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </form>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount (RM)</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($claims as $claim)
                                <tr class="hover:bg-gray-50/60 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $claim->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $claim->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($claim->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($claim->status == 'Draft')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-600/20">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                                Draft
                                            </span>
                                        @elseif($claim->status == 'Pending')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                                Pending
                                            </span>
                                        @elseif($claim->status == 'Approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                                Approved
                                            </span>
                                        @elseif($claim->status == 'Rejected')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                                Rejected
                                            </span>
                                        @elseif($claim->status == 'Paid')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/20">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                                Paid
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $claim->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('claims.show', $claim) }}" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-medium transition-colors duration-150">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                View
                                            </a>

                                            @if($claim->status == 'Pending')
                                                <form method="POST" action="{{ route('claims.approve', $claim) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center gap-1 text-green-600 hover:text-green-800 font-medium transition-colors duration-150" onclick="return confirm('Approve this claim?')">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        Approve
                                                    </button>
                                                </form>
                                                <button type="button" onclick="openQuickReject({{ $claim->id }})" class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 font-medium transition-colors duration-150">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                    Reject
                                                </button>
                                            @endif

                                            @if($claim->status == 'Approved')
                                                <form method="POST" action="{{ route('claims.mark-paid', $claim) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 font-medium transition-colors duration-150" onclick="return confirm('Mark this claim as paid?')">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                                        </svg>
                                                        Mark Paid
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                        </svg>
                                        <p class="text-sm text-gray-500">No claims found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($claims->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $claims->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Quick Reject Modal --}}
    <div id="quickRejectModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeQuickReject()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 z-10">
                <div class="flex items-center gap-3 mb-5">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Reject Claim</h3>
                </div>
                <form id="quickRejectForm" method="POST" action="">
                    @csrf
                    @method('PATCH')
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Rejection Reason:</label>
                        <textarea name="rejection_reason" required class="block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-xl shadow-sm resize-none transition-colors duration-200" rows="3" placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeQuickReject()" class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all duration-200 hover:shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openQuickReject(claimId) {
            document.getElementById('quickRejectForm').action = '/claims/' + claimId + '/reject';
            document.getElementById('quickRejectModal').classList.remove('hidden');
        }

        function closeQuickReject() {
            document.getElementById('quickRejectModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
