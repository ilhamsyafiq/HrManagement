<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Consolidation to a single admin panel (Filament at /panel).
 *
 * Admins & Super Admins hitting the legacy Blade admin pages (/admin/*) are
 * redirected to their Filament equivalents. Supervisors — who cannot access the
 * Filament panel — continue to the legacy controller unchanged. Only GET listing
 * pages are mapped; POST/PUT/DELETE handlers and the PDF Reports page are untouched.
 */
class RedirectAdminsToFilament
{
    /** Legacy path (no leading slash) => Filament path. */
    protected array $map = [
        'admin/dashboard' => '/panel',
        'admin/users' => '/panel/users',
        'admin/users/create' => '/panel/users/create',
        'admin/departments' => '/panel/departments',
        'admin/attendances' => '/panel/attendances',
        'admin/leaves' => '/panel/leaves',
        'admin/audit-logs' => '/panel/audit-logs',
        'admin/office-locations' => '/panel/office-locations',
        'admin/working-hours' => '/panel/working-hours',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $request->isMethod('get') && ($user->isAdmin() || $user->isSuperAdmin())) {
            $path = trim($request->path(), '/');

            if (isset($this->map[$path])) {
                return redirect($this->map[$path]);
            }

            // /admin/users/{id}/edit  ->  /panel/users/{id}/edit
            if (preg_match('#^admin/users/(\d+)/edit$#', $path, $m)) {
                return redirect('/panel/users/' . $m[1] . '/edit');
            }
        }

        return $next($request);
    }
}
