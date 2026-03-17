<?php

namespace Tests\Feature;

use App\Models\Leave;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $leave;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::create(['name' => 'Admin']);
        $userRole = Role::create(['name' => 'Employee']);

        // Create users
        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->user = User::factory()->create(['role_id' => $userRole->id]);

        // Create a pending leave
        $this->leave = Leave::create([
            'user_id' => $this->user->id,
            'type' => 'AL',
            'start_date' => now()->addDays(1)->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'reason' => 'Test leave',
            'status' => 'Pending',
        ]);
    }

    public function test_admin_can_approve_leave()
    {
        $this->actingAs($this->admin);

        $response = $this->patch("/leave/{$this->leave->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Leave approved successfully.');

        $this->leave->refresh();
        $this->assertEquals('Approved', $this->leave->status);
        $this->assertEquals($this->admin->id, $this->leave->approved_by);
    }

    public function test_admin_can_reject_leave()
    {
        $this->actingAs($this->admin);

        $response = $this->patch("/leave/{$this->leave->id}/reject", [
            'reason' => 'Test rejection reason'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Leave rejected');

        $this->leave->refresh();
        $this->assertEquals('Rejected', $this->leave->status);
        $this->assertEquals($this->admin->id, $this->leave->approved_by);
        $this->assertEquals('Test rejection reason', $this->leave->rejection_reason);
    }

    public function test_non_admin_cannot_approve_leave()
    {
        $this->actingAs($this->user);

        $response = $this->patch("/leave/{$this->leave->id}/approve");

        $response->assertForbidden();
    }
}
