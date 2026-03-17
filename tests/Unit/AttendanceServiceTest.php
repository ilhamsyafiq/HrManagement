<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Models\Role;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        config(['geofence.enabled' => false]);
    }

    public function test_work_hours_calculation_for_short_period()
    {
        $role = Role::where('name', 'Employee')->first();
        $user = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($user);

        $service = app(AttendanceService::class);

        // Clock in
        $service->clockIn(null, null);

        // Wait simulation: create attendance with 7-minute gap
        $attendance = Attendance::where('user_id', $user->id)->first();
        $attendance->update([
            'clock_in' => now('Asia/Kuala_Lumpur')->subMinutes(7),
        ]);

        // Clock out
        $service->clockOut(null, null);

        $attendance->refresh();
        $this->assertGreaterThanOrEqual(0, $attendance->total_work_hours);
        $this->assertStringContainsString('m', $attendance->formatted_work_hours);
    }

    public function test_work_hours_never_negative()
    {
        $attendance = new Attendance(['total_work_hours' => 0]);
        $this->assertEquals('N/A', $attendance->formatted_work_hours);

        $attendance2 = new Attendance(['total_work_hours' => 0.01]);
        $formatted = $attendance2->formatted_work_hours;
        $this->assertStringNotContainsString('-', $formatted);
    }

    public function test_break_hours_subtracted_from_total()
    {
        $role = Role::where('name', 'Employee')->first();
        $user = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($user);

        // Create attendance with 8 hour work day
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now('Asia/Kuala_Lumpur')->toDateString(),
            'clock_in' => now('Asia/Kuala_Lumpur')->subHours(8),
        ]);

        // Create a 1 hour break
        BreakRecord::create([
            'attendance_id' => $attendance->id,
            'break_in' => now('Asia/Kuala_Lumpur')->subHours(4),
            'break_out' => now('Asia/Kuala_Lumpur')->subHours(3),
            'duration_minutes' => 60,
        ]);

        $service = app(AttendanceService::class);
        $service->clockOut(null, null);

        $attendance->refresh();
        // 8 hours - 1 hour break = ~7 hours
        $this->assertLessThan(8, $attendance->total_work_hours);
        $this->assertGreaterThan(6, $attendance->total_work_hours);
    }

    public function test_formatted_work_hours_displays_correctly()
    {
        // 8 hours 30 minutes
        $attendance = new Attendance(['total_work_hours' => 8.5]);
        $this->assertEquals('8h 30m', $attendance->formatted_work_hours);

        // 0 hours 7 minutes (was previously showing -0.11)
        $attendance2 = new Attendance(['total_work_hours' => 0.12]);
        $this->assertEquals('0h 7m', $attendance2->formatted_work_hours);

        // 1 hour exactly
        $attendance3 = new Attendance(['total_work_hours' => 1.0]);
        $this->assertEquals('1h 0m', $attendance3->formatted_work_hours);

        // Null
        $attendance4 = new Attendance(['total_work_hours' => null]);
        $this->assertEquals('N/A', $attendance4->formatted_work_hours);
    }
}
