<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure roles exist
        Role::firstOrCreate(['name' => 'Supervisor']);
        Role::firstOrCreate(['name' => 'Intern']);
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Super Admin']);
    }

    private function createSupervisorUser()
    {
        $role = Role::where('name', 'Supervisor')->first();
        return User::factory()->create(['role_id' => $role->id]);
    }

    private function createNormalUser()
    {
        $role = Role::where('name', 'Employee')->first();
        if (! $role) {
            $role = Role::firstOrCreate(['name' => 'Employee']);
        }
        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_supervisor_cannot_view_create_user_page()
    {
        $supervisor = $this->createSupervisorUser();
        $response = $this->actingAs($supervisor)->get(route('admin.users.create'));
        $response->assertStatus(403);
    }

    public function test_supervisor_cannot_store_user()
    {
        $supervisor = $this->createSupervisorUser();

        $response = $this->actingAs($supervisor)->post(route('admin.users.store'), [
            'name' => 'Test Intern',
            'email' => 'testintern@example.com',
            'password' => 'password123',
            'role_id' => Role::where('name', 'Intern')->first()->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_supervisor_cannot_view_edit_user_page()
    {
        $supervisor = $this->createSupervisorUser();
        $user = $this->createNormalUser();

        $response = $this->actingAs($supervisor)->get(route('admin.users.edit', $user->id));
        $response->assertStatus(403);
    }

    public function test_supervisor_cannot_update_user()
    {
        $supervisor = $this->createSupervisorUser();
        $user = $this->createNormalUser();

        $response = $this->actingAs($supervisor)->put(route('admin.users.update', $user->id), [
            'name' => 'Changed',
            'email' => $user->email,
            'role_id' => $user->role_id,
        ]);

        $response->assertStatus(403);
    }
}
