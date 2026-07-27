<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Verifies the single-panel consolidation:
 * - Admin/Super Admin are redirected from legacy /admin/* to Filament /panel.
 * - Supervisors keep the legacy admin UI.
 * - The Filament dashboard (with widgets) renders.
 * - The PDF Reports page stays reachable (not redirected).
 */
class ConsolidationTest extends TestCase
{
    private function userWithRole(string $role): ?User
    {
        return User::whereHas('role', fn ($q) => $q->where('name', $role))->first();
    }

    public function test_consolidation(): void
    {
        $admin = $this->userWithRole('Super Admin');
        $supervisor = $this->userWithRole('Supervisor');
        $this->assertNotNull($admin, 'No Super Admin in hr DB');

        $report = [];

        // Admin redirected legacy -> Filament
        foreach ([
            'admin/dashboard' => '/panel',
            'admin/users' => '/panel/users',
            'admin/leaves' => '/panel/leaves',
        ] as $from => $to) {
            $res = $this->actingAs($admin)->get('/' . $from);
            $report[] = "admin GET /{$from} => {$res->getStatusCode()} -> " . $res->headers->get('Location');
            $res->assertRedirect($to);
        }

        // Reports page NOT redirected for admin
        $res = $this->actingAs($admin)->get('/admin/reports');
        $report[] = "admin GET /admin/reports => {$res->getStatusCode()} (should be 200, not redirect)";
        $this->assertEquals(200, $res->getStatusCode());

        // Filament dashboard renders (widgets included)
        $res = $this->actingAs($admin)->get('/panel');
        $report[] = "admin GET /panel (dashboard+widgets) => {$res->getStatusCode()}";
        $this->assertEquals(200, $res->getStatusCode());

        // Supervisor keeps legacy admin (not redirected to Filament they can't access)
        if ($supervisor) {
            $res = $this->actingAs($supervisor)->get('/admin/dashboard');
            $report[] = "supervisor GET /admin/dashboard => {$res->getStatusCode()} (should be 200 legacy)";
            $this->assertEquals(200, $res->getStatusCode());
        }

        fwrite(STDERR, "\n" . implode("\n", $report) . "\n");
    }
}
