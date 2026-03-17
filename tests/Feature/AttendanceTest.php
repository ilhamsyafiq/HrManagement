<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BreakRecord;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $employee;
    protected User $supervisor;
    protected User $intern;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $employeeRole = Role::where('name', 'Employee')->first();
        $supervisorRole = Role::where('name', 'Supervisor')->first();
        $internRole = Role::where('name', 'Intern')->first();

        $this->employee = User::factory()->create(['role_id' => $employeeRole->id]);
        $this->supervisor = User::factory()->create(['role_id' => $supervisorRole->id]);
        $this->intern = User::factory()->create([
            'role_id' => $internRole->id,
            'is_intern' => true,
            'supervisor_id' => $this->supervisor->id,
        ]);
    }

    public function test_employee_can_view_attendance_history()
    {
        Attendance::create([
            'user_id' => $this->employee->id,
            'date' => now('Asia/Kuala_Lumpur')->subDay()->toDateString(),
            'clock_in' => now('Asia/Kuala_Lumpur')->subDay()->setTime(9, 0),
            'clock_out' => now('Asia/Kuala_Lumpur')->subDay()->setTime(17, 0),
            'total_work_hours' => 8.0,
        ]);

        $response = $this->actingAs($this->employee)->get('/attendance');

        $response->assertStatus(200);
        $response->assertViewHas('attendances');
    }

    public function test_supervisor_can_view_intern_attendance()
    {
        Attendance::create([
            'user_id' => $this->intern->id,
            'date' => now('Asia/Kuala_Lumpur')->toDateString(),
            'clock_in' => now('Asia/Kuala_Lumpur'),
        ]);

        $response = $this->actingAs($this->supervisor)
            ->get('/attendance?user=' . $this->intern->id);

        $response->assertStatus(200);
    }

    public function test_employee_cannot_view_others_attendance()
    {
        $otherEmployee = User::factory()->create([
            'role_id' => Role::where('name', 'Employee')->first()->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->get('/attendance?user=' . $otherEmployee->id);

        $response->assertStatus(403);
    }

    public function test_clock_in_creates_attendance_record()
    {
        config(['geofence.enabled' => false]);

        $response = $this->actingAs($this->employee)
            ->postJson('/clock/in', ['lat' => null, 'lng' => null]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->employee->id,
            'date' => now('Asia/Kuala_Lumpur')->toDateString(),
        ]);
    }

    public function test_cannot_clock_in_twice()
    {
        config(['geofence.enabled' => false]);

        Attendance::create([
            'user_id' => $this->employee->id,
            'date' => now('Asia/Kuala_Lumpur')->toDateString(),
            'clock_in' => now('Asia/Kuala_Lumpur'),
        ]);

        $response = $this->actingAs($this->employee)
            ->postJson('/clock/in', ['lat' => null, 'lng' => null]);

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_clock_out_calculates_work_hours()
    {
        config(['geofence.enabled' => false]);

        $clockIn = now('Asia/Kuala_Lumpur')->subHours(8);
        Attendance::create([
            'user_id' => $this->employee->id,
            'date' => now('Asia/Kuala_Lumpur')->toDateString(),
            'clock_in' => $clockIn,
        ]);

        $response = $this->actingAs($this->employee)
            ->postJson('/clock/out', ['lat' => null, 'lng' => null]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $attendance = Attendance::where('user_id', $this->employee->id)->first();
        $this->assertNotNull($attendance->clock_out);
        $this->assertGreaterThan(0, $attendance->total_work_hours);
    }

    public function test_cannot_clock_out_without_clock_in()
    {
        config(['geofence.enabled' => false]);

        $response = $this->actingAs($this->employee)
            ->postJson('/clock/out', ['lat' => null, 'lng' => null]);

        $response->assertStatus(200)->assertJson(['success' => false]);
    }

    public function test_break_in_creates_break_record()
    {
        $attendance = Attendance::create([
            'user_id' => $this->employee->id,
            'date' => now('Asia/Kuala_Lumpur')->toDateString(),
            'clock_in' => now('Asia/Kuala_Lumpur'),
        ]);

        $response = $this->actingAs($this->employee)
            ->postJson('/clock/break-in', ['lat' => null, 'lng' => null]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
        ]);
    }

    public function test_break_out_calculates_duration()
    {
        $attendance = Attendance::create([
            'user_id' => $this->employee->id,
            'date' => now('Asia/Kuala_Lumpur')->toDateString(),
            'clock_in' => now('Asia/Kuala_Lumpur')->subHours(2),
        ]);

        $break = BreakRecord::create([
            'attendance_id' => $attendance->id,
            'break_in' => now('Asia/Kuala_Lumpur')->subMinutes(30),
        ]);

        $response = $this->actingAs($this->employee)
            ->postJson('/clock/break-out', [
                'lat' => null,
                'lng' => null,
                'break_id' => $break->id,
            ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $break->refresh();
        $this->assertNotNull($break->break_out);
        $this->assertNotNull($break->duration_minutes);
    }

    public function test_today_attendance_json_endpoint()
    {
        Attendance::create([
            'user_id' => $this->employee->id,
            'date' => now('Asia/Kuala_Lumpur')->toDateString(),
            'clock_in' => now('Asia/Kuala_Lumpur'),
        ]);

        $response = $this->actingAs($this->employee)
            ->getJson('/attendance/today');

        $response->assertStatus(200)
            ->assertJsonStructure(['attendance', 'breaks']);
    }

    public function test_attendance_edit_updates_record()
    {
        $attendance = Attendance::create([
            'user_id' => $this->employee->id,
            'date' => now('Asia/Kuala_Lumpur')->toDateString(),
            'clock_in' => now('Asia/Kuala_Lumpur')->setTime(9, 0),
        ]);

        $response = $this->actingAs($this->employee)
            ->post("/attendance/{$attendance->id}/edit", [
                'clock_in' => '08:30',
                'reason' => 'Forgot to clock in on time',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_formatted_work_hours_attribute()
    {
        $attendance = new Attendance(['total_work_hours' => 8.5]);
        $this->assertEquals('8h 30m', $attendance->formatted_work_hours);

        $attendance2 = new Attendance(['total_work_hours' => 0.12]);
        $this->assertEquals('0h 7m', $attendance2->formatted_work_hours);

        $attendance3 = new Attendance(['total_work_hours' => null]);
        $this->assertEquals('N/A', $attendance3->formatted_work_hours);
    }
}
