<?php

namespace Tests\Feature;

use App\Models\AccessibilityType;
use App\Models\FacilityAccessibility;
use App\Models\IssueCategory;
use App\Models\Report;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use App\Models\VerificationStatus;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesTestData;
use Tests\TestCase;

class CommunityReportingTest extends TestCase
{
    use CreatesTestData, LazilyRefreshDatabase;

    public function test_reporter_can_submit_a_report_with_an_accessibility_item_and_photo(): void
    {
        Storage::fake('public');
        $reporter = $this->createReporter();
        $period = $this->createCurrentPeriod();
        $facility = $this->makeFacility();
        $issue = IssueCategory::create(['category_name' => 'Akses terhalang']);
        $status = VerificationStatus::create(['status_name' => 'Menunggu Verifikasi']);
        $type = AccessibilityType::create(['type_name' => 'Ramp']);
        $item = FacilityAccessibility::create([
            'facility_id' => $facility->getKey(),
            'accessibility_type_id' => $type->getKey(),
            'location_description' => 'Pintu timur',
        ]);

        $this->actingAs($reporter)->get(route('reports.create'))
            ->assertOk()
            ->assertSee(route('reports.facilities.accessibilities', ['facility' => '__FACILITY_ID__']))
            ->assertSee('evidence-preview');
        $this->getJson(route('reports.facilities.accessibilities', $facility))
            ->assertOk()
            ->assertJsonPath('0.id', $item->getKey())
            ->assertJsonPath('0.name', 'Ramp');

        $response = $this->post(route('reports.store'), [
            'report_title' => 'Ramp terhalang',
            'description' => 'Pot tanaman menghalangi ramp di pintu timur.',
            'facility_id' => $facility->getKey(),
            'facility_accessibility_id' => $item->getKey(),
            'issue_category_id' => $issue->getKey(),
            'incident_date' => now()->toDateString(),
            'evidences' => [UploadedFile::fake()->image('ramp.jpg')],
        ]);

        $report = Report::sole();
        $response->assertRedirectToRoute('reports.show', $report);
        $this->assertDatabaseHas('reports', [
            'report_id' => $report->getKey(),
            'user_id' => $reporter->getKey(),
            'period_id' => $period->getKey(),
            'status_id' => $status->getKey(),
            'facility_accessibility_id' => $item->getKey(),
        ]);
        Storage::disk('public')->assertExists($report->evidences()->sole()->file_path);
        $this->get(route('reports.show', $report))->assertOk()->assertSee('Pintu timur');
    }

    public function test_reporting_with_overlapping_periods_asks_the_manager_to_fix_the_dates(): void
    {
        $reporter = $this->createReporter();
        $this->createCurrentPeriod();
        $this->createCurrentPeriod('Periode tumpang tindih');

        $this->actingAs($reporter)->get(route('reports.create'))
            ->assertOk()
            ->assertSee('Tanggal laporan cocok dengan beberapa periode pelaporan.');

        $this->from(route('reports.create'))->post(route('reports.store'))
            ->assertRedirectToRoute('reports.create')
            ->assertSessionHasErrors('period_id');

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_report_list_combines_search_status_and_order_without_showing_other_users_reports(): void
    {
        $reporter = $this->createReporter();
        $otherReporter = $this->createReporter();
        $facility = $this->makeFacility();
        $category = IssueCategory::create(['category_name' => 'Akses terhalang']);
        $filteredStatus = VerificationStatus::create(['status_name' => 'Terverifikasi']);
        $otherStatus = VerificationStatus::create(['status_name' => 'Menunggu Verifikasi']);
        $common = [
            'user_id' => $reporter->getKey(),
            'facility_id' => $facility->getKey(),
            'issue_category_id' => $category->getKey(),
            'period_id' => $this->createCurrentPeriod()->getKey(),
            'status_id' => $filteredStatus->getKey(),
            'report_title' => 'Ramp terhalang',
            'description' => 'Jalur menuju pintu terhalang.',
        ];
        Report::create(['report_number' => 'LAP-BARU', 'created_at' => now()] + $common);
        Report::create(['report_number' => 'LAP-LAMA', 'created_at' => now()->subDay()] + $common);
        Report::create(['report_number' => 'LAP-WARGA-LAIN', 'user_id' => $otherReporter->getKey()] + $common);
        Report::create(['report_number' => 'LAP-BEDA-STATUS', 'status_id' => $otherStatus->getKey()] + $common);
        Report::create(['report_number' => 'LAP-BEDA-JUDUL', 'report_title' => 'Lift rusak'] + $common);

        $response = $this->actingAs($reporter)->get(route('reports.index', [
            'search' => 'Ramp',
            'status_id' => $filteredStatus->getKey(),
            'sort' => 'oldest',
        ]));

        $response->assertOk()
            ->assertSeeInOrder(['LAP-LAMA', 'LAP-BARU'])
            ->assertDontSee('LAP-WARGA-LAIN')
            ->assertDontSee('LAP-BEDA-STATUS')
            ->assertDontSee('LAP-BEDA-JUDUL');
    }

    private function createReporter(): User
    {
        $role = Role::firstOrCreate(['role_name' => Role::PUBLIC]);

        return User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);
    }

    private function createCurrentPeriod(string $name = 'Periode uji'): ReportingPeriod
    {
        return ReportingPeriod::create([
            'period_name' => $name,
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);
    }
}
