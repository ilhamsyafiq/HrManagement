<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Malaysian Statutory Payroll Defaults
    |--------------------------------------------------------------------------
    |
    | These are CONFIGURABLE approximations of Malaysian statutory payroll
    | contributions. They are intended as sensible defaults for automated
    | payroll generation, NOT as a substitute for the official statutory
    | tables. Adjust per company policy / latest LHDN, KWSP (EPF), PERKESO
    | (SOCSO) and EIS rules as required.
    |
    | IMPORTANT: Real-world EPF, SOCSO and EIS are computed from published
    | wage-bracket / ceiling tables, not flat percentages. The percentage
    | approach below is a simplification. Where wage ceilings apply we clamp
    | the computed value with a `*_max` cap.
    |
    */

    /*
    | EPF (KWSP) — Employees Provident Fund.
    | Employee: 11% of wages.
    | Employer: 13% when monthly wage <= RM5,000, otherwise 12%.
    | (These are the common statutory rates; the split threshold is RM5,000.)
    */
    'epf' => [
        'employee_rate'          => 0.11,
        'employer_rate_low'      => 0.13, // wage <= threshold
        'employer_rate_high'     => 0.12, // wage >  threshold
        'employer_wage_threshold' => 5000,
    ],

    /*
    | SOCSO (PERKESO) — Employment Injury + Invalidity Scheme.
    | NOTE: Real SOCSO uses fixed contribution amounts per wage bracket with a
    | wage ceiling (currently RM6,000/month => max employee ~RM24.75,
    | employer ~RM86.65). The flat percentages below are an APPROXIMATION.
    | The `*_max` caps mirror the ceiling-based maximums.
    */
    'socso' => [
        'employee_rate' => 0.005,   // 0.5% (approximation)
        'employer_rate' => 0.0175,  // 1.75% (approximation)
        'employee_max'  => 24.75,   // approx statutory ceiling
        'employer_max'  => 86.65,   // approx statutory ceiling
    ],

    /*
    | EIS (SIP) — Employment Insurance System.
    | Employee 0.2%, Employer 0.2%. Real EIS also follows a wage-ceiling table
    | (ceiling RM6,000 => max ~RM11.90 each side). `*_max` mirrors that cap.
    */
    'eis' => [
        'employee_rate' => 0.002,
        'employer_rate' => 0.002,
        'employee_max'  => 11.90,
        'employer_max'  => 11.90,
    ],

    /*
    | PCB (Potongan Cukai Bulanan) — Monthly Tax Deduction / income tax.
    | Real PCB is COMPLEX: it depends on the employee's projected annual income,
    | marital status, number of children, EPF/insurance reliefs, zakat, prior
    | months' deductions, etc. It cannot be reduced to a single percentage.
    |
    | We therefore default PCB to a configurable FLAT estimate of 0. HR should
    | override per-employee (via a payroll item / manual edit) or plug in a
    | proper PCB engine. Set `flat` to a fixed RM amount, or use `rate` for a
    | crude percentage-of-gross estimate (applied only when `flat` is 0).
    */
    'pcb' => [
        'flat' => 0.0,  // fixed RM estimate per month (default: none)
        'rate' => 0.0,  // crude % of gross fallback when `flat` == 0
    ],

    /*
    | Overtime.
    | overtime_rate_multiplier: multiplier applied to the normal hourly rate
    | for overtime hours (Employment Act default for normal-day OT is 1.5x).
    | standard_daily_hours: fallback used to derive the hourly rate from the
    | monthly basic salary (monthly / working_days / standard_daily_hours).
    | working_days_per_month: divisor to convert monthly salary to a daily rate.
    */
    'overtime_rate_multiplier' => 1.5,
    'standard_daily_hours'     => 8,
    'working_days_per_month'   => 26, // common EA basis for daily-rate derivation

];
