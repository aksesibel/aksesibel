<?php

namespace Tests\Feature;

use App\Models\FacilityCategory;
use App\Models\IssueCategory;
use App\Models\Report;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use App\Models\VerificationStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class StagingModuleAccessTest extends TestCase
{
    use CreatesTestData, LazilyRefreshDatabase;

    public function test_admin_returns_to_verification_after_signing_in(): void
    {
        $admin = $this->createUserForRole(Role::ADMIN);
        $this->get(route('verifikasi.status'))->assertRedirectToRoute('login');

        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirectToRoute('verifikasi.status');

        $this->get(route('verifikasi.status'))
            ->assertOk()
            ->assertSee($admin->name)
            ->assertSee(route('logout'));
    }

    public function test_admin_returns_to_facility_management_after_signing_in(): void
    {
        $admin = $this->createUserForRole(Role::ADMIN);
        $this->get(route('admin.facilities.index'))->assertRedirectToRoute('login');

        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirectToRoute('admin.facilities.index');

        $this->get(route('admin.facilities.index'))->assertOk();
        $this->get(route('admin.categories.index'))->assertOk();
    }

    public function test_follow_up_officer_lands_on_assignments_after_signing_in(): void
    {
        $officer = $this->createUserForRole(Role::FOLLOW_UP_OFFICER);

        $this->post(route('login.store'), ['email' => $officer->email, 'password' => 'password'])
            ->assertRedirectToRoute('follow-up-assignments.index');

        $this->get(route('follow-up-assignments.index'))
            ->assertOk()
            ->assertSee(route('logout'));
    }

    public function test_public_user_can_browse_facilities_but_cannot_manage_facilities_or_verification(): void
    {
        $user = $this->createUserForRole(Role::PUBLIC);
        $pending = VerificationStatus::create(['status_name' => 'Menunggu Verifikasi']);
        $report = $this->createReport($user, $pending);

        $this->actingAs($user)->get(route('facilities.index'))
            ->assertOk()
            ->assertDontSee(route('admin.facilities.index'));
        $this->get(route('admin.categories.index'))->assertForbidden();
        $this->post(route('admin.facilities.store'))->assertForbidden();
        $this->get(route('verifikasi.status'))->assertForbidden();
        $this->post(route('verifikasi.status.update', $report))->assertForbidden();
    }

    public function test_admin_records_status_change_with_the_signed_in_actor(): void
    {
        $admin = $this->createUserForRole(Role::ADMIN);
        $pending = VerificationStatus::create(['status_name' => 'Menunggu Verifikasi']);
        $verified = VerificationStatus::create(['status_name' => 'Terverifikasi']);
        $report = $this->createReport($this->createUserForRole(Role::PUBLIC), $pending);
        $payload = ['status_id' => $verified->getKey(), 'notes' => 'Kendala dikonfirmasi.'];

        $this->actingAs($admin)->get(route('verifikasi.antrean'))
            ->assertOk()
            ->assertSee(route('verifikasi.status', ['kelola' => $report->getKey()]));

        $this->post(route('verifikasi.status.update', $report), $payload)
            ->assertRedirectToRoute('verifikasi.status', ['kelola' => $report->getKey()]);

        $this->assertDatabaseHas('reports', ['report_id' => $report->getKey(), 'status_id' => $verified->getKey()]);
        $verification = $report->verifications()->sole();
        $this->assertSame($admin->getKey(), $verification->verifier_id);
        $this->assertDatabaseHas('verification_histories', [
            'verification_id' => $verification->getKey(),
            'actor_id' => $admin->getKey(),
            'previous_status_id' => $pending->getKey(),
            'new_status_id' => $verified->getKey(),
            'notes' => $payload['notes'],
        ]);
        $this->get(route('verifikasi.riwayat'))->assertOk()->assertSee($payload['notes']);

        $this->post(route('verifikasi.status.update', $report), $payload)->assertRedirect();
        $this->assertDatabaseCount('verification_histories', 1);
    }

    public function test_admin_can_update_a_report_selected_from_a_filtered_page(): void
    {
        $admin = $this->createUserForRole(Role::ADMIN);
        $reporter = $this->createUserForRole(Role::PUBLIC);
        $pending = VerificationStatus::create(['status_name' => 'Menunggu Verifikasi']);
        $verified = VerificationStatus::create(['status_name' => 'Terverifikasi']);
        $report = $this->createReport($reporter, $pending);
        $report->update(['report_title' => 'Ramp simulasi', 'created_at' => now()->subDay()]);

        foreach (range(2, 6) as $number) {
            $report->replicate()->fill(['report_number' => 'AKS-UJI-00'.$number])->save();
        }

        $listQuery = ['search' => 'Ramp simulasi', 'status' => $pending->getKey(), 'page' => 2];
        $selection = [...$listQuery, 'kelola' => $report->getKey()];
        $formAction = route('verifikasi.status.update', [$report, ...$listQuery]);

        $this->actingAs($admin)->get(route('verifikasi.status', $listQuery))
            ->assertOk()
            ->assertSee(route('verifikasi.status', $selection).'#ubah-status');

        $this->get(route('verifikasi.status', $selection))
            ->assertOk()
            ->assertViewHas('detail', fn (Report $selected) => $selected->is($report))
            ->assertSee($formAction);

        $this->post($formAction, ['status_id' => $verified->getKey(), 'notes' => 'Bukti sesuai.'])
            ->assertRedirectToRoute('verifikasi.status', $selection)
            ->assertSessionHas('success', 'Status laporan berhasil diperbarui.');

        $this->assertDatabaseHas('reports', ['report_id' => $report->getKey(), 'status_id' => $verified->getKey()]);

        $this->get(route('verifikasi.status', $selection))
            ->assertOk()
            ->assertViewHas('detail', fn (Report $selected) => $selected->is($report) && $selected->status->is($verified))
            ->assertViewHas('laporan', fn ($reports) => $reports->currentPage() === 1 && $reports->count() === 5)
            ->assertViewHas('filters', ['search' => 'Ramp simulasi', 'status' => $pending->getKey()]);
    }

    private function createReport(User $reporter, VerificationStatus $pending): Report
    {
        $category = FacilityCategory::create(['category_name' => 'Taman', 'slug' => 'taman']);
        $issue = IssueCategory::create(['category_name' => 'Akses terhalang']);
        $facility = $this->makeFacility(['category_id' => $category->getKey()]);
        $period = ReportingPeriod::create([
            'period_name' => 'Periode uji',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        return Report::create([
            'report_number' => 'AKS-UJI-001',
            'user_id' => $reporter->getKey(),
            'facility_id' => $facility->getKey(),
            'issue_category_id' => $issue->getKey(),
            'status_id' => $pending->getKey(),
            'period_id' => $period->getKey(),
            'description' => 'Jalur masuk terhalang pot tanaman.',
        ]);
    }

    private function createUserForRole(string $roleName): User
    {
        $role = Role::create(['role_name' => $roleName]);

        return User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);
    }
}
