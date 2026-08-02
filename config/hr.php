<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Annual Leave Entitlements
    |--------------------------------------------------------------------------
    |
    | Default annual leave entitlements (in days) per leave type for a full
    | calendar year. These are configurable Malaysian defaults based on the
    | Employment Act. Adjust per company policy as required.
    |
    */

    'leave_entitlements' => [
        'AL' => 14,        // Annual Leave
        'MC' => 14,        // Medical / Sick Leave
        'Emergency' => 7,  // Emergency Leave
    ],

    /*
    |--------------------------------------------------------------------------
    | Standard Daily Working Hours
    |--------------------------------------------------------------------------
    |
    | Fallback number of standard paid working hours in a normal day, used to
    | calculate overtime when a user has no specific WorkingHour configuration.
    |
    */

    'standard_daily_hours' => 8,

    /*
    |--------------------------------------------------------------------------
    | Email Notifications
    |--------------------------------------------------------------------------
    |
    | When true, SystemNotification is delivered over the "mail" channel in
    | addition to "database". Keep this false until a real MAIL_MAILER / SMTP
    | connection is configured, otherwise queued/sync mail sends will fail.
    |
    | To enable: set HR_EMAIL_NOTIFICATIONS=true in .env AFTER configuring a
    | real MAIL_MAILER / SMTP (MAIL_HOST, MAIL_PORT, MAIL_USERNAME, etc.).
    |
    */

    'email_notifications' => env('HR_EMAIL_NOTIFICATIONS', false),

    /*
    |--------------------------------------------------------------------------
    | Payroll Module
    |--------------------------------------------------------------------------
    |
    | Master switch for the payroll / payslip feature. Kept OFF while the
    | statutory calculation (EPF/SOCSO/EIS bracket tables, PCB, monthly vs
    | daily/hourly basis, proration rules) is still pending review with the
    | finance & HR teams. When off:
    |   - the Filament Payroll resource is hidden and its routes are blocked;
    |   - the employee "Payslip" nav links are hidden;
    |   - the Blade payroll routes are not registered.
    |
    | The underlying models, migrations and calculator remain intact — flip
    | HR_PAYROLL_ENABLED=true (then `php artisan optimize:clear`) to restore.
    |
    */

    'payroll_enabled' => env('HR_PAYROLL_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Auto Clock-Out
    |--------------------------------------------------------------------------
    |
    | When an employee forgets to clock out, App\Console\Commands\CloseForgotten
    | ClockOuts snaps the missing clock-out to the scheduled shift end (or, when
    | no schedule applies, to clock_in + standard_daily_hours) instead of letting
    | the open record run to "now" and inflate wages/hours.
    |
    | grace_minutes: only auto-close a record once this many minutes have elapsed
    | past the scheduled shift end (overnight-aware), so a genuinely late employee
    | still on shift is never closed prematurely.
    |
    */

    'auto_clockout' => [
        'grace_minutes' => env('HR_AUTO_CLOCKOUT_GRACE', 120),
    ],

];
