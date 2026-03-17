<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('My Payslips') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-base font-semibold text-gray-900">Payroll History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50/80">
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Month</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Basic Salary</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Gross</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Deductions</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Net Salary</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($payrolls as $payroll)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($payroll->month . '-01')->format('F Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">RM {{ number_format($payroll->basic_salary, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">RM {{ number_format($payroll->gross_salary, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-red-600">RM {{ number_format($payroll->total_deductions + $payroll->epf_employee + $payroll->socso_employee + $payroll->eis_employee + $payroll->pcb_tax, 2) }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-green-700">RM {{ number_format($payroll->net_salary, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full
                                            @if($payroll->status === 'Paid') bg-green-50 text-green-700
                                            @elseif($payroll->status === 'Approved') bg-blue-50 text-blue-700
                                            @else bg-amber-50 text-amber-700
                                            @endif
                                        ">{{ $payroll->status }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="{{ route('payroll.show', $payroll) }}" class="text-blue-600 hover:text-blue-800 mr-2">View</a>
                                        @if($payroll->status === 'Paid')
                                            <a href="{{ route('payroll.payslip', $payroll) }}" class="text-indigo-600 hover:text-indigo-800">Payslip</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400">No payroll records found.</td>
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
</x-app-layout>
