<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Snap forgotten clock-outs to the scheduled shift end. Runs frequently so
// overnight shifts close soon after their (grace-padded) end. Requires
// `php artisan schedule:run` every minute from Task Scheduler / cron.
Schedule::command('attendance:auto-clockout')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->timezone('Asia/Kuala_Lumpur');
