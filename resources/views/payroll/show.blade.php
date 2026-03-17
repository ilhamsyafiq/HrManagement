<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Payroll - {{ $payroll->user->name }} ({{ \Carbon\Carbon::parse($payroll->month . '-01')->format('F Y') }})
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-green-700">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Summary --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-xs text-gray-500">Basic Salary</p>
                    <p class="text-lg font-bold text-gray-800">RM {{ number_format($payroll->basic_salary, 2) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-xs text-gray-500">Gross Salary</p>
                    <p class="text-lg font-bold text-gray-800">RM {{ number_format($payroll->gross_salary, 2) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
                    <p class="text-xs text-gray-500">Total Deductions</p>
                    <p class="text-lg font-bold text-red-600">RM {{ number_format($payroll->total_deductions + $payroll->epf_employee + $payroll->socso_employee + $payroll->eis_employee + $payroll->pcb_tax, 2) }}</p>
                </div>
                <div class="bg-green-50 rounded-xl border border-green-200 p-4 text-center">
                    <p class="text-xs text-green-600">Net Salary</p>
                    <p class="text-lg font-bold text-green-700">RM {{ number_format($payroll->net_salary, 2) }}</p>
                </div>
            </div>

            {{-- Statutory Deductions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-base font-semibold text-gray-900">Statutory Deductions</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">EPF (Employee 11%)</span><span class="font-medium">RM {{ number_format($payroll->epf_employee, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">EPF (Employer 12%)</span><span class="font-medium text-gray-400">RM {{ number_format($payroll->epf_employer, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">SOCSO (Employee)</span><span class="font-medium">RM {{ number_format($payroll->socso_employee, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">SOCSO (Employer)</span><span class="font-medium text-gray-400">RM {{ number_format($payroll->socso_employer, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">EIS (Employee)</span><span class="font-medium">RM {{ number_format($payroll->eis_employee, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">EIS (Employer)</span><span class="font-medium text-gray-400">RM {{ number_format($payroll->eis_employer, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">PCB (Income Tax)</span><span class="font-medium">RM {{ number_format($payroll->pcb_tax, 2) }}</span></div>
                </div>
            </div>

            {{-- Allowances & Deductions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Allowances & Deductions</h3>
                    @if($isAdmin && $payroll->status === 'Draft')
                        <button onclick="document.getElementById('addItemModal').classList.remove('hidden')" class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium py-1.5 px-3 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Item
                        </button>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                                @if($isAdmin && $payroll->status === 'Draft')
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($payroll->items as $item)
                                <tr>
                                    <td class="px-6 py-3 text-sm">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            @if(in_array($item->type, ['Allowance', 'Bonus', 'Reimbursement', 'Overtime'])) bg-green-50 text-green-700
                                            @else bg-red-50 text-red-700
                                            @endif
                                        ">{{ $item->type }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-700">{{ $item->name }}</td>
                                    <td class="px-6 py-3 text-sm text-right font-medium {{ in_array($item->type, ['Allowance', 'Bonus', 'Reimbursement', 'Overtime']) ? 'text-green-700' : 'text-red-600' }}">
                                        {{ in_array($item->type, ['Allowance', 'Bonus', 'Reimbursement', 'Overtime']) ? '+' : '-' }} RM {{ number_format($item->amount, 2) }}
                                    </td>
                                    @if($isAdmin && $payroll->status === 'Draft')
                                        <td class="px-6 py-3 text-right">
                                            <form action="{{ route('payroll.item.remove', $item) }}" method="POST" onsubmit="return confirm('Remove this item?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-sm text-gray-400">No additional items.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-between items-center">
                <a href="{{ route('payroll.index') }}" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back to Payroll</a>
                <div class="flex gap-3">
                    @if($payroll->status === 'Paid' || $payroll->status === 'Approved')
                        <a href="{{ route('payroll.payslip', $payroll) }}" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition">View Payslip</a>
                    @endif
                    @if($isAdmin && $payroll->status === 'Draft')
                        <form action="{{ route('payroll.approve', $payroll) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition">Approve</button>
                        </form>
                    @endif
                    @if($isAdmin && $payroll->status === 'Approved')
                        <form action="{{ route('payroll.mark-paid', $payroll) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition">Mark as Paid</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Add Item Modal --}}
    @if($isAdmin && $payroll->status === 'Draft')
    <div id="addItemModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="document.getElementById('addItemModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Payroll Item</h3>
                <form action="{{ route('payroll.item.add', $payroll) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="Allowance">Allowance</option>
                            <option value="Deduction">Deduction</option>
                            <option value="Bonus">Bonus</option>
                            <option value="Reimbursement">Reimbursement</option>
                            <option value="Overtime">Overtime</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <input type="text" name="name" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="e.g. Transport Allowance">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount (RM)</label>
                        <input type="number" name="amount" step="0.01" min="0.01" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('addItemModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</x-app-layout>
