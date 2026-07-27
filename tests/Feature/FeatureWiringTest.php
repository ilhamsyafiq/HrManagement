<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SystemNotification;
use Tests\TestCase;

class FeatureWiringTest extends TestCase
{
    private function role(string $r): ?User
    {
        return User::whereHas('role', fn ($q) => $q->where('name', $r))->first();
    }

    public function test_new_features_render_and_notify(): void
    {
        $emp = $this->role('Employee');
        $admin = $this->role('Super Admin');
        $this->assertNotNull($emp);
        $this->assertNotNull($admin);

        $out = [];

        // Employee-facing pages (leave balance card, overtime column live here)
        foreach (['/dashboard', '/leave', '/claims', '/payroll', '/notifications'] as $u) {
            $code = $this->actingAs($emp)->get($u)->getStatusCode();
            $out[] = "EMP  $u => $code";
            $this->assertContains($code, [200, 302], "$u failed");
        }

        // Admin Filament shift resource
        foreach (['/panel/shifts', '/panel/shifts/create'] as $u) {
            $code = $this->actingAs($admin)->get($u)->getStatusCode();
            $out[] = "ADM  $u => $code";
            $this->assertEquals(200, $code, "$u failed");
        }

        // Notification round-trip: send one, confirm it lands + page shows it
        $emp->notify(new SystemNotification('Test title', 'Test body', '/dashboard', 'bell'));
        $fresh = $emp->fresh();
        $out[] = 'unread after notify => ' . $fresh->unreadNotifications->count();
        $this->assertGreaterThanOrEqual(1, $fresh->unreadNotifications->count());

        $res = $this->actingAs($fresh)->get('/notifications');
        $res->assertStatus(200)->assertSee('Test title');
        $out[] = 'notifications page shows notification => OK';

        // cleanup the test notification
        $fresh->notifications()->where('data', 'like', '%Test title%')->delete();

        fwrite(STDERR, "\n" . implode("\n", $out) . "\n");
    }
}
