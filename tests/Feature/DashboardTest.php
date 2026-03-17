<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Document;
use App\Models\Leave;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_employee_sees_employee_dashboard()
    {
        $role = Role::where('name', 'Employee')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    public function test_intern_sees_intern_dashboard()
    {
        $supervisorRole = Role::where('name', 'Supervisor')->first();
        $internRole = Role::where('name', 'Intern')->first();
        $supervisor = User::factory()->create(['role_id' => $supervisorRole->id]);
        $intern = User::factory()->create([
            'role_id' => $internRole->id,
            'is_intern' => true,
            'supervisor_id' => $supervisor->id,
        ]);

        $response = $this->actingAs($intern)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('intern.dashboard');
        $response->assertViewHas('reportStats');
    }

    public function test_admin_redirected_to_admin_dashboard()
    {
        $role = Role::where('name', 'Admin')->first();
        $admin = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_dashboard_shows_today_attendance()
    {
        $role = Role::where('name', 'Employee')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => now('Asia/Kuala_Lumpur')->toDateString(),
            'clock_in' => now('Asia/Kuala_Lumpur'),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('todayAttendance');
    }

    public function test_intern_dashboard_shows_report_stats()
    {
        $supervisorRole = Role::where('name', 'Supervisor')->first();
        $internRole = Role::where('name', 'Intern')->first();
        $supervisor = User::factory()->create(['role_id' => $supervisorRole->id]);
        $intern = User::factory()->create([
            'role_id' => $internRole->id,
            'is_intern' => true,
            'supervisor_id' => $supervisor->id,
        ]);

        Document::create([
            'user_id' => $intern->id,
            'type' => 'Internship Report',
            'path' => 'reports/test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'status' => 'draft',
            'title' => 'Test Report',
        ]);

        $response = $this->actingAs($intern)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('reportStats', function ($stats) {
            return $stats['total'] === 1 && $stats['draft'] === 1;
        });
    }

    public function test_unauthenticated_user_redirected_to_login()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }
}
