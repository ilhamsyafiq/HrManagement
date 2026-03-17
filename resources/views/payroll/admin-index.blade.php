<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Payroll Management') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-green-700">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Month Selector & Generate --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <form action="{{ route('payroll.index') }}" method="GET" class="flex items-center gap-3">
                    <input type="month" name="month" value="{{ $month }}" class="rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">View</button>
                </form>

                <button onclick="document.getElementById('generateModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 px-5 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Generate Payroll
                </button>
            </div>

            {{-- Payroll List --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-base font-semibold text-gray-900">Payroll for {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Employee</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Basic</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Gross</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Net</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($payrolls as $payroll)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $payroll->user->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">RM {{ number_format($payroll->basic_salary, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">RM {{ number_format($payroll->gross_salary, 2) }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-green-700">RM {{ number_format($payroll->net_salary, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                            @if($payroll->status === 'Paid') bg-green-50 text-green-700
                                            @elseif($payroll->status === 'Approved') bg-blue-50 text-blue-700
                                            @else bg-amber-50 text-amber-700
                                            @endif
                                        ">{{ $payroll->status }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm flex gap-2">
                                        <a href="{{ route('payroll.show', $payroll) }}" class="text-blue-600 hover:text-blue-800">View</a>
                                        @if($payroll->status === 'Draft')
                                            <form action="{{ route('payroll.approve', $payroll) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-green-600 hover:text-green-800">Approve</button>
                                            </form>
                                        @elseif($payroll->status === 'Approved')
                                            <form action="{{ route('payroll.mark-paid', $payroll) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-green-600 hover:text-green-800">Mark Paid</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400">No payroll records for this month.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($payrolls->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">{{ $payrolls->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Generate Payroll Modal --}}
    <div id="generateModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="document.getElementById('generateModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 z-10">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Generate Payroll</h3>
                <form action="{{ route('payroll.generate') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                        <input type="month" name="month" value="{{ $month }}" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Employees</label>
                        <div class="max-h-48 overflow-y-auto border rounded-lg p-3 space-y-2">
                            <label class="flex items-center gap-2 text-sm text-gray-700 border-b pb-2 mb-2">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" onclick="document.querySelectorAll('.emp-checkbox').forEach(c => c.checked = this.checked)">
                                <span class="font-medium">Select All</span>
                            </label>
                            @foreach($employees as $emp)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="user_ids[]" value="{{ $emp->id }}" class="emp-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    {{ $emp->name }} <span class="text-gray-400">({{ $emp->role->name }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="document.getElementById('generateModal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Generate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
