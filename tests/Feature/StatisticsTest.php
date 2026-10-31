<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\FollowUpAssignment;
use App\Models\FollowUpProgress;
use App\Models\IssueCategory;
use App\Models\Report;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use App\Models\Verification;
use App\Models\VerificationHistory;
use App\Models\VerificationStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_counts_each_report_once_despite_multiple_evidence_history_and_progress(): void
    {
        $data = $this->reportData();
        $report = $data['reports'][0];
        $report->evidences()->createMany([
            ['file_path' => 'evidence/a.jpg'],
            ['file_path' => 'evidence/b.jpg'],
        ]);
        $verification = Verification::create([
            'report_id' => $report->report_id,
            'verifier_id' => $data['manager']->id,
        ]);
        foreach (['Diperiksa', 'Dikonfirmasi'] as $notes) {
            VerificationHistory::create([
                'verification_id' => $verification->verification_id,
                'actor_id' => $data['manager']->id,
                'new_status_id' => $data['statuses'][0]->status_id,
                'notes' => $notes,
            ]);
        }
        $assignment = FollowUpAssignment::create([
            'report_id' => $report->report_id,
            'assigned_to' => $data['manager']->id,
            'assigned_by' => $data['manager']->id,
        ]);
        foreach (['Awal', 'Lanjutan'] as $description) {
            FollowUpProgress::create([
                'assignment_id' => $assignment->assignment_id,
                'actor_id' => $data['manager']->id,
                'action_description' => $description,
            ]);
        }
        $before = Report::query()->orderBy('report_id')->get()->toArray();

        $response = $this->actingAs($data['manager'])->get(route('manage.statistics'));

        $response->assertOk()->assertViewHas('facilityTotal', 3)->assertViewHas('reportTotal', 5)
            ->assertSee('Jumlah fasilitas — total global');
        $this->assertSame(2, (int) $response->viewData('byStatus')->firstWhere('label', 'Menunggu')->total);
        $this->assertSame(3, (int) $response->viewData('byStatus')->firstWhere('label', 'Selesai')->total);
        $this->assertSame(3, (int) $response->viewData('byIssue')->firstWhere('label', 'Ramp rusak')->total);
        $this->assertSame(3, (int) $response->viewData('byFacility')->firstWhere('label', 'Taman')->total);
        $this->assertSame(5, $response->viewData('recap')->total());
        $this->assertSame($before, Report::query()->orderBy('report_id')->get()->toArray());
    }

    /** @return array<string, array{array<string, int>, int}> */
    public static function filterCombinations(): array
    {
        return [
            'periode' => [['period_id' => 0], 3],
            'fasilitas' => [['category_id' => 0], 3],
            'kendala' => [['issue_category_id' => 0], 3],
            'periode dan fasilitas' => [['period_id' => 0, 'category_id' => 0], 2],
            'periode dan kendala' => [['period_id' => 0, 'issue_category_id' => 0], 2],
            'fasilitas dan kendala' => [['category_id' => 0, 'issue_category_id' => 0], 2],
            'ketiga filter' => [['period_id' => 0, 'category_id' => 0, 'issue_category_id' => 0], 1],
        ];
    }

    #[DataProvider('filterCombinations')]
    public function test_applies_filters_consistently_to_cards_charts_and_recap(array $dimensions, int $expected): void
    {
        $data = $this->reportData();
        $ids = [
            'period_id' => $data['periods'][0]->period_id,
            'category_id' => $data['categories'][0]->category_id,
            'issue_category_id' => $data['issues'][0]->issue_category_id,
        ];
        $filters = array_intersect_key($ids, $dimensions);

        $response = $this->actingAs($data['manager'])->get(route('manage.statistics', $filters));

        $response->assertOk()->assertViewHas('reportTotal', $expected)->assertViewHas('facilityTotal', 3);
        $this->assertSame($expected, (int) $response->viewData('byStatus')->sum('total'));
        $this->assertSame($expected, (int) $response->viewData('byIssue')->sum('total'));
        $this->assertSame($expected, (int) $response->viewData('byFacility')->sum('total'));
        $this->assertSame($expected, (int) $response->viewData('recap')->getCollection()->sum('total'));
    }

    public function test_period_filter_uses_report_relationship_even_outside_period_dates(): void
    {
        $data = $this->reportData();

        $response = $this->actingAs($data['manager'])->get(route('manage.statistics', [
            'period_id' => $data['periods'][0]->period_id,
            'category_id' => $data['categories'][0]->category_id,
            'issue_category_id' => $data['issues'][0]->issue_category_id,
        ]));

        $response->assertOk()->assertViewHas('reportTotal', 1);
        $this->assertSame('Menunggu', $response->viewData('recap')->first()->status_name);
    }

    public function test_displays_empty_state_and_reset_filter_without_sample_numbers(): void
    {
        $manager = $this->manager();

        $response = $this->actingAs($manager)->get(route('manage.statistics'));

        $response->assertOk()->assertViewHas('reportTotal', 0)->assertViewHas('facilityTotal', 0)
            ->assertSee('Belum ada laporan yang sesuai dengan filter.')
            ->assertSee('Reset filter')->assertSee('Belum ada data laporan.');
        $this->assertSame(0, $response->viewData('recap')->total());
    }

    public function test_paginates_recap_groups_and_keeps_filter_in_page_links(): void
    {
        $data = $this->reportData();
        foreach (range(1, 16) as $index) {
            $issue = IssueCategory::create(['category_name' => 'Kendala '.$index, 'is_active' => true]);
            $this->report($data['manager'], $data['facilities'][0], $issue, $data['periods'][0], $data['statuses'][0]);
        }
        $filters = ['period_id' => $data['periods'][0]->period_id, 'category_id' => $data['categories'][0]->category_id];

        $response = $this->actingAs($data['manager'])->get(route('manage.statistics', [...$filters, 'page' => 2]));

        $response->assertOk()->assertViewHas('reportTotal', 18);
        $recap = $response->viewData('recap');
        $this->assertSame(18, $recap->total());
        $this->assertSame(2, $recap->currentPage());
        $this->assertCount(3, $recap->items());
        $this->assertStringContainsString('period_id='.$filters['period_id'], $recap->url(1));
        $this->assertStringContainsString('category_id='.$filters['category_id'], $recap->url(1));
        $this->assertSame(18, (int) $response->viewData('byIssue')->sum('total'));
    }

    public function test_offers_first_page_when_recap_page_is_out_of_range(): void
    {
        $data = $this->reportData();

        $response = $this->actingAs($data['manager'])->get(route('manage.statistics', [
            'period_id' => $data['periods'][0]->period_id,
            'page' => 999,
        ]));

        $response->assertOk()->assertViewHas('reportTotal', 3)
            ->assertSee('Halaman ini tidak memiliki rekap.')
            ->assertSee('Kembali ke halaman pertama.')
            ->assertDontSee('Belum ada laporan yang sesuai dengan filter.');
    }

    /** @return array<string, array{string, string|int}> */
    public static function invalidFilters(): array
    {
        return [
            'periode tidak tersedia' => ['period_id', 99999],
            'fasilitas tidak tersedia' => ['category_id', 99999],
            'kendala tidak tersedia' => ['issue_category_id', 99999],
            'bukan ID' => ['period_id', '1 OR 1=1'],
            'halaman nol' => ['page', 0],
        ];
    }

    #[DataProvider('invalidFilters')]
    public function test_rejects_invalid_filter_instead_of_displaying_unfiltered_statistics(string $field, string|int $value): void
    {
        $manager = $this->manager();

        $response = $this->actingAs($manager)->get(route('manage.statistics', [$field => $value]));

        $response->assertRedirect()->assertSessionHasErrors($field);
    }

    public function test_keeps_inactive_categories_in_historical_statistics_and_escapes_labels(): void
    {
        $data = $this->reportData();
        $data['categories'][0]->update(['category_name' => '<script>alert("fasilitas")</script>', 'is_active' => false]);
        $data['issues'][0]->update(['category_name' => '<script>alert("kendala")</script>', 'is_active' => false]);

        $response = $this->actingAs($data['manager'])->get(route('manage.statistics', [
            'category_id' => $data['categories'][0]->category_id,
            'issue_category_id' => $data['issues'][0]->issue_category_id,
        ]));

        $response->assertOk()->assertViewHas('reportTotal', 2)->assertSee('(nonaktif)')
            ->assertSee('<script>alert("fasilitas")</script>')->assertSee('<script>alert("kendala")</script>')
            ->assertDontSee('<script>alert("fasilitas")</script>', false)->assertDontSee('<script>alert("kendala")</script>', false);
    }

    public function test_explains_missing_report_dependency_without_inventing_totals(): void
    {
        $manager = $this->manager();
        Schema::rename('reports', 'reports_unavailable');

        try {
            $response = $this->actingAs($manager)->get(route('manage.statistics'));

            $response->assertOk()->assertSee('Statistik belum dapat ditampilkan')
                ->assertSee('reports')->assertSee('Data laporan dari modul pelaporan belum tersedia.')
                ->assertDontSee('Jumlah laporan sesuai filter');
        } finally {
            Schema::rename('reports_unavailable', 'reports');
        }
    }

    private function manager(): User
    {
        $role = Role::firstOrCreate(['role_name' => 'Pengelola Fasilitas']);

        return User::factory()->create(['role_id' => $role->role_id, 'account_status' => 'active']);
    }

    /** @return array{manager: User, categories: array<FacilityCategory>, issues: array<IssueCategory>, periods: array<ReportingPeriod>, statuses: array<VerificationStatus>, facilities: array<Facility>, reports: array<Report>} */
    private function reportData(): array
    {
        $manager = $this->manager();
        $categories = [
            FacilityCategory::create(['category_name' => 'Taman', 'is_active' => true]),
            FacilityCategory::create(['category_name' => 'Sekolah', 'is_active' => true]),
        ];
        $issues = [
            IssueCategory::create(['category_name' => 'Ramp rusak', 'is_active' => true]),
            IssueCategory::create(['category_name' => 'Jalur terhalang', 'is_active' => true]),
        ];
        $periods = [
            ReportingPeriod::create(['period_name' => 'Januari', 'start_date' => '2026-01-01', 'end_date' => '2026-01-31']),
            ReportingPeriod::create(['period_name' => 'Februari', 'start_date' => '2026-02-01', 'end_date' => '2026-02-28']),
        ];
        $statuses = [
            VerificationStatus::create(['status_name' => 'Menunggu']),
            VerificationStatus::create(['status_name' => 'Selesai']),
        ];
        $facilities = [
            Facility::create(['category_id' => $categories[0]->category_id, 'name' => 'Taman A']),
            Facility::create(['category_id' => $categories[1]->category_id, 'name' => 'Sekolah B']),
            Facility::create(['category_id' => $categories[1]->category_id, 'name' => 'Sekolah C', 'facility_status' => 'inactive']),
        ];
        $reports = [
            $this->report($manager, $facilities[0], $issues[0], $periods[0], $statuses[0], '2025-12-31 23:59:59'),
            $this->report($manager, $facilities[0], $issues[1], $periods[0], $statuses[1], '2026-01-15 10:00:00'),
            $this->report($manager, $facilities[0], $issues[0], $periods[1], $statuses[1], '2026-01-15 11:00:00'),
            $this->report($manager, $facilities[1], $issues[0], $periods[0], $statuses[0], '2026-01-31 23:59:59'),
            $this->report($manager, $facilities[1], $issues[1], $periods[1], $statuses[1], '2026-02-01 00:00:00'),
        ];

        return compact('manager', 'categories', 'issues', 'periods', 'statuses', 'facilities', 'reports');
    }

    private function report(User $user, Facility $facility, IssueCategory $issue, ReportingPeriod $period, VerificationStatus $status, string $createdAt = '2026-01-01 12:00:00'): Report
    {
        return Report::create([
            'report_number' => 'REPORT-'.fake()->unique()->numerify('########'),
            'user_id' => $user->id,
            'facility_id' => $facility->facility_id,
            'issue_category_id' => $issue->issue_category_id,
            'status_id' => $status->status_id,
            'period_id' => $period->period_id,
            'description' => 'Kendala aksesibilitas yang perlu diperbaiki.',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
