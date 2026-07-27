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

];
