<?php

namespace Tests\Feature;

use App\Models\FollowUpAssignment;
use App\Models\FollowUpProgress;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use App\Models\VerificationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowUpProgressTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $assignedOfficer;
    private User $otherOfficer;
    private User $citizen;
    private FollowUpAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles
        $roleAdmin = Role::create(['role_name' => 'Admin']);
        $roleOfficer = Role::create(['role_name' => 'Petugas Tindak Lanjut']);
        $roleCitizen = Role::create(['role_name' => 'Masyarakat']);

        // Users
        $this->admin = User::factory()->create([
            'role_id'        => $roleAdmin->role_id,
            'account_status' => 'active',
        ]);
        $this->assignedOfficer = User::factory()->create([
            'role_id'        => $roleOfficer->role_id,
            'account_status' => 'active',
        ]);
        $this->otherOfficer = User::factory()->create([
            'role_id'        => $roleOfficer->role_id,
            'account_status' => 'active',
        ]);
        $this->citizen = User::factory()->create([
            'role_id'        => $roleCitizen->role_id,
            'account_status' => 'active',
        ]);

        // Status & Report
        $status = VerificationStatus::create(['status_name' => 'Dalam Tindak Lanjut']);
        $facilityCategory = \App\Models\FacilityCategory::firstOrCreate(['category_name' => 'Kategori Fasilitas']);
        $facility = \App\Models\Facility::firstOrCreate(
            ['name' => 'Fasilitas Uji'],
            ['category_id' => $facilityCategory->category_id]
        );
        $issueCategory = \App\Models\IssueCategory::firstOrCreate(['category_name' => 'Kendala Uji']);
        $period = \App\Models\ReportingPeriod::firstOrCreate(
            ['period_name' => 'Periode Uji'],
            ['start_date' => now()->startOfYear(), 'end_date' => now()->endOfYear()]
        );

        $report = Report::create([
            'report_number'     => 'RPT-TEST-001',
            'user_id'           => $this->citizen->id,
            'facility_id'       => $facility->facility_id,
            'issue_category_id' => $issueCategory->issue_category_id,
            'status_id'         => $status->status_id,
            'period_id'         => $period->period_id,
            'description'       => 'Deskripsi uji progres penanganan.',
        ]);

        $this->assignment = FollowUpAssignment::create([
            'report_id'         => $report->report_id,
            'assigned_to'       => $this->assignedOfficer->id,
            'assigned_by'       => $this->admin->id,
            'assignment_status' => 'assigned',
            'assigned_at'       => now(),
        ]);
    }

    public function test_guest_cannot_store_progress(): void
    {
        $this->post(route('follow-up-assignments.progress.store', $this->assignment->assignment_id), [
            'progress_status'    => 'Sedang Dikerjakan',
            'action_description' => 'Memulai pembersihan area.',
        ])->assertRedirect(route('login'));
    }

    public function test_citizen_cannot_store_progress(): void
    {
        $this->actingAs($this->citizen)
            ->post(route('follow-up-assignments.progress.store', $this->assignment->assignment_id), [
                'progress_status'    => 'Sedang Dikerjakan',
                'action_description' => 'Mencoba input progres.',
            ])->assertForbidden();
    }

    public function test_unrelated_officer_cannot_store_progress(): void
    {
        $this->actingAs($this->otherOfficer)
            ->post(route('follow-up-assignments.progress.store', $this->assignment->assignment_id), [
                'progress_status'    => 'Sedang Dikerjakan',
                'action_description' => 'Bukan petugas yang ditunjuk.',
            ])->assertForbidden();
    }

    public function test_assigned_officer_can_store_progress(): void
    {
        $this->actingAs($this->assignedOfficer)
            ->post(route('follow-up-assignments.progress.store', $this->assignment->assignment_id), [
                'progress_status'    => 'Sedang Dikerjakan',
                'action_description' => 'Material penghalang telah dipindahkan sebagian.',
            ])
            ->assertRedirect(route('follow-up-assignments.show', $this->assignment->assignment_id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('follow_up_progress', [
            'assignment_id'      => $this->assignment->assignment_id,
            'actor_id'           => $this->assignedOfficer->id,
            'progress_status'    => 'Sedang Dikerjakan',
            'action_description' => 'Material penghalang telah dipindahkan sebagian.',
        ]);

        // assignment_status beralih ke in_progress
        $this->assertEquals('in_progress', $this->assignment->fresh()->assignment_status);
    }

    public function test_admin_can_store_supervisory_progress(): void
    {
        $this->actingAs($this->admin)
            ->post(route('follow-up-assignments.progress.store', $this->assignment->assignment_id), [
                'progress_status'    => 'Dalam Koordinasi Lapangan',
                'action_description' => 'Instruksi tambahan koordinasi keamanan.',
            ])
            ->assertRedirect(route('follow-up-assignments.show', $this->assignment->assignment_id))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('follow_up_progress', [
            'assignment_id'      => $this->assignment->assignment_id,
            'actor_id'           => $this->admin->id,
            'progress_status'    => 'Dalam Koordinasi Lapangan',
        ]);
    }

    public function test_completed_progress_updates_assignment_status(): void
    {
        $this->actingAs($this->assignedOfficer)
            ->post(route('follow-up-assignments.progress.store', $this->assignment->assignment_id), [
                'progress_status'    => 'Selesai',
                'action_description' => 'Seluruh pekerjaan perbaikan jalur kursi roda telah rampung.',
            ])
            ->assertRedirect(route('follow-up-assignments.show', $this->assignment->assignment_id));

        $this->assertEquals('completed', $this->assignment->fresh()->assignment_status);
    }

    public function test_progress_validation_requires_status_and_description(): void
    {
        $this->actingAs($this->assignedOfficer)
            ->post(route('follow-up-assignments.progress.store', $this->assignment->assignment_id), [
                'progress_status'    => '',
                'action_description' => '',
            ])
            ->assertSessionHasErrors(['progress_status', 'action_description']);
    }
}

