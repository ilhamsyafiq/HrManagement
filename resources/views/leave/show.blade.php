<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leave Details') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Main Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- Section Header --}}
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">Leave Application Details</h3>
                    </div>
                    <a href="{{ route('leave.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-xl transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Leaves
                    </a>
                </div>

                {{-- Details Grid --}}
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Type --}}
                        <div class="bg-gray-50/80 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Type</p>
                            <p class="text-sm font-medium text-gray-900">{{ $leave->type }}</p>
                        </div>

                        {{-- Status --}}
                        <div class="bg-gray-50/80 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Status</p>
                            <div class="mt-0.5">
                                @if($leave->status == 'Approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Approved
                                    </span>
                                @elseif($leave->status == 'Supervisor Approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Supervisor Approved
                                    </span>
                                @elseif($leave->status == 'Rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Pending
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Start Date --}}
                        <div class="bg-gray-50/80 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Start Date</p>
                            <p class="text-sm font-medium text-gray-900">{{ $leave->start_date->format('M d, Y') }}</p>
                        </div>

                        {{-- End Date --}}
                        <div class="bg-gray-50/80 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">End Date</p>
                            <p class="text-sm font-medium text-gray-900">{{ $leave->end_date->format('M d, Y') }}</p>
                        </div>

                        {{-- Reason --}}
                        <div class="md:col-span-2 bg-gray-50/80 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Reason</p>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $leave->reason }}</p>
                        </div>

                        {{-- Document --}}
                        @if($leave->document_path)
                            <div class="md:col-span-2 bg-gray-50/80 rounded-xl p-4">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Document</p>
                                <a href="{{ route('leave.document.download', $leave->id) }}" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors duration-150">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download Document
                                </a>
                            </div>
                        @endif

                        {{-- Approved/Rejected By --}}
                        @if($leave->approved_by)
                            <div class="bg-gray-50/80 rounded-xl p-4">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Approved/Rejected By</p>
                                <p class="text-sm font-medium text-gray-900">{{ $leave->approver->name ?? 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50/80 rounded-xl p-4">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Action Date</p>
                                <p class="text-sm font-medium text-gray-900">{{ $leave->approved_at ? $leave->approved_at->format('M d, Y H:i') : 'N/A' }}</p>
                            </div>
                        @endif

                        {{-- Rejection Reason --}}
                        @if($leave->rejection_reason)
                            <div class="md:col-span-2 bg-red-50/60 rounded-xl p-4 ring-1 ring-inset ring-red-100">
                                <p class="text-xs font-semibold text-red-400 uppercase tracking-wider mb-1">Rejection Reason</p>
                                <p class="text-sm text-red-700 leading-relaxed">{{ $leave->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Approval Actions Card --}}
            @can('approve', $leave)
                @if(in_array($leave->status, ['Pending', 'Supervisor Approved']))
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                        {{-- Section Header --}}
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <h4 class="text-base font-semibold text-gray-900">Approval Actions</h4>
                            </div>
                        </div>

                        <div class="p-6">
                            @if($leave->status == 'Supervisor Approved')
                                <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 mb-5 flex items-center gap-3">
                                    <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-sm font-medium text-blue-700">This intern leave was approved by the supervisor and is awaiting your final approval.</p>
                                </div>
                            @endif
                            <div class="flex items-center gap-3">
                                <form method="POST" action="{{ route('leave.approve', $leave->id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all duration-200 hover:shadow-md">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Approve Leave
                                    </button>
                                </form>
                                <button onclick="showRejectModal()" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all duration-200 hover:shadow-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Reject Leave
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Reject Modal -->
                    <div id="rejectModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-modal="true">
                        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
                            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
                            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 z-10">
                                <div class="flex items-center gap-3 mb-5">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900">Reject Leave Application</h3>
                                </div>
                                <form method="POST" action="{{ route('leave.reject', $leave->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="mb-5">
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Rejection Reason:</label>
                                        <textarea name="reason" required class="block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-xl shadow-sm resize-none transition-colors duration-200" rows="3" placeholder="Please provide a reason for rejection..."></textarea>
                                    </div>
                                    <div class="flex justify-end gap-3">
                                        <button type="button" onclick="closeRejectModal()" class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all duration-200">
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
                @endif
            @endcan

        </div>
    </div>

    <script>
        function showRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
