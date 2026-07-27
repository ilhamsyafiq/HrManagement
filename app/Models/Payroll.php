<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'user_id', 'month', 'basic_salary', 'total_allowances', 'total_deductions',
        'gross_salary', 'net_salary', 'epf_employee', 'epf_employer',
        'socso_employee', 'socso_employer', 'eis_employee', 'eis_employer',
        'pcb_tax', 'status', 'payment_date', 'created_by', 'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'basic_salary' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'gross_salary' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Recompute totals and Malaysian statutory contributions for this payroll.
     *
     * Earnings come from PayrollItems (Allowance/Bonus/Reimbursement/Overtime)
     * on top of the basic salary. Statutory rates are pulled from
     * config/payroll.php (see that file for the approximation caveats) so the
     * formulas here stay in lock-step with App\Services\PayrollCalculator.
     */
    public function calculateTotals()
    {
        $config = config('payroll');

        // Allowances / deductions sourced from manual payroll items.
        $itemAllowances = (float) $this->items()
            ->whereIn('type', ['Allowance', 'Bonus', 'Reimbursement', 'Overtime'])
            ->sum('amount');
        $itemDeductions = (float) $this->items()
            ->where('type', 'Deduction')
            ->sum('amount');

        $this->total_allowances = round($itemAllowances, 2);
        $this->gross_salary = round((float) $this->basic_salary + $itemAllowances, 2);

        // EPF: employer rate depends on the wage threshold.
        $epfEmployerRate = (float) $this->basic_salary <= (float) $config['epf']['employer_wage_threshold']
            ? (float) $config['epf']['employer_rate_low']
            : (float) $config['epf']['employer_rate_high'];

        $this->epf_employee = round($this->gross_salary * (float) $config['epf']['employee_rate'], 2);
        $this->epf_employer = round($this->gross_salary * $epfEmployerRate, 2);

        // SOCSO (approximation with ceiling caps).
        $this->socso_employee = min(
            round($this->gross_salary * (float) $config['socso']['employee_rate'], 2),
            (float) $config['socso']['employee_max']
        );
        $this->socso_employer = min(
            round($this->gross_salary * (float) $config['socso']['employer_rate'], 2),
            (float) $config['socso']['employer_max']
        );

        // EIS (approximation with ceiling caps).
        $this->eis_employee = min(
            round($this->gross_salary * (float) $config['eis']['employee_rate'], 2),
            (float) $config['eis']['employee_max']
        );
        $this->eis_employer = min(
            round($this->gross_salary * (float) $config['eis']['employer_rate'], 2),
            (float) $config['eis']['employer_max']
        );

        // PCB: configurable flat estimate (default 0) unless already set.
        if ($this->pcb_tax === null) {
            $pcb = (float) $config['pcb']['flat'];
            if ($pcb <= 0 && (float) $config['pcb']['rate'] > 0) {
                $pcb = round($this->gross_salary * (float) $config['pcb']['rate'], 2);
            }
            $this->pcb_tax = $pcb;
        }

        // total_deductions stores ONLY the manual deduction items; the employee
        // statutory contributions (EPF/SOCSO/EIS/PCB) are tracked in their own
        // columns and subtracted separately for net pay. This matches how the
        // payslip PDF computes its "Total Deductions" line.
        $this->total_deductions = round($itemDeductions, 2);

        $this->net_salary = round(
            $this->gross_salary
            - $this->total_deductions
            - $this->epf_employee
            - $this->socso_employee
            - $this->eis_employee
            - (float) $this->pcb_tax,
            2
        );

        $this->save();
    }
}
