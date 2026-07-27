<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\User;
use App\Services\PayrollCalculator;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $month = $request->get('month', now('Asia/Kuala_Lumpur')->format('Y-m'));
            $payrolls = Payroll::with('user')
                ->where('month', $month)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            $employees = User::whereHas('role', fn($q) => $q->whereNotIn('name', ['Super Admin']))->get();

            return view('payroll.admin-index', compact('payrolls', 'month', 'employees'));
        }

        $payrolls = Payroll::where('user_id', $user->id)
            ->orderByDesc('month')
            ->paginate(12);

        return view('payroll.index', compact('payrolls'));
    }

    public function show(Payroll $payroll)
    {
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->isAdmin();

        if (!$isAdmin && $payroll->user_id !== $user->id) {
            abort(403);
        }

        $payroll->load(['user', 'items']);

        return view('payroll.show', compact('payroll', 'isAdmin'));
    }

    public function generate(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'month' => 'required|string|size:7',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $employees = User::with('profile')->whereIn('id', $request->user_ids)->get();
        $generated = static::generatePayrollFor($employees, $request->month, $user->id);

        return redirect()->route('payroll.index', ['month' => $request->month])
            ->with('success', "$generated payroll(s) generated successfully.");
    }

    /**
     * Generate payroll rows (with an Overtime PayrollItem where applicable)
     * for the given employees + month, skipping any who already have one.
     *
     * Reusable by both this controller and the Filament "Generate payroll"
     * header action so the calculation logic lives in one place.
     *
     * @param  iterable<\App\Models\User>  $employees
     * @return int  Number of payrolls generated.
     */
    public static function generatePayrollFor(iterable $employees, string $month, ?int $createdBy = null): int
    {
        $calculator = new PayrollCalculator();
        $generated = 0;

        foreach ($employees as $employee) {
            if (!$employee) {
                continue;
            }

            $exists = Payroll::where('user_id', $employee->id)
                ->where('month', $month)
                ->exists();
            if ($exists) {
                continue;
            }

            $data = $calculator->calculate($employee, $month);
            $overtimeHours = $data['overtime_hours'];
            $overtimeAmount = $data['overtime_amount'];

            // Only the persisted payroll columns (strip calculator metadata).
            unset($data['overtime_hours'], $data['overtime_amount']);
            $data['created_by'] = $createdBy;

            $payroll = Payroll::create($data);

            // Record the auto-computed overtime allowance as a payroll item.
            if ($overtimeAmount > 0) {
                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'type' => 'Overtime',
                    'name' => 'Overtime (' . $overtimeHours . 'h @ ' . config('payroll.overtime_rate_multiplier') . 'x)',
                    'amount' => $overtimeAmount,
                    'notes' => 'Auto-generated from attendance overtime for ' . $month,
                ]);
            }

            // Recompute totals so the item-based allowances stay in sync.
            $payroll->calculateTotals();
            $generated++;
        }

        return $generated;
    }

    public function addItem(Request $request, Payroll $payroll)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'type' => 'required|in:Allowance,Deduction,Bonus,Reimbursement,Overtime',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        PayrollItem::create([
            'payroll_id' => $payroll->id,
            'type' => $request->type,
            'name' => $request->name,
            'amount' => $request->amount,
            'notes' => $request->notes,
        ]);

        $payroll->calculateTotals();

        return redirect()->route('payroll.show', $payroll)->with('success', 'Payroll item added.');
    }

    public function removeItem(PayrollItem $item)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $payroll = $item->payroll;
        $item->delete();
        $payroll->calculateTotals();

        return redirect()->route('payroll.show', $payroll)->with('success', 'Item removed.');
    }

    public function approve(Payroll $payroll)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $payroll->update([
            'status' => 'Approved',
            'approved_by' => $user->id,
        ]);

        return redirect()->route('payroll.show', $payroll)->with('success', 'Payroll approved.');
    }

    public function markPaid(Payroll $payroll)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $payroll->update([
            'status' => 'Paid',
            'payment_date' => now('Asia/Kuala_Lumpur'),
        ]);

        return redirect()->route('payroll.show', $payroll)->with('success', 'Payroll marked as paid.');
    }

    public function payslip(Payroll $payroll)
    {
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->isAdmin();

        if (!$isAdmin && $payroll->user_id !== $user->id) {
            abort(403);
        }

        $payroll->load(['user.profile', 'user.department', 'items']);

        return view('payroll.payslip', compact('payroll'));
    }

    public function downloadPayslip(Payroll $payroll)
    {
        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->isAdmin();

        if (!$isAdmin && $payroll->user_id !== $user->id) {
            abort(403);
        }

        $payroll->load(['user.profile', 'user.department', 'items']);

        $rm = fn ($amount) => 'RM ' . number_format((float) $amount, 2);
        $period = \Carbon\Carbon::parse($payroll->month . '-01')->format('F Y');

        $totalDeductions = $payroll->total_deductions
            + $payroll->epf_employee
            + $payroll->socso_employee
            + $payroll->eis_employee
            + $payroll->pcb_tax;

        $pdf = new \FPDF();
        $pdf->AddPage();

        // Header
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, config('app.name', 'HR Management'), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Payslip for ' . $period, 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, 'Generated on: ' . now('Asia/Kuala_Lumpur')->format('Y-m-d H:i'), 0, 1, 'C');
        $pdf->Ln(6);

        // Employee details
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, 'Employee Details', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(45, 6, 'Name:', 0);
        $pdf->Cell(0, 6, $payroll->user->name, 0, 1);
        $pdf->Cell(45, 6, 'Employee ID:', 0);
        $pdf->Cell(0, 6, 'EMP-' . str_pad($payroll->user->id, 4, '0', STR_PAD_LEFT), 0, 1);
        $pdf->Cell(45, 6, 'Department:', 0);
        $pdf->Cell(0, 6, $payroll->user->department->name ?? 'N/A', 0, 1);
        $pdf->Cell(45, 6, 'Pay Period:', 0);
        $pdf->Cell(0, 6, $period, 0, 1);
        $pdf->Cell(45, 6, 'Status:', 0);
        $pdf->Cell(0, 6, $payroll->status ?? 'N/A', 0, 1);
        $pdf->Ln(4);

        // Earnings
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(120, 8, 'Earnings', 1);
        $pdf->Cell(60, 8, 'Amount', 1, 1, 'R');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(120, 6, 'Basic Salary', 1);
        $pdf->Cell(60, 6, $rm($payroll->basic_salary), 1, 1, 'R');
        foreach ($payroll->items->whereIn('type', ['Allowance', 'Bonus', 'Reimbursement', 'Overtime']) as $item) {
            $pdf->Cell(120, 6, substr($item->name . ' (' . $item->type . ')', 0, 60), 1);
            $pdf->Cell(60, 6, $rm($item->amount), 1, 1, 'R');
        }
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(120, 6, 'Total Allowances', 1);
        $pdf->Cell(60, 6, $rm($payroll->total_allowances), 1, 1, 'R');
        $pdf->Cell(120, 6, 'Gross Salary', 1);
        $pdf->Cell(60, 6, $rm($payroll->gross_salary), 1, 1, 'R');
        $pdf->Ln(4);

        // Deductions (employee statutory)
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(120, 8, 'Deductions', 1);
        $pdf->Cell(60, 8, 'Amount', 1, 1, 'R');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(120, 6, 'EPF (Employee 11%)', 1);
        $pdf->Cell(60, 6, $rm($payroll->epf_employee), 1, 1, 'R');
        $pdf->Cell(120, 6, 'SOCSO (Employee)', 1);
        $pdf->Cell(60, 6, $rm($payroll->socso_employee), 1, 1, 'R');
        $pdf->Cell(120, 6, 'EIS (Employee)', 1);
        $pdf->Cell(60, 6, $rm($payroll->eis_employee), 1, 1, 'R');
        $pdf->Cell(120, 6, 'PCB (Income Tax)', 1);
        $pdf->Cell(60, 6, $rm($payroll->pcb_tax), 1, 1, 'R');
        foreach ($payroll->items->where('type', 'Deduction') as $item) {
            $pdf->Cell(120, 6, substr($item->name, 0, 60), 1);
            $pdf->Cell(60, 6, $rm($item->amount), 1, 1, 'R');
        }
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(120, 6, 'Total Deductions', 1);
        $pdf->Cell(60, 6, $rm($totalDeductions), 1, 1, 'R');
        $pdf->Ln(4);

        // Net salary
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(120, 10, 'NET SALARY', 1);
        $pdf->Cell(60, 10, $rm($payroll->net_salary), 1, 1, 'R');
        $pdf->Ln(4);

        // Employer contributions
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(120, 8, 'Employer Contributions', 1);
        $pdf->Cell(60, 8, 'Amount', 1, 1, 'R');
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(120, 6, 'EPF (Employer 12%)', 1);
        $pdf->Cell(60, 6, $rm($payroll->epf_employer), 1, 1, 'R');
        $pdf->Cell(120, 6, 'SOCSO (Employer)', 1);
        $pdf->Cell(60, 6, $rm($payroll->socso_employer), 1, 1, 'R');
        $pdf->Cell(120, 6, 'EIS (Employer)', 1);
        $pdf->Cell(60, 6, $rm($payroll->eis_employer), 1, 1, 'R');

        return $pdf->Output('D', "payslip-{$payroll->month}.pdf");
    }
}
