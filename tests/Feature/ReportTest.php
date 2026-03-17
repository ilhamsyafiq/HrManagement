<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $intern;
    protected User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        Storage::fake();

        $supervisorRole = Role::where('name', 'Supervisor')->first();
        $internRole = Role::where('name', 'Intern')->first();

        $this->supervisor = User::factory()->create(['role_id' => $supervisorRole->id]);
        $this->intern = User::factory()->create([
            'role_id' => $internRole->id,
            'is_intern' => true,
            'supervisor_id' => $this->supervisor->id,
        ]);
    }

    public function test_intern_can_view_reports_index()
    {
        $response = $this->actingAs($this->intern)->get('/reports');
        $response->assertStatus(200);
        $response->assertViewHas('reports');
    }

    public function test_intern_can_create_report()
    {
        $response = $this->actingAs($this->intern)->get('/reports/create');
        $response->assertStatus(200);
    }

    public function test_intern_can_store_report()
    {
        $file = UploadedFile::fake()->create('report.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->intern)->post('/reports', [
            'title' => 'Weekly Report 1',
            'file' => $file,
        ]);

        $response->assertRedirect(route('reports.index'));
        $this->assertDatabaseHas('documents', [
            'title' => 'Weekly Report 1',
            'user_id' => $this->intern->id,
            'type' => 'Internship Report',
            'status' => 'draft',
            'supervisor_id' => $this->supervisor->id,
        ]);
    }

    public function test_report_store_validates_required_fields()
    {
        $response = $this->actingAs($this->intern)->post('/reports', []);

        $response->assertSessionHasErrors(['title', 'file']);
    }

    public function test_intern_can_submit_draft_report()
    {
        $report = Document::create([
            'user_id' => $this->intern->id,
            'title' => 'Test Report',
            'type' => 'Internship Report',
            'path' => 'reports/test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->intern)
            ->post("/reports/{$report->id}/submit");

        $response->assertRedirect();
        $report->refresh();
        $this->assertEquals('pending', $report->status);
        $this->assertEquals($this->intern->supervisor_id, $report->supervisor_id);
    }

    public function test_cannot_submit_signed_report()
    {
        $report = Document::create([
            'user_id' => $this->intern->id,
            'title' => 'Signed Report',
            'type' => 'Internship Report',
            'path' => 'reports/test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'status' => 'signed',
        ]);

        $response = $this->actingAs($this->intern)
            ->post("/reports/{$report->id}/submit");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_other_user_cannot_submit_report()
    {
        $otherIntern = User::factory()->create([
            'role_id' => Role::where('name', 'Intern')->first()->id,
            'is_intern' => true,
            'supervisor_id' => $this->supervisor->id,
        ]);

        $report = Document::create([
            'user_id' => $this->intern->id,
            'title' => 'Test Report',
            'type' => 'Internship Report',
            'path' => 'reports/test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($otherIntern)
            ->post("/reports/{$report->id}/submit");

        $response->assertStatus(403);
    }

    public function test_supervisor_sees_intern_reports()
    {
        Document::create([
            'user_id' => $this->intern->id,
            'title' => 'Intern Report',
            'type' => 'Internship Report',
            'path' => 'reports/test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'status' => 'pending',
            'supervisor_id' => $this->supervisor->id,
        ]);

        $response = $this->actingAs($this->supervisor)->get('/reports');

        $response->assertStatus(200);
        $response->assertViewHas('reports', function ($reports) {
            return $reports->count() === 1;
        });
    }

    public function test_intern_can_delete_own_report()
    {
        Storage::put('reports/test.pdf', 'content');

        $report = Document::create([
            'user_id' => $this->intern->id,
            'title' => 'Delete Me',
            'type' => 'Internship Report',
            'path' => 'reports/test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->intern)
            ->delete("/reports/{$report->id}");

        $response->assertRedirect(route('reports.index'));
        $this->assertDatabaseMissing('documents', ['id' => $report->id]);
    }

    public function test_download_signed_report_requires_signed_status()
    {
        $report = Document::create([
            'user_id' => $this->intern->id,
            'title' => 'Not Signed',
            'type' => 'Internship Report',
            'path' => 'reports/test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->intern)
            ->get("/reports/{$report->id}/download-signed");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
