<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payslip - {{ $payroll->user->name }} - {{ \Carbon\Carbon::parse($payroll->month . '-01')->format('F Y') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #333; font-size: 13px; }
        .payslip { max-width: 800px; margin: 20px auto; padding: 40px; border: 1px solid #ddd; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #4f46e5; padding-bottom: 20px; }
        .header h1 { font-size: 22px; color: #4f46e5; }
        .header h2 { font-size: 14px; color: #666; margin-top: 5px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .info-box { background: #f9fafb; padding: 15px; border-radius: 8px; }
        .info-box h3 { font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 8px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .info-row .label { color: #6b7280; }
        .info-row .value { font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th { background: #f3f4f6; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        table td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
        .amount { text-align: right; font-weight: 600; }
        .total-row { background: #f0fdf4; }
        .total-row td { font-weight: 700; font-size: 15px; color: #15803d; padding: 12px; }
        .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 11px; }
        @media print { body { margin: 0; } .payslip { border: none; margin: 0; padding: 20px; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; padding: 15px; background: #f3f4f6;">
        <button onclick="window.print()" style="padding: 8px 20px; background: #4f46e5; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px;">Print Payslip</button>
        <a href="{{ route('payroll.show', $payroll) }}" style="margin-left: 10px; color: #6b7280; text-decoration: none;">Back</a>
    </div>

    <div class="payslip">
        <div class="header">
            <h1>{{ config('app.name', 'HR Management') }}</h1>
            <h2>PAYSLIP FOR {{ strtoupper(\Carbon\Carbon::parse($payroll->month . '-01')->format('F Y')) }}</h2>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h3>Employee Details</h3>
                <div class="info-row"><span class="label">Name:</span><span class="value">{{ $payroll->user->name }}</span></div>
                <div class="info-row"><span class="label">Employee ID:</span><span class="value">EMP-{{ str_pad($payroll->user->id, 4, '0', STR_PAD_LEFT) }}</span></div>
                <div class="info-row"><span class="label">Department:</span><span class="value">{{ $payroll->user->department->name ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="label">Position:</span><span class="value">{{ $payroll->user->profile->job_title ?? 'N/A' }}</span></div>
            </div>
            <div class="info-box">
                <h3>Payment Details</h3>
                <div class="info-row"><span class="label">Pay Period:</span><span class="value">{{ \Carbon\Carbon::parse($payroll->month . '-01')->format('M Y') }}</span></div>
                <div class="info-row"><span class="label">Payment Date:</span><span class="value">{{ $payroll->payment_date?->format('d/m/Y') ?? 'Pending' }}</span></div>
                <div class="info-row"><span class="label">Status:</span><span class="value">{{ $payroll->status }}</span></div>
                <div class="info-row"><span class="label">Bank:</span><span class="value">{{ $payroll->user->profile->bank_name ?? 'N/A' }}</span></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Earnings</th>
                    <th class="amount">Amount (RM)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Basic Salary</td>
                    <td class="amount">{{ number_format($payroll->basic_salary, 2) }}</td>
                </tr>
                @foreach($payroll->items->whereIn('type', ['Allowance', 'Bonus', 'Reimbursement', 'Overtime']) as $item)
                    <tr>
                        <td>{{ $item->name }} ({{ $item->type }})</td>
                        <td class="amount">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr style="background: #f9fafb; font-weight: 600;">
                    <td>Gross Salary</td>
                    <td class="amount">{{ number_format($payroll->gross_salary, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Deductions</th>
                    <th class="amount">Amount (RM)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>EPF (Employee 11%)</td><td class="amount">{{ number_format($payroll->epf_employee, 2) }}</td></tr>
                <tr><td>SOCSO (Employee)</td><td class="amount">{{ number_format($payroll->socso_employee, 2) }}</td></tr>
                <tr><td>EIS (Employee)</td><td class="amount">{{ number_format($payroll->eis_employee, 2) }}</td></tr>
                @if($payroll->pcb_tax > 0)
                    <tr><td>PCB (Income Tax)</td><td class="amount">{{ number_format($payroll->pcb_tax, 2) }}</td></tr>
                @endif
                @foreach($payroll->items->where('type', 'Deduction') as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td class="amount">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr style="background: #fef2f2; font-weight: 600;">
                    <td>Total Deductions</td>
                    <td class="amount">{{ number_format($payroll->total_deductions + $payroll->epf_employee + $payroll->socso_employee + $payroll->eis_employee + $payroll->pcb_tax, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <table>
            <tbody>
                <tr class="total-row">
                    <td>NET SALARY</td>
                    <td class="amount" style="font-size: 18px;">RM {{ number_format($payroll->net_salary, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <table style="margin-top: 10px;">
            <thead>
                <tr>
                    <th>Employer Contributions</th>
                    <th class="amount">Amount (RM)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>EPF (Employer 12%)</td><td class="amount">{{ number_format($payroll->epf_employer, 2) }}</td></tr>
                <tr><td>SOCSO (Employer)</td><td class="amount">{{ number_format($payroll->socso_employer, 2) }}</td></tr>
                <tr><td>EIS (Employer)</td><td class="amount">{{ number_format($payroll->eis_employer, 2) }}</td></tr>
            </tbody>
        </table>

        <div class="footer">
            <p>This is a computer-generated payslip. No signature is required.</p>
            <p>Generated on {{ now('Asia/Kuala_Lumpur')->format('d/m/Y H:i') }} | {{ config('app.name', 'HR Management') }}</p>
        </div>
    </div>
</body>
</html>
