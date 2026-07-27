<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

/**
 * PayrollCalculator
 *
 * Computes a single employee's payroll for a given month using the
 * configurable Malaysian statutory defaults in config/payroll.php.
 *
 * All statutory figures here are APPROXIMATIONS driven by config — see
 * config/payroll.php for the caveats (EPF/SOCSO/EIS wage tables, complex PCB).
 */
class PayrollCalculator
{
    /**
     * Calculate payroll figures for a user in a given month.
     *
     * @param  User    $user   The employee.
     * @param  string  $month  Period in 'YYYY-MM' format.
     * @return array<string, mixed>  Keys match the `payrolls` table columns,
     *         plus 'overtime_hours' and 'overtime_amount' metadata for callers
     *         that want to persist a PayrollItem.
     */
    public function calculate(User $user, string $month): array
    {
        $config = config('payroll');

        $basicSalary = (float) ($user->profile?->basic_salary ?? 0);

        // --- Overtime allowance -------------------------------------------
        $overtimeHours  = $this->totalOvertimeHours($user, $month);
        $hourlyRate     = $this->hourlyRate($basicSalary, $config);
        $overtimeAmount = round(
            $overtimeHours * $hourlyRate * (float) $config['overtime_rate_multiplier'],
            2
        );

        // --- Earnings ------------------------------------------------------
        // The OT allowance is the only auto-computed allowance; other
        // allowances/deductions are handled via PayrollItems downstream.
        $totalAllowances = $overtimeAmount;
        $grossSalary     = round($basicSalary + $totalAllowances, 2);

        // --- EPF -----------------------------------------------------------
        $epfEmployerRate = $basicSalary <= (float) $config['epf']['employer_wage_threshold']
            ? (float) $config['epf']['employer_rate_low']
            : (float) $config['epf']['employer_rate_high'];

        $epfEmployee = round($grossSalary * (float) $config['epf']['employee_rate'], 2);
        $epfEmployer = round($grossSalary * $epfEmployerRate, 2);

        // --- SOCSO (approximation with ceiling caps) -----------------------
        $socsoEmployee = min(
            round($grossSalary * (float) $config['socso']['employee_rate'], 2),
            (float) $config['socso']['employee_max']
        );
        $socsoEmployer = min(
            round($grossSalary * (float) $config['socso']['employer_rate'], 2),
            (float) $config['socso']['employer_max']
        );

        // --- EIS (approximation with ceiling caps) -------------------------
        $eisEmployee = min(
            round($grossSalary * (float) $config['eis']['employee_rate'], 2),
            (float) $config['eis']['employee_max']
        );
        $eisEmployer = min(
            round($grossSalary * (float) $config['eis']['employer_rate'], 2),
            (float) $config['eis']['employer_max']
        );

        // --- PCB (configurable flat estimate; complex in reality) ----------
        $pcbTax = (float) $config['pcb']['flat'];
        if ($pcbTax <= 0 && (float) $config['pcb']['rate'] > 0) {
            $pcbTax = round($grossSalary * (float) $config['pcb']['rate'], 2);
        }

        // --- Deductions & net ---------------------------------------------
        // total_deductions stores ONLY manual "Deduction" PayrollItems (none at
        // generation time), matching Payroll::calculateTotals() and the payslip
        // PDF. Statutory contributions live in their own columns and are
        // subtracted separately when computing net pay.
        $totalDeductions = 0.0;
        $netSalary       = round(
            $grossSalary - $totalDeductions - $epfEmployee - $socsoEmployee - $eisEmployee - $pcbTax,
            2
        );

        return [
            'user_id'          => $user->id,
            'month'            => $month,
            'basic_salary'     => round($basicSalary, 2),
            'total_allowances' => round($totalAllowances, 2),
            'gross_salary'     => $grossSalary,
            'epf_employee'     => $epfEmployee,
            'epf_employer'     => $epfEmployer,
            'socso_employee'   => $socsoEmployee,
            'socso_employer'   => $socsoEmployer,
            'eis_employee'     => $eisEmployee,
            'eis_employer'     => $eisEmployer,
            'pcb_tax'          => $pcbTax,
            'total_deductions' => $totalDeductions,
            'net_salary'       => $netSalary,

            // Metadata (not payroll columns) for creating an OT PayrollItem.
            'overtime_hours'   => $overtimeHours,
            'overtime_amount'  => $overtimeAmount,
        ];
    }

    /**
     * Sum the Attendance `overtime_hours` accessor across the user's
     * attendances within the given month.
     */
    public function totalOvertimeHours(User $user, string $month): float
    {
        try {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            return 0.0;
        }
        $end = $start->copy()->endOfMonth();

        $attendances = $user->attendances()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->with('breaks')
            ->get();

        return round((float) $attendances->sum(fn ($a) => $a->overtime_hours), 2);
    }

    /**
     * Derive an hourly rate from the monthly basic salary using the
     * configurable working-days-per-month and standard daily hours.
     */
    protected function hourlyRate(float $basicSalary, array $config): float
    {
        $workingDays  = (float) ($config['working_days_per_month'] ?: 26);
        $dailyHours   = (float) ($config['standard_daily_hours'] ?: 8);
        $divisor      = $workingDays * $dailyHours;

        if ($divisor <= 0) {
            return 0.0;
        }

        return $basicSalary / $divisor;
    }
}
