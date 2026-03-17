<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\User;
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

        $month = $request->month;
        $generated = 0;

        foreach ($request->user_ids as $userId) {
            $employee = User::with('profile')->find($userId);
            if (!$employee) continue;

            $existing = Payroll::where('user_id', $userId)->where('month', $month)->first();
            if ($existing) continue;

            $basicSalary = $employee->profile?->basic_salary ?? 0;

            $payroll = Payroll::create([
                'user_id' => $userId,
                'month' => $month,
                'basic_salary' => $basicSalary,
                'created_by' => $user->id,
            ]);

            $payroll->calculateTotals();
            $generated++;
        }

        return redirect()->route('payroll.index', ['month' => $month])
            ->with('success', "$generated payroll(s) generated successfully.");
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
}
