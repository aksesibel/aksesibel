<?php

namespace Tests\Feature;

use App\Models\FollowUpAssignment;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use App\Models\Verification;
use App\Models\VerificationStatus;
use Carbon\Carbon;
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

    public function test_facility_manager_can_access_and_create_assignment(): void
    {
        $roleManager = Role::firstOrCreate(['role_name' => 'Pengelola Fasilitas']);
        $manager = User::factory()->create([
            'role_id'        => $roleManager->role_id,
            'account_status' => 'active',
        ]);

        $this->actingAs($manager)
            ->get(route('follow-up-assignments.ready-reports'))
            ->assertOk();

        $this->actingAs($manager)
            ->post(route('follow-up-assignments.store'), [
                'report_id'        => $this->reportInFollowUp->report_id,
                'assigned_to'      => $this->officer->id,
                'assignment_notes' => 'Instruksi pengelola fasilitas.',
                'due_date'         => now()->addDays(5)->toDateString(),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('follow_up_assignments', [
            'report_id'   => $this->reportInFollowUp->report_id,
            'assigned_by' => $manager->id,
            'assigned_to' => $this->officer->id,
        ]);
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

    public function test_inactive_admin_cannot_access_index(): void
    {
        $this->admin->update(['account_status' => 'inactive']);

        $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.index'))
            ->assertForbidden();
    }

    public function test_inactive_officer_cannot_access_index(): void
    {
        $this->officer->update(['account_status' => 'inactive']);

        $this->actingAs($this->officer)
            ->get(route('follow-up-assignments.index'))
            ->assertForbidden();
    }

    public function test_inactive_admin_cannot_access_ready_reports(): void
    {
        $this->admin->update(['account_status' => 'inactive']);

        $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.ready-reports'))
            ->assertForbidden();
    }

    public function test_inactive_admin_cannot_access_create_form(): void
    {
        $this->admin->update(['account_status' => 'inactive']);

        $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.create'))
            ->assertForbidden();
    }

    public function test_inactive_officer_cannot_access_show(): void
    {
        $assignment = FollowUpAssignment::create([
            'report_id'         => $this->reportInFollowUp->report_id,
            'assigned_to'       => $this->officer->id,
            'assigned_by'       => $this->admin->id,
            'assignment_status' => 'assigned',
            'assigned_at'       => now(),
        ]);

        $this->officer->update(['account_status' => 'inactive']);

        $this->actingAs($this->officer)
            ->get(route('follow-up-assignments.show', $assignment->assignment_id))
            ->assertForbidden();
    }

    public function test_ready_reports_displays_actual_reporter_and_facility_data(): void
    {
        $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.ready-reports', ['selected_id' => $this->reportInFollowUp->report_id]))
            ->assertOk()
            ->assertSee($this->citizen->name)
            ->assertDontSee('Rina W. (privat)')
            ->assertDontSee('Pintu masuk utara')
            ->assertDontSee('jalur-pemandu.jpg');
    }

    public function test_ready_reports_displays_not_recorded_when_verification_result_is_null_or_empty(): void
    {
        Verification::create([
            'report_id'           => $this->reportInFollowUp->report_id,
            'verifier_id'         => $this->admin->id,
            'verification_result' => null,
            'notes'               => 'Menunggu verifikasi lapangan',
            'verified_at'         => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.ready-reports', ['selected_id' => $this->reportInFollowUp->report_id]));

        $response->assertOk()
            ->assertSee('Hasil verifikasi belum dicatat')
            ->assertSee('bg-gray-100 text-gray-700')
            ->assertDontSee('bg-green-100 text-green-800');
    }

    public function test_ready_reports_orders_verifications_by_verified_at_precedence(): void
    {
        $vNewer = Verification::create([
            'report_id'           => $this->reportInFollowUp->report_id,
            'verifier_id'         => $this->admin->id,
            'verification_result' => 'Hasil Terbaru Berdasarkan Tanggal',
            'notes'               => 'Verifikasi tanggal baru',
            'verified_at'         => Carbon::parse('2026-11-20 10:00:00'),
        ]);

        $vOlder = Verification::create([
            'report_id'           => $this->reportInFollowUp->report_id,
            'verifier_id'         => $this->admin->id,
            'verification_result' => 'Hasil Lama Berdasarkan Tanggal',
            'notes'               => 'Verifikasi tanggal lama',
            'verified_at'         => Carbon::parse('2026-11-10 10:00:00'),
        ]);

        $this->assertTrue($vOlder->verification_id > $vNewer->verification_id);

        $response = $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.ready-reports', ['selected_id' => $this->reportInFollowUp->report_id]));

        $response->assertOk()
            ->assertSee('Hasil Terbaru Berdasarkan Tanggal')
            ->assertDontSee('Hasil Lama Berdasarkan Tanggal');
    }

    public function test_ready_reports_uses_verification_id_as_tie_breaker_for_same_verified_at(): void
    {
        $sameTime = Carbon::parse('2026-11-20 12:00:00');

        $v1 = Verification::create([
            'report_id'           => $this->reportInFollowUp->report_id,
            'verifier_id'         => $this->admin->id,
            'verification_result' => 'Verifikasi ID Lama',
            'notes'               => 'Catatan 1',
            'verified_at'         => $sameTime,
        ]);

        $v2 = Verification::create([
            'report_id'           => $this->reportInFollowUp->report_id,
            'verifier_id'         => $this->admin->id,
            'verification_result' => 'Verifikasi ID Baru Tie Breaker',
            'notes'               => 'Catatan 2',
            'verified_at'         => $sameTime,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.ready-reports', ['selected_id' => $this->reportInFollowUp->report_id]));

        $response->assertOk()
            ->assertSee('Verifikasi ID Baru Tie Breaker')
            ->assertDontSee('Verifikasi ID Lama');
    }

    public function test_ready_reports_prioritizes_dated_verification_over_undated(): void
    {
        $vDated = Verification::create([
            'report_id'           => $this->reportInFollowUp->report_id,
            'verifier_id'         => $this->admin->id,
            'verification_result' => 'Verifikasi Memiliki Tanggal',
            'notes'               => 'Catatan bertanggal',
            'verified_at'         => Carbon::parse('2026-11-15 09:00:00'),
        ]);

        $vUndated = Verification::create([
            'report_id'           => $this->reportInFollowUp->report_id,
            'verifier_id'         => $this->admin->id,
            'verification_result' => 'Verifikasi Tanpa Tanggal ID Lebih Tinggi',
            'notes'               => 'Catatan tanpa tanggal',
            'verified_at'         => null,
        ]);

        $this->assertTrue($vUndated->verification_id > $vDated->verification_id);

        $response = $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.ready-reports', ['selected_id' => $this->reportInFollowUp->report_id]));

        $response->assertOk()
            ->assertSee('Verifikasi Memiliki Tanggal')
            ->assertDontSee('Verifikasi Tanpa Tanggal ID Lebih Tinggi');
    }

    public function test_ready_reports_picks_highest_id_when_all_verifications_are_undated(): void
    {
        $v1 = Verification::create([
            'report_id'           => $this->reportInFollowUp->report_id,
            'verifier_id'         => $this->admin->id,
            'verification_result' => 'Undated Verifikasi ID 1',
            'verified_at'         => null,
        ]);

        $v2 = Verification::create([
            'report_id'           => $this->reportInFollowUp->report_id,
            'verifier_id'         => $this->admin->id,
            'verification_result' => 'Undated Verifikasi ID 2 Fallback',
            'verified_at'         => null,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.ready-reports', ['selected_id' => $this->reportInFollowUp->report_id]));

        $response->assertOk()
            ->assertSee('Undated Verifikasi ID 2 Fallback')
            ->assertDontSee('Undated Verifikasi ID 1');
    }

    public function test_ready_reports_converts_utc_verified_at_to_asia_jakarta_timezone(): void
    {
        Verification::create([
            'report_id'           => $this->reportInFollowUp->report_id,
            'verifier_id'         => $this->admin->id,
            'verification_result' => 'Valid',
            'notes'               => 'Lolos verifikasi',
            'verified_at'         => Carbon::parse('2026-11-22 10:00:00', 'UTC'),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.ready-reports', ['selected_id' => $this->reportInFollowUp->report_id]));

        $response->assertOk()
            ->assertSee('22 Nov 2026, 17:00 WIB')
            ->assertDontSee('22 Nov 2026, 10:00 WIB');
    }

    public function test_ready_reports_renders_dash_without_wib_when_verified_at_is_null(): void
    {
        Verification::create([
            'report_id'           => $this->reportInFollowUp->report_id,
            'verifier_id'         => $this->admin->id,
            'verification_result' => 'Sesuai',
            'notes'               => 'Lolos verifikasi tanpa tanggal',
            'verified_at'         => null,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('follow-up-assignments.ready-reports', ['selected_id' => $this->reportInFollowUp->report_id]));

        $response->assertOk()
            ->assertDontSee('- WIB');
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
