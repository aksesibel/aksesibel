<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\FollowUpAssignment;
use App\Models\FollowUpEvidence;
use App\Models\FollowUpProgress;
use App\Models\IssueCategory;
use App\Models\Report;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use App\Models\VerificationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FollowUpEvidenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
        Storage::fake('local');
    }

    /**
     * Helper: membuat Report lengkap dengan dependensi minimal.
     */
    private function createReport(int $statusId, User $reporter): Report
    {
        $facilityCategory = FacilityCategory::firstOrCreate(['category_name' => 'Test Category']);
        $facility = Facility::firstOrCreate(
            ['name' => 'Test Facility'],
            ['category_id' => $facilityCategory->category_id],
        );
        $issueCategory = IssueCategory::firstOrCreate(['category_name' => 'Test Issue']);
        $period = ReportingPeriod::firstOrCreate(
            ['period_name' => 'Test Period'],
            ['start_date' => now()->startOfYear(), 'end_date' => now()->endOfYear()],
        );

        return Report::create([
            'report_number'     => 'RPT-' . fake()->unique()->numerify('######'),
            'user_id'           => $reporter->id,
            'facility_id'       => $facility->facility_id,
            'issue_category_id' => $issueCategory->issue_category_id,
            'status_id'         => $statusId,
            'period_id'         => $period->period_id,
            'description'       => 'Laporan tes bukti.',
        ]);
    }

    /**
     * Helper: membuat penugasan lengkap dengan user, laporan, dan progres.
     */
    private function createAssignmentWithProgress(string $roleName = 'Petugas Tindak Lanjut', bool $ownAssignment = true): array
    {
        $role = Role::where('role_name', $roleName)->firstOrFail();
        $user = User::factory()->create([
            'role_id'        => $role->role_id,
            'account_status' => 'active',
        ]);

        $adminRole = Role::where('role_name', 'Admin')->firstOrFail();
        $admin = User::factory()->create([
            'role_id'        => $adminRole->role_id,
            'account_status' => 'active',
        ]);

        // Buat reporter untuk Report
        $citizenRole = Role::where('role_name', 'Masyarakat')->firstOrFail();
        $citizen = User::factory()->create([
            'role_id'        => $citizenRole->role_id,
            'account_status' => 'active',
        ]);

        $status = VerificationStatus::where('status_name', 'Dalam Tindak Lanjut')->first();
        $report = $this->createReport($status->status_id, $citizen);

        $assignment = FollowUpAssignment::create([
            'report_id'         => $report->report_id,
            'assigned_to'       => $ownAssignment ? $user->id : $admin->id,
            'assigned_by'       => $admin->id,
            'assignment_status' => 'in_progress',
            'assigned_at'       => now(),
        ]);

        $progress = FollowUpProgress::create([
            'assignment_id'      => $assignment->assignment_id,
            'actor_id'           => $ownAssignment ? $user->id : $admin->id,
            'progress_status'    => 'Sedang Dikerjakan',
            'action_description' => 'Pembatas sementara dipasang.',
            'recorded_at'        => now(),
        ]);

        return compact('user', 'admin', 'citizen', 'assignment', 'progress', 'report');
    }

    /** @test */
    public function test_assigned_officer_can_upload_evidence()
    {
        $data = $this->createAssignmentWithProgress();

        $response = $this->actingAs($data['user'])->post(
            route('follow-up-assignments.evidence.store', $data['assignment']->assignment_id),
            [
                'progress_id' => $data['progress']->progress_id,
                'photos'      => [
                    UploadedFile::fake()->create('foto1.jpg', 1024, 'image/jpeg'),
                ],
                'description' => 'Foto perbaikan awal.',
            ]
        );

        $response->assertRedirect(route('follow-up-assignments.show', $data['assignment']->assignment_id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('follow_up_evidences', [
            'progress_id' => $data['progress']->progress_id,
            'description'  => 'Foto perbaikan awal.',
        ]);

        // File tersimpan di disk
        $evidence = FollowUpEvidence::where('progress_id', $data['progress']->progress_id)->first();
        Storage::disk('local')->assertExists($evidence->file_path);
    }

    /** @test */
    public function test_admin_can_upload_evidence()
    {
        $data = $this->createAssignmentWithProgress('Petugas Tindak Lanjut', true);

        $response = $this->actingAs($data['admin'])->post(
            route('follow-up-assignments.evidence.store', $data['assignment']->assignment_id),
            [
                'progress_id' => $data['progress']->progress_id,
                'photos'      => [
                    UploadedFile::fake()->create('admin-foto.png', 512, 'image/png'),
                ],
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('follow_up_evidences', 1);
    }

    /** @test */
    public function test_facility_manager_can_upload_evidence()
    {
        $managerRole = Role::where('role_name', 'Pengelola Fasilitas')->first();
        if (! $managerRole) {
            $this->markTestSkipped('Role Pengelola Fasilitas belum ada di seeder.');
        }

        $manager = User::factory()->create([
            'role_id'        => $managerRole->role_id,
            'account_status' => 'active',
        ]);

        $data = $this->createAssignmentWithProgress();

        $response = $this->actingAs($manager)->post(
            route('follow-up-assignments.evidence.store', $data['assignment']->assignment_id),
            [
                'progress_id' => $data['progress']->progress_id,
                'photos'      => [
                    UploadedFile::fake()->create('manager.jpg', 256, 'image/jpeg'),
                ],
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function test_unrelated_officer_cannot_upload_evidence()
    {
        $data = $this->createAssignmentWithProgress();

        // Buat petugas lain yang BUKAN penerima tugas
        $otherRole = Role::where('role_name', 'Petugas Tindak Lanjut')->firstOrFail();
        $otherOfficer = User::factory()->create([
            'role_id'        => $otherRole->role_id,
            'account_status' => 'active',
        ]);

        $response = $this->actingAs($otherOfficer)->post(
            route('follow-up-assignments.evidence.store', $data['assignment']->assignment_id),
            [
                'progress_id' => $data['progress']->progress_id,
                'photos'      => [
                    UploadedFile::fake()->create('x.jpg', 100, 'image/jpeg'),
                ],
            ]
        );

        $response->assertForbidden();
        $this->assertDatabaseCount('follow_up_evidences', 0);
    }

    /** @test */
    public function test_invalid_file_format_rejected()
    {
        $data = $this->createAssignmentWithProgress();

        $response = $this->actingAs($data['user'])->post(
            route('follow-up-assignments.evidence.store', $data['assignment']->assignment_id),
            [
                'progress_id' => $data['progress']->progress_id,
                'photos'      => [
                    UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
                ],
            ]
        );

        $response->assertSessionHasErrors('photos.0');
        $this->assertDatabaseCount('follow_up_evidences', 0);
    }

    /** @test */
    public function test_file_too_large_rejected()
    {
        $data = $this->createAssignmentWithProgress();

        $response = $this->actingAs($data['user'])->post(
            route('follow-up-assignments.evidence.store', $data['assignment']->assignment_id),
            [
                'progress_id' => $data['progress']->progress_id,
                'photos'      => [
                    UploadedFile::fake()->create('besar.jpg', 6000, 'image/jpeg'), // 6 MB > 5 MB
                ],
            ]
        );

        $response->assertSessionHasErrors('photos.0');
        $this->assertDatabaseCount('follow_up_evidences', 0);
    }

    /** @test */
    public function test_too_many_files_rejected()
    {
        $data = $this->createAssignmentWithProgress();

        $photos = [];
        for ($i = 1; $i <= 6; $i++) {
            $photos[] = UploadedFile::fake()->create("foto{$i}.jpg", 100, 'image/jpeg');
        }

        $response = $this->actingAs($data['user'])->post(
            route('follow-up-assignments.evidence.store', $data['assignment']->assignment_id),
            [
                'progress_id' => $data['progress']->progress_id,
                'photos'      => $photos,
            ]
        );

        $response->assertSessionHasErrors('photos');
        $this->assertDatabaseCount('follow_up_evidences', 0);
    }

    /** @test */
    public function test_progress_from_other_assignment_rejected()
    {
        $data = $this->createAssignmentWithProgress();

        // Buat penugasan lain dengan progresnya sendiri
        $otherAssignment = FollowUpAssignment::create([
            'report_id'         => $data['report']->report_id,
            'assigned_to'       => $data['user']->id,
            'assigned_by'       => $data['admin']->id,
            'assignment_status' => 'in_progress',
            'assigned_at'       => now(),
        ]);

        $otherProgress = FollowUpProgress::create([
            'assignment_id'      => $otherAssignment->assignment_id,
            'actor_id'           => $data['user']->id,
            'progress_status'    => 'Sedang Dikerjakan',
            'action_description' => 'Progres penugasan lain.',
            'recorded_at'        => now(),
        ]);

        // Coba unggah bukti ke assignment pertama tapi pakai progress_id dari assignment kedua
        $response = $this->actingAs($data['user'])->post(
            route('follow-up-assignments.evidence.store', $data['assignment']->assignment_id),
            [
                'progress_id' => $otherProgress->progress_id,
                'photos'      => [
                    UploadedFile::fake()->create('cross.jpg', 100, 'image/jpeg'),
                ],
            ]
        );

        $response->assertSessionHasErrors('progress_id');
        $this->assertDatabaseCount('follow_up_evidences', 0);
    }

    /** @test */
    public function test_guest_cannot_upload_evidence()
    {
        $data = $this->createAssignmentWithProgress();

        $response = $this->post(
            route('follow-up-assignments.evidence.store', $data['assignment']->assignment_id),
            [
                'progress_id' => $data['progress']->progress_id,
                'photos'      => [
                    UploadedFile::fake()->create('x.jpg', 100, 'image/jpeg'),
                ],
            ]
        );

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function test_evidence_visible_on_assignment_detail()
    {
        $data = $this->createAssignmentWithProgress();

        // Simpan bukti langsung
        $evidence = FollowUpEvidence::create([
            'progress_id' => $data['progress']->progress_id,
            'file_path'   => 'evidence/test/foto.jpg',
            'file_type'   => 'image/jpeg',
            'uploaded_at'  => now(),
            'description'  => 'Foto perbaikan.',
        ]);

        $response = $this->actingAs($data['user'])->get(
            route('follow-up-assignments.show', $data['assignment']->assignment_id)
        );

        $response->assertOk();
        $response->assertSee('Bukti Foto');
    }

    /** @test */
    public function test_multiple_photos_uploaded_at_once()
    {
        $data = $this->createAssignmentWithProgress();

        $response = $this->actingAs($data['user'])->post(
            route('follow-up-assignments.evidence.store', $data['assignment']->assignment_id),
            [
                'progress_id' => $data['progress']->progress_id,
                'photos'      => [
                    UploadedFile::fake()->create('a.jpg', 500, 'image/jpeg'),
                    UploadedFile::fake()->create('b.png', 300, 'image/png'),
                    UploadedFile::fake()->create('c.jpg', 400, 'image/jpeg'),
                ],
                'description' => 'Tiga foto sekaligus.',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('follow_up_evidences', 3);

        // Semua terkait progres yang sama
        $evidences = FollowUpEvidence::where('progress_id', $data['progress']->progress_id)->get();
        $this->assertCount(3, $evidences);

        foreach ($evidences as $e) {
            Storage::disk('local')->assertExists($e->file_path);
        }
    }

    /** @test */
    public function test_inactive_user_cannot_upload_evidence()
    {
        $data = $this->createAssignmentWithProgress();
        $data['user']->update(['account_status' => 'inactive']);

        $response = $this->actingAs($data['user'])->post(
            route('follow-up-assignments.evidence.store', $data['assignment']->assignment_id),
            [
                'progress_id' => $data['progress']->progress_id,
                'photos'      => [
                    UploadedFile::fake()->create('foto1.jpg', 500, 'image/jpeg'),
                ],
            ]
        );

        $response->assertForbidden();
        $this->assertDatabaseCount('follow_up_evidences', 0);
    }

    /** @test */
    public function test_authorized_officer_can_view_evidence()
    {
        $data = $this->createAssignmentWithProgress();

        $path = "evidence/{$data['assignment']->assignment_id}/{$data['progress']->progress_id}/test.jpg";
        Storage::disk('local')->put($path, 'dummy image content');

        $evidence = FollowUpEvidence::create([
            'progress_id'  => $data['progress']->progress_id,
            'file_path'    => $path,
            'file_type'    => 'image/jpeg',
            'uploaded_at'  => now(),
            'description'  => 'Foto bukti lapangan',
        ]);

        $response = $this->actingAs($data['user'])->get(
            route('evidence.show', $evidence->follow_up_evidence_id)
        );

        $response->assertOk();
    }

    /** @test */
    public function test_unrelated_officer_cannot_view_evidence()
    {
        $data = $this->createAssignmentWithProgress();

        $path = "evidence/{$data['assignment']->assignment_id}/{$data['progress']->progress_id}/test.jpg";
        Storage::disk('local')->put($path, 'dummy image content');

        $evidence = FollowUpEvidence::create([
            'progress_id'  => $data['progress']->progress_id,
            'file_path'    => $path,
            'file_type'    => 'image/jpeg',
            'uploaded_at'  => now(),
        ]);

        $otherRole = Role::where('role_name', 'Petugas Tindak Lanjut')->firstOrFail();
        $otherOfficer = User::factory()->create([
            'role_id'        => $otherRole->role_id,
            'account_status' => 'active',
        ]);

        $response = $this->actingAs($otherOfficer)->get(
            route('evidence.show', $evidence->follow_up_evidence_id)
        );

        $response->assertForbidden();
    }

    /** @test */
    public function test_inactive_user_cannot_view_evidence()
    {
        $data = $this->createAssignmentWithProgress();

        $path = "evidence/{$data['assignment']->assignment_id}/{$data['progress']->progress_id}/test.jpg";
        Storage::disk('local')->put($path, 'dummy image content');

        $evidence = FollowUpEvidence::create([
            'progress_id'  => $data['progress']->progress_id,
            'file_path'    => $path,
            'file_type'    => 'image/jpeg',
            'uploaded_at'  => now(),
        ]);

        $data['user']->update(['account_status' => 'inactive']);

        $response = $this->actingAs($data['user'])->get(
            route('evidence.show', $evidence->follow_up_evidence_id)
        );

        $response->assertForbidden();
    }

    /** @test */
    public function test_view_evidence_returns_404_when_file_not_found()
    {
        $data = $this->createAssignmentWithProgress();

        $evidence = FollowUpEvidence::create([
            'progress_id'  => $data['progress']->progress_id,
            'file_path'    => "evidence/{$data['assignment']->assignment_id}/{$data['progress']->progress_id}/missing.jpg",
            'file_type'    => 'image/jpeg',
            'uploaded_at'  => now(),
        ]);

        $response = $this->actingAs($data['user'])->get(
            route('evidence.show', $evidence->follow_up_evidence_id)
        );

        $response->assertNotFound();
    }

    /** @test */
    public function test_failed_storage_rolls_back_database_and_cleans_files()
    {
        $data = $this->createAssignmentWithProgress();

        // Buat file JPEG valid minimal (1x1 px), lalu override storeAs agar mengembalikan false
        $tempPath = tempnam(sys_get_temp_dir(), 'test_fail');
        file_put_contents($tempPath, base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA='));
        
        $failingFile = new class($tempPath, 'gagal.jpg', 'image/jpeg', null, true) extends UploadedFile {
            public function storeAs($path, $name = null, $options = [])
            {
                return false; // Mensimulasikan kegagalan disk / storeAs
            }
        };

        $response = $this->actingAs($data['user'])->post(
            route('follow-up-assignments.evidence.store', $data['assignment']->assignment_id),
            [
                'progress_id' => $data['progress']->progress_id,
                'photos'      => [$failingFile],
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionMissing('success');
        $this->assertDatabaseCount('follow_up_evidences', 0);
    }

    /** @test */
    public function test_first_photo_cleaned_up_when_second_photo_fails_storage()
    {
        $data = $this->createAssignmentWithProgress();

        $photo1 = UploadedFile::fake()->create('foto1.jpg', 100, 'image/jpeg');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_fail2');
        file_put_contents($tempPath, base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA='));

        $failingFile = new class($tempPath, 'gagal2.jpg', 'image/jpeg', null, true) extends UploadedFile {
            public function storeAs($path, $name = null, $options = [])
            {
                return false;
            }
        };

        $response = $this->actingAs($data['user'])->post(
            route('follow-up-assignments.evidence.store', $data['assignment']->assignment_id),
            [
                'progress_id' => $data['progress']->progress_id,
                'photos'      => [$photo1, $failingFile],
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionMissing('success');
        $this->assertDatabaseCount('follow_up_evidences', 0);

        // Pastikan tidak ada file yang tertinggal di folder evidence penugasan
        $files = Storage::disk('local')->allFiles("evidence/{$data['assignment']->assignment_id}");
        $this->assertEmpty($files);
    }

    /** @test */
    public function test_cleanup_exception_does_not_cause_500_and_logs_warning()
    {
        $data = $this->createAssignmentWithProgress();

        Log::spy();

        $diskMock = \Mockery::mock(Storage::disk('local'))->makePartial();
        $diskMock->shouldReceive('delete')->andThrow(new \RuntimeException('Disk I/O error during cleanup'));
        Storage::set('local', $diskMock);

        $photo1 = UploadedFile::fake()->create('foto1.jpg', 100, 'image/jpeg');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_fail3');
        file_put_contents($tempPath, base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA='));

        $failingFile = new class($tempPath, 'gagal3.jpg', 'image/jpeg', null, true) extends UploadedFile {
            public function storeAs($path, $name = null, $options = [])
            {
                return false;
            }
        };

        $response = $this->actingAs($data['user'])->post(
            route('follow-up-assignments.evidence.store', $data['assignment']->assignment_id),
            [
                'progress_id' => $data['progress']->progress_id,
                'photos'      => [$photo1, $failingFile],
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('follow_up_evidences', 0);

        Log::shouldHaveReceived('error')->once();
        Log::shouldHaveReceived('warning')->once();
    }
}
