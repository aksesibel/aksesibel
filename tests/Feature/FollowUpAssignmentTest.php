<?php

namespace Tests\Feature;

use App\Models\FollowUpAssignment;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use App\Models\VerificationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowUpAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $officer;
    private User $citizen;
    private Report $reportInFollowUp;
    private Report $reportVerified;
    private VerificationStatus $statusDalamTindakLanjut;
    private VerificationStatus $statusTerverifikasi;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles
        $roleAdmin = Role::create(['role_name' => 'Admin']);
        $roleOfficer = Role::create(['role_name' => 'Petugas Tindak Lanjut']);
        $roleCitizen = Role::create(['role_name' => 'Masyarakat']);

        // Users
        $this->admin = User::factory()->create([
            'role_id' => $roleAdmin->role_id,
            'account_status' => 'active',
        ]);
        $this->officer = User::factory()->create([
            'role_id' => $roleOfficer->role_id,
            'account_status' => 'active',
        ]);
        $this->citizen = User::factory()->create([
            'role_id' => $roleCitizen->role_id,
            'account_status' => 'active',
        ]);

        // Statuses
        $this->statusDalamTindakLanjut = VerificationStatus::create(['status_name' => 'Dalam Tindak Lanjut']);
        $this->statusTerverifikasi = VerificationStatus::create(['status_name' => 'Terverifikasi']);

        // Reports (membutuhkan facility, issue_category, period — buat minimal)
        $this->reportInFollowUp = $this->createReport($this->statusDalamTindakLanjut->status_id);
        $this->reportVerified = $this->createReport($this->statusTerverifikasi->status_id);
    }

    // ── Otorisasi ──────────────────────────────────────────────

    public function test_guest_cannot_access_assignments(): void
    {
        $this->get(route('follow-up-assignments.index'))
            ->assertRedirect(route('login'));
    }

    public function test_citizen_cannot_access_assignments(): void
    {
        $this->actingAs($this->citizen)
            ->get(route('follow-up-assignments.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.index'))
            ->assertOk();
    }

    public function test_officer_can_access_index(): void
    {
        $this->actingAs($this->officer)
            ->get(route('follow-up-assignments.index'))
            ->assertOk();
    }

    public function test_admin_can_access_ready_reports(): void
    {
        $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.ready-reports'))
            ->assertOk();
    }

    public function test_officer_cannot_access_ready_reports(): void
    {
        $this->actingAs($this->officer)
            ->get(route('follow-up-assignments.ready-reports'))
            ->assertForbidden();
    }

    public function test_admin_can_access_create_form(): void
    {
        $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.create', ['report_id' => $this->reportInFollowUp->report_id]))
            ->assertOk();
    }

    // ── Buat penugasan ─────────────────────────────────────────

    public function test_admin_can_create_assignment(): void
    {
        $this->actingAs($this->admin)
            ->post(route('follow-up-assignments.store'), [
                'report_id'        => $this->reportInFollowUp->report_id,
                'assigned_to'      => $this->officer->id,
                'assignment_notes' => 'Segera tangani jalur kursi roda.',
                'due_date'         => now()->addDays(7)->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('follow_up_assignments', [
            'report_id'         => $this->reportInFollowUp->report_id,
            'assigned_to'       => $this->officer->id,
            'assigned_by'       => $this->admin->id,
            'assignment_status' => 'assigned',
            'assignment_notes'  => 'Segera tangani jalur kursi roda.',
        ]);
    }

    public function test_officer_cannot_create_assignment(): void
    {
        $this->actingAs($this->officer)
            ->post(route('follow-up-assignments.store'), [
                'report_id'   => $this->reportInFollowUp->report_id,
                'assigned_to' => $this->officer->id,
            ])
            ->assertForbidden();
    }

    // ── Validasi ────────────────────────────────────────────────

    public function test_cannot_assign_report_not_in_follow_up_status(): void
    {
        $this->actingAs($this->admin)
            ->post(route('follow-up-assignments.store'), [
                'report_id'   => $this->reportVerified->report_id,
                'assigned_to' => $this->officer->id,
            ])
            ->assertSessionHasErrors('report_id');
    }

    public function test_cannot_assign_to_non_officer_role(): void
    {
        $this->actingAs($this->admin)
            ->post(route('follow-up-assignments.store'), [
                'report_id'   => $this->reportInFollowUp->report_id,
                'assigned_to' => $this->citizen->id,
            ])
            ->assertSessionHasErrors('assigned_to');
    }

    public function test_due_date_cannot_be_in_past(): void
    {
        $this->actingAs($this->admin)
            ->post(route('follow-up-assignments.store'), [
                'report_id'   => $this->reportInFollowUp->report_id,
                'assigned_to' => $this->officer->id,
                'due_date'    => now()->subDay()->toDateString(),
            ])
            ->assertSessionHasErrors('due_date');
    }

    // ── Lihat detail ────────────────────────────────────────────

    public function test_admin_can_see_any_assignment(): void
    {
        $assignment = FollowUpAssignment::create([
            'report_id'         => $this->reportInFollowUp->report_id,
            'assigned_to'       => $this->officer->id,
            'assigned_by'       => $this->admin->id,
            'assignment_status' => 'assigned',
            'assigned_at'       => now(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.show', $assignment->assignment_id))
            ->assertOk();
    }

    public function test_officer_can_see_own_assignment(): void
    {
        $assignment = FollowUpAssignment::create([
            'report_id'         => $this->reportInFollowUp->report_id,
            'assigned_to'       => $this->officer->id,
            'assigned_by'       => $this->admin->id,
            'assignment_status' => 'assigned',
            'assigned_at'       => now(),
        ]);

        $this->actingAs($this->officer)
            ->get(route('follow-up-assignments.show', $assignment->assignment_id))
            ->assertOk();
    }

    public function test_officer_cannot_see_others_assignment(): void
    {
        $otherOfficer = User::factory()->create([
            'role_id' => Role::where('role_name', 'Petugas Tindak Lanjut')->first()->role_id,
            'account_status' => 'active',
        ]);

        $assignment = FollowUpAssignment::create([
            'report_id'         => $this->reportInFollowUp->report_id,
            'assigned_to'       => $otherOfficer->id,
            'assigned_by'       => $this->admin->id,
            'assignment_status' => 'assigned',
            'assigned_at'       => now(),
        ]);

        $this->actingAs($this->officer)
            ->get(route('follow-up-assignments.show', $assignment->assignment_id))
            ->assertForbidden();
    }

    // ── Helper ──────────────────────────────────────────────────

    /**
     * Buat Report minimal untuk testing.
     * Memerlukan tabel pendukung (facility_categories, facilities, issue_categories, reporting_periods).
     */
    private function createReport(int $statusId): Report
    {
        // Buat dependensi minimal jika belum ada
        $facilityCategory = \App\Models\FacilityCategory::firstOrCreate(
            ['category_name' => 'Test Category'],
        );
        $facility = \App\Models\Facility::firstOrCreate(
            ['name' => 'Test Facility'],
            ['category_id' => $facilityCategory->category_id],
        );
        $issueCategory = \App\Models\IssueCategory::firstOrCreate(
            ['category_name' => 'Test Issue'],
        );
        $period = \App\Models\ReportingPeriod::firstOrCreate(
            ['period_name' => 'Test Period'],
            ['start_date' => now()->startOfYear(), 'end_date' => now()->endOfYear()],
        );

        return Report::create([
            'report_number'     => 'RPT-' . fake()->unique()->numerify('######'),
            'user_id'           => $this->citizen->id,
            'facility_id'       => $facility->facility_id,
            'issue_category_id' => $issueCategory->issue_category_id,
            'status_id'         => $statusId,
            'period_id'         => $period->period_id,
            'description'       => 'Laporan tes.',
        ]);
    }
}
