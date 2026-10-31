<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\IssueCategory;
use App\Models\Report;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use App\Models\VerificationStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReportingPeriodManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_manager_creates_period_with_equal_dates_and_only_validated_fields(): void
    {
        $this->asManager();

        $response = $this->post(route('manage.reporting-periods.store'), [
            'period_name' => '  Hari Aksesibilitas  ',
            'start_date' => '2026-12-03',
            'end_date' => '2026-12-03',
            'period_id' => 999,
        ]);

        $period = ReportingPeriod::sole();
        $response->assertRedirect(route('manage.reporting-periods.show', $period))->assertSessionHas('success');
        $this->assertSame('Hari Aksesibilitas', $period->period_name);
        $this->assertSame('2026-12-03', $period->start_date->format('Y-m-d'));
        $this->assertSame('2026-12-03', $period->end_date->format('Y-m-d'));
        $this->assertNotSame(999, $period->getKey());
    }

    public function test_manager_can_view_create_edit_and_detail_pages(): void
    {
        $this->asManager();
        $period = $this->createPeriod(['period_name' => '<script>alert(1)</script>']);

        $this->get(route('manage.reporting-periods.create'))->assertOk()->assertSee('Tanggal mulai');
        $this->get(route('manage.reporting-periods.edit', $period))->assertOk()->assertSee('Simpan periode');
        $this->get(route('manage.reporting-periods.show', $period))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('01/10/2026')->assertSee('31/10/2026');
    }

    public function test_period_listing_searches_and_paginates_with_clear_empty_state(): void
    {
        $this->asManager();
        for ($index = 1; $index <= 17; $index++) {
            $this->createPeriod(['period_name' => sprintf('Periode %02d', $index)]);
        }

        $this->get(route('manage.reporting-periods.index'))->assertOk()
            ->assertViewHas('periods', fn ($periods) => $periods->total() === 17 && $periods->count() === 15);
        $this->get(route('manage.reporting-periods.index', ['q' => 'Periode 17']))->assertOk()
            ->assertSee('Periode 17')->assertDontSee('Periode 01')
            ->assertViewHas('periods', fn ($periods) => $periods->total() === 1);
        $this->get(route('manage.reporting-periods.index', ['q' => 'Tidak ditemukan']))
            ->assertOk()->assertSee('Tidak ada periode yang sesuai.');
    }

    public function test_period_requires_name_and_both_dates(): void
    {
        $this->asManager();

        $this->post(route('manage.reporting-periods.store'), [])
            ->assertSessionHasErrors([
                'period_name' => 'Nama periode wajib diisi.',
                'start_date' => 'Tanggal mulai wajib diisi.',
                'end_date' => 'Tanggal selesai wajib diisi.',
            ]);

        $this->assertDatabaseCount('reporting_periods', 0);
    }

    public static function invalidNames(): array
    {
        return [
            'nama terlalu panjang' => [str_repeat('A', 256), 'Nama periode maksimal 255 karakter.'],
            'nama bukan teks' => [['Oktober'], 'Nama periode harus berupa teks.'],
        ];
    }

    #[DataProvider('invalidNames')]
    public function test_period_rejects_invalid_name(mixed $name, string $message): void
    {
        $this->asManager();

        $this->post(route('manage.reporting-periods.store'), ['period_name' => $name, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31'])
            ->assertSessionHasErrors(['period_name' => $message]);

        $this->assertDatabaseCount('reporting_periods', 0);
    }

    public static function invalidDates(): array
    {
        return [
            'tanggal selesai lebih awal' => ['2026-10-31', '2026-10-01', 'end_date', 'Tanggal selesai harus sama atau sesudah tanggal mulai.'],
            'tanggal mulai tidak valid' => ['2026-02-30', '2026-10-31', 'start_date', 'Tanggal mulai harus berupa tanggal yang valid (YYYY-MM-DD).'],
            'tanggal selesai tidak valid' => ['2026-10-01', 'bukan tanggal', 'end_date', 'Tanggal selesai harus berupa tanggal yang valid (YYYY-MM-DD).'],
        ];
    }

    #[DataProvider('invalidDates')]
    public function test_period_rejects_invalid_date_range(string $start, string $end, string $field, string $message): void
    {
        $this->asManager();

        $this->post(route('manage.reporting-periods.store'), ['period_name' => 'Oktober', 'start_date' => $start, 'end_date' => $end])
            ->assertSessionHasErrors([$field => $message]);

        $this->assertDatabaseCount('reporting_periods', 0);
    }

    public function test_overlapping_periods_are_allowed(): void
    {
        $this->asManager();
        $this->createPeriod();

        $this->post(route('manage.reporting-periods.store'), ['period_name' => 'Triwulan IV', 'start_date' => '2026-10-01', 'end_date' => '2026-12-31'])
            ->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertDatabaseCount('reporting_periods', 2);
    }

    public function test_changing_period_dates_preserves_existing_report_period_link(): void
    {
        $user = $this->asManager();
        $period = $this->createPeriod();
        $report = $this->createReport($user, $period);

        $this->put(route('manage.reporting-periods.update', $period), [
            'period_name' => 'Revisi periode',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
        ])->assertRedirect(route('manage.reporting-periods.show', $period))->assertSessionHas('success');

        $this->assertSame('2026-11-01', $period->fresh()->start_date->format('Y-m-d'));
        $this->assertSame('2026-11-30', $period->fresh()->end_date->format('Y-m-d'));
        $this->assertSame($period->getKey(), $report->fresh()->period_id);
    }

    public function test_used_period_cannot_be_deleted(): void
    {
        $user = $this->asManager();
        $period = $this->createPeriod();
        $report = $this->createReport($user, $period);

        $this->from(route('manage.reporting-periods.show', $period))
            ->delete(route('manage.reporting-periods.destroy', $period))
            ->assertRedirect(route('manage.reporting-periods.show', $period))
            ->assertSessionHas('error', 'Periode digunakan laporan dan tidak dapat dihapus. Hubungan laporan harus tetap dipertahankan.');

        $this->assertModelExists($period);
        $this->assertModelExists($report);
    }

    public function test_unused_period_can_be_deleted(): void
    {
        $this->asManager();
        $period = $this->createPeriod();

        $this->delete(route('manage.reporting-periods.destroy', $period))
            ->assertRedirect(route('manage.reporting-periods.index'))->assertSessionHas('success');

        $this->assertModelMissing($period);
    }

    private function asManager(): User
    {
        $role = Role::firstOrCreate(['role_name' => 'Pengelola Fasilitas']);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);
        $this->actingAs($user);

        return $user;
    }

    private function createPeriod(array $overrides = []): ReportingPeriod
    {
        return ReportingPeriod::create(array_replace([
            'period_name' => 'Oktober',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
        ], $overrides));
    }

    private function createReport(User $user, ReportingPeriod $period): Report
    {
        $facilityCategory = FacilityCategory::create(['category_name' => 'Taman']);
        $facility = Facility::create(['category_id' => $facilityCategory->getKey(), 'name' => 'Taman kota']);
        $issueCategory = IssueCategory::create(['category_name' => 'Jalur terhalang']);
        $status = VerificationStatus::create(['status_name' => 'Menunggu Verifikasi']);

        return Report::create([
            'report_number' => 'PER-001',
            'user_id' => $user->id,
            'facility_id' => $facility->getKey(),
            'issue_category_id' => $issueCategory->getKey(),
            'status_id' => $status->getKey(),
            'period_id' => $period->getKey(),
            'description' => 'Akses terhalang.',
        ]);
    }
}
