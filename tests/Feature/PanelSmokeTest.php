<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Renders every Filament panel page as an admin and reports any 500/exception.
 * Uses the real `hr` database (no RefreshDatabase) — read-only GETs.
 */
class PanelSmokeTest extends TestCase
{
    public function test_all_panel_pages_render(): void
    {
        $admin = User::whereHas('role', fn ($q) => $q->where('name', 'Super Admin'))->first();
        $this->assertNotNull($admin, 'No Super Admin user found in the hr database.');

        $resources = [
            'users', 'departments', 'attendances', 'claims', 'leaves',
            'announcements', 'holidays', 'office-locations', 'working-hours',
            'payrolls', 'audit-logs', 'roles',
        ];

        $urls = ['/panel'];
        foreach ($resources as $r) {
            $urls[] = "/panel/{$r}";            // index
            $urls[] = "/panel/{$r}/create";     // create (audit-logs has none → expect 404, fine)
        }
        // Edit pages (load record + relation managers) for the heavy ones.
        $urls[] = '/panel/users/' . $admin->getKey() . '/edit';

        $failures = [];
        foreach ($urls as $url) {
            try {
                $res = $this->actingAs($admin)->get($url);
                $status = $res->getStatusCode();
                $line = str_pad($url, 40) . " => {$status}";
                if ($status >= 500) {
                    $msg = optional($res->exception)->getMessage() ?? 'unknown 500';
                    $line .= "  !! " . $msg;
                    $failures[] = $line;
                }
                fwrite(STDERR, $line . "\n");
            } catch (\Throwable $e) {
                $line = str_pad($url, 40) . " => EXCEPTION  !! " . $e->getMessage()
                    . '  @ ' . $e->getFile() . ':' . $e->getLine();
                $failures[] = $line;
                fwrite(STDERR, $line . "\n");
            }
        }

        $this->assertEmpty($failures, "\nBroken panel pages:\n" . implode("\n", $failures) . "\n");
    }
}
