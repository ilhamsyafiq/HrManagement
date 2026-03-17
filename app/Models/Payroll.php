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

    public function calculateTotals()
    {
        $this->total_allowances = $this->items()->whereIn('type', ['Allowance', 'Bonus', 'Reimbursement', 'Overtime'])->sum('amount');
        $this->total_deductions = $this->items()->where('type', 'Deduction')->sum('amount');
        $this->gross_salary = $this->basic_salary + $this->total_allowances;

        // Malaysian statutory deductions
        $this->epf_employee = round($this->gross_salary * 0.11, 2); // 11%
        $this->epf_employer = round($this->gross_salary * 0.12, 2); // 12%
        $this->socso_employee = min(round($this->gross_salary * 0.005, 2), 86.65);
        $this->socso_employer = min(round($this->gross_salary * 0.0175, 2), 303.29);
        $this->eis_employee = min(round($this->gross_salary * 0.002, 2), 99.40);
        $this->eis_employer = min(round($this->gross_salary * 0.002, 2), 99.40);

        $this->net_salary = $this->gross_salary - $this->total_deductions
            - $this->epf_employee - $this->socso_employee - $this->eis_employee - $this->pcb_tax;

        $this->save();
    }
}
