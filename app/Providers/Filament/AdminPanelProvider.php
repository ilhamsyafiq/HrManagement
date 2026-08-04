<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('panel')
            ->login()
            // Account page (name / email / password change) in the user menu — lets
            // Super Admin / Admin change their own password from within the panel.
            ->profile(isSimple: false)
            // Impersonation banner (shows for an impersonated Admin who lands in the panel).
            ->renderHook('panels::body.start', fn (): string => \Illuminate\Support\Facades\Blade::render('
                @if (session()->has(\'impersonator_id\'))
                    <div style="background:#f59e0b;color:#fff;padding:8px 16px;font-size:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;">
                        <span>You are impersonating <strong>{{ auth()->user()->name }}</strong>.</span>
                        <a href="{{ route(\'impersonate.stop\') }}" style="text-decoration:underline;font-weight:600;white-space:nowrap;">Return to your account</a>
                    </div>
                @endif
            '))
            ->brandName('HR Management')
            ->navigationGroups(['People', 'Organization', 'Settings'])
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Custom dashboard: per-admin widget show/hide + drag-to-reorder.
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            // AccountWidget (the dashboard "Welcome / Sign out" card) intentionally
            // removed per request. Sign-out remains in the top-right user menu.
            ->widgets([
                // Top: full-width KPI cards.
                \App\Filament\Widgets\StatsOverview::class,
                // Today's snapshot + the doughnut share a row.
                \App\Filament\Widgets\TodayAttendanceChart::class,
                \App\Filament\Widgets\LeavesByTypeChart::class,
                // Full-width trend charts.
                \App\Filament\Widgets\AttendanceRateChart::class,
                \App\Filament\Widgets\AttendanceTrendChart::class,
                // Two-up breakdown charts.
                \App\Filament\Widgets\LeaveStatusChart::class,
                \App\Filament\Widgets\HeadcountByRoleChart::class,
                \App\Filament\Widgets\DepartmentHeadcountChart::class,
                // Pending approvals KPI group + upcoming leaves table.
                \App\Filament\Widgets\PendingApprovalsOverview::class,
                \App\Filament\Widgets\UpcomingLeavesTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
