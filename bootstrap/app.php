<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Behind Railway's load balancer: trust forwarded headers so HTTPS is
        // detected correctly (secure cookies, correct URL scheme, real client IP).
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'redirect.admin' => \App\Http\Middleware\RedirectAdminMiddleware::class,
            'supervisor.admin' => \App\Http\Middleware\SupervisorAdminMiddleware::class,
            'check.supervisor.admin' => \App\Http\Middleware\CheckSupervisorAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
