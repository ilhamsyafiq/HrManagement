<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Claim Details') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
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

            {{-- Claim Info Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- Section Header --}}
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">Claim Information</h3>
                    </div>
                    <a href="{{ route('claims.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-xl transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Claims
                    </a>
                </div>

                {{-- Details Grid --}}
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Title --}}
                        <div class="bg-gray-50/80 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Title</p>
                            <p class="text-sm font-medium text-gray-900">{{ $claim->title }}</p>
                        </div>

                        {{-- Status --}}
                        <div class="bg-gray-50/80 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Status</p>
                            <div class="mt-0.5">
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
                            </div>
                        </div>

                        {{-- Total Amount --}}
                        <div class="bg-gray-50/80 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Total Amount</p>
                            <p class="text-sm font-medium text-gray-900">RM {{ number_format($claim->total_amount, 2) }}</p>
                        </div>

                        {{-- Submitted By --}}
                        <div class="bg-gray-50/80 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Submitted By</p>
                            <p class="text-sm font-medium text-gray-900">{{ $claim->user->name }}</p>
                        </div>

                        {{-- Created Date --}}
                        <div class="bg-gray-50/80 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Created Date</p>
                            <p class="text-sm font-medium text-gray-900">{{ $claim->created_at->format('M d, Y H:i') }}</p>
                        </div>

                        {{-- Last Updated --}}
                        <div class="bg-gray-50/80 rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Last Updated</p>
                            <p class="text-sm font-medium text-gray-900">{{ $claim->updated_at->format('M d, Y H:i') }}</p>
                        </div>

                        {{-- Description --}}
                        @if($claim->description)
                            <div class="md:col-span-2 bg-gray-50/80 rounded-xl p-4">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Description</p>
                                <p class="text-sm text-gray-700 leading-relaxed">{{ $claim->description }}</p>
                            </div>
                        @endif

                        {{-- Approved/Rejected By --}}
                        @if($claim->approved_by)
                            <div class="bg-gray-50/80 rounded-xl p-4">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Reviewed By</p>
                                <p class="text-sm font-medium text-gray-900">{{ $claim->approver->name ?? 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50/80 rounded-xl p-4">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Review Date</p>
                                <p class="text-sm font-medium text-gray-900">{{ $claim->approved_at ? $claim->approved_at->format('M d, Y H:i') : 'N/A' }}</p>
                            </div>
                        @endif

                        {{-- Rejection Reason --}}
                        @if($claim->rejection_reason)
                            <div class="md:col-span-2 bg-red-50/60 rounded-xl p-4 ring-1 ring-inset ring-red-100">
                                <p class="text-xs font-semibold text-red-400 uppercase tracking-wider mb-1">Rejection Reason</p>
                                <p class="text-sm text-red-700 leading-relaxed">{{ $claim->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Claim Items Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- Section Header --}}
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">Expense Items ({{ $claim->items->count() }})</h3>
                    </div>
                    @if($claim->status == 'Draft' && $claim->user_id == Auth::id())
                        <button type="button" onclick="showAddItemModal()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-xl transition-all duration-200 hover:shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Item
                        </button>
                    @endif
                </div>

                {{-- Items Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount (RM)</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Receipt</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</th>
                                @if($claim->status == 'Draft' && $claim->user_id == Auth::id())
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($claim->items as $item)
                                <tr class="hover:bg-gray-50/60 transition-colors duration-150">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-md bg-blue-50 text-blue-700">
                                            {{ $item->category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($item->amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->expense_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($item->receipt_path)
                                            @php
                                                $ext = strtolower(pathinfo($item->receipt_path, PATHINFO_EXTENSION));
                                            @endphp
                                            @if(in_array($ext, ['jpg', 'jpeg', 'png']))
                                                <a href="{{ Storage::url($item->receipt_path) }}" target="_blank" class="group inline-flex items-center gap-2">
                                                    <img src="{{ Storage::url($item->receipt_path) }}" alt="Receipt" class="w-10 h-10 object-cover rounded-lg border border-gray-200 group-hover:border-blue-400 transition-colors duration-150">
                                                    <span class="text-blue-600 hover:text-blue-800 font-medium text-xs transition-colors duration-150">View</span>
                                                </a>
                                            @else
                                                <a href="{{ Storage::url($item->receipt_path) }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-medium transition-colors duration-150">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    Download
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-gray-400 text-xs">No receipt</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $item->notes ?? '-' }}</td>
                                    @if($claim->status == 'Draft' && $claim->user_id == Auth::id())
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <form method="POST" action="{{ route('claims.item.remove', $item) }}" class="inline" onsubmit="return confirm('Remove this item?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium transition-colors duration-150">Remove</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $claim->status == 'Draft' && $claim->user_id == Auth::id() ? 7 : 6 }}" class="px-6 py-8 text-center text-sm text-gray-400">
                                        No items in this claim.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Total --}}
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-end gap-3">
                    <span class="text-sm font-semibold text-gray-700">Total:</span>
                    <span class="text-lg font-bold text-gray-900">RM {{ number_format($claim->total_amount, 2) }}</span>
                </div>
            </div>

            {{-- Submit Button (for owner, if Draft) --}}
            @if($claim->status == 'Draft' && $claim->user_id == Auth::id())
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">Ready to submit?</h4>
                            <p class="text-sm text-gray-500 mt-0.5">Once submitted, this claim will be sent for approval and cannot be edited.</p>
                        </div>
                        <form method="POST" action="{{ route('claims.submit', $claim) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all duration-200 hover:shadow-md" onclick="return confirm('Submit this claim for approval? It cannot be edited after submission.')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Submit for Approval
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Approval Actions Card (for admin, if Pending) --}}
            @if(($claim->status == 'Pending') && (Auth::user()->isSuperAdmin() || Auth::user()->isAdmin()))
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
                        <div class="flex items-center gap-3">
                            <form method="POST" action="{{ route('claims.approve', $claim) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all duration-200 hover:shadow-md" onclick="return confirm('Approve this claim?')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Approve Claim
                                </button>
                            </form>
                            <button type="button" onclick="showRejectModal()" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all duration-200 hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Reject Claim
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Mark as Paid (for admin, if Approved) --}}
            @if(($claim->status == 'Approved') && (Auth::user()->isSuperAdmin() || Auth::user()->isAdmin()))
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">Payment</h4>
                            <p class="text-sm text-gray-500 mt-0.5">Mark this approved claim as paid after processing the payment.</p>
                        </div>
                        <form method="POST" action="{{ route('claims.mark-paid', $claim) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all duration-200 hover:shadow-md" onclick="return confirm('Mark this claim as paid?')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                </svg>
                                Mark as Paid
                            </button>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- Add Item Modal (for Draft claims) --}}
    @if($claim->status == 'Draft' && $claim->user_id == Auth::id())
        <div id="addItemModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeAddItemModal()"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 z-10">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Add Expense Item</h3>
                    </div>
                    <form method="POST" action="{{ route('claims.item.add', $claim) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                                <input type="text" name="description" required class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm" placeholder="e.g. Grab ride to client office">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (RM) *</label>
                                    <input type="number" name="amount" step="0.01" min="0.01" required class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm" placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Expense Date *</label>
                                    <input type="date" name="expense_date" required class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                <select name="category" required class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm">
                                    <option value="">Select Category</option>
                                    <option value="Transport">Transport</option>
                                    <option value="Meal">Meal</option>
                                    <option value="Accommodation">Accommodation</option>
                                    <option value="Office Supplies">Office Supplies</option>
                                    <option value="Medical">Medical</option>
                                    <option value="Training">Training</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Receipt (optional)</label>
                                <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="text-xs text-gray-400 mt-1">PDF, JPG, JPEG, PNG up to 2MB</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                                <input type="text" name="notes" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm" placeholder="Any additional notes">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" onclick="closeAddItemModal()" class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all duration-200">
                                Cancel
                            </button>
                            <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition-all duration-200 hover:shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Reject Modal --}}
    @if(($claim->status == 'Pending') && (Auth::user()->isSuperAdmin() || Auth::user()->isAdmin()))
        <div id="rejectModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" onclick="closeRejectModal()"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 z-10">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Reject Claim</h3>
                    </div>
                    <form method="POST" action="{{ route('claims.reject', $claim) }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Rejection Reason:</label>
                            <textarea name="rejection_reason" required class="block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-xl shadow-sm resize-none transition-colors duration-200" rows="3" placeholder="Please provide a reason for rejection..."></textarea>
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

    <script>
        function showAddItemModal() {
            document.getElementById('addItemModal').classList.remove('hidden');
        }

        function closeAddItemModal() {
            document.getElementById('addItemModal').classList.add('hidden');
        }

        function showRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
