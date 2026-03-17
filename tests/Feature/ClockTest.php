<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ClockTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('active_office_locations');
        config(['geofence.enabled' => false]);
        $this->user = User::factory()->create();
    }

    public function test_clock_out_with_null_location()
    {
        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => now('Asia/Kuala_Lumpur')->toDateString(),
            'clock_in' => now('Asia/Kuala_Lumpur'),
        ]);

        // Act as the user
        $this->actingAs($this->user);

        // Test clock out with null lat/lng
        $response = $this->postJson('/clock/out', [
            'lat' => null,
            'lng' => null,
        ]);

        // Assert success
        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_break_out_with_null_location()
    {
        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => now('Asia/Kuala_Lumpur')->toDateString(),
            'clock_in' => now('Asia/Kuala_Lumpur'),
        ]);

        // Create break record
        $break = BreakRecord::create([
            'attendance_id' => $attendance->id,
            'break_in' => now('Asia/Kuala_Lumpur'),
        ]);

        // Act as the user
        $this->actingAs($this->user);

        // Test break out with null lat/lng
        $response = $this->postJson('/clock/break-out', [
            'lat' => null,
            'lng' => null,
            'break_id' => $break->id,
        ]);

        // Assert success
        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_clock_out_with_valid_location()
    {
        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => now('Asia/Kuala_Lumpur')->toDateString(),
            'clock_in' => now('Asia/Kuala_Lumpur'),
        ]);

        // Act as the user
        $this->actingAs($this->user);

        // Test clock out with valid lat/lng
        $response = $this->postJson('/clock/out', [
            'lat' => 3.1390,
            'lng' => 101.6869,
        ]);

        // Assert success
        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_break_out_with_valid_location()
    {
        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => now('Asia/Kuala_Lumpur')->toDateString(),
            'clock_in' => now('Asia/Kuala_Lumpur'),
        ]);

        // Create break record
        $break = BreakRecord::create([
            'attendance_id' => $attendance->id,
            'break_in' => now('Asia/Kuala_Lumpur'),
        ]);

        // Act as the user
        $this->actingAs($this->user);

        // Test break out with valid lat/lng
        $response = $this->postJson('/clock/break-out', [
            'lat' => 3.1390,
            'lng' => 101.6869,
            'break_id' => $break->id,
        ]);

        // Assert success
        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }
}
