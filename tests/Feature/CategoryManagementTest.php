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
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public static function categoryTypes(): array
    {
        return [
            'kategori fasilitas' => [FacilityCategory::class, 'manage.facility-categories'],
            'kategori kendala' => [IssueCategory::class, 'manage.issue-categories'],
        ];
    }

    #[DataProvider('categoryTypes')]
    public function test_manager_creates_trimmed_category_from_only_validated_fields(string $modelClass, string $routePrefix): void
    {
        $this->asManager();

        $response = $this->post(route($routePrefix.'.store'), [
            'category_name' => '  Transportasi  ',
            'description' => '  Akses angkutan umum  ',
            'is_active' => '1',
            'category_id' => 900,
            'issue_category_id' => 900,
        ]);

        $category = $modelClass::sole();
        $response->assertRedirect(route($routePrefix.'.show', $category))->assertSessionHas('success');
        $this->assertSame('Transportasi', $category->category_name);
        $this->assertSame('Akses angkutan umum', $category->description);
        $this->assertTrue($category->is_active);
        $this->assertNotSame(900, $category->getKey());
    }

    #[DataProvider('categoryTypes')]
    public function test_manager_can_view_create_edit_and_detail_pages(string $modelClass, string $routePrefix): void
    {
        $this->asManager();
        $category = $modelClass::create(['category_name' => 'Akses <script>alert(1)</script>', 'description' => '<script>alert(2)</script>', 'is_active' => true]);

        $this->get(route($routePrefix.'.create'))->assertOk()->assertSee('Nama kategori');
        $this->get(route($routePrefix.'.edit', $category))->assertOk()->assertSee('Simpan kategori');
        $this->get(route($routePrefix.'.show', $category))
            ->assertOk()->assertSee('Akses &lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('&lt;script&gt;alert(2)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('<script>alert(2)</script>', false);
    }

    #[DataProvider('categoryTypes')]
    public function test_category_listing_searches_filters_and_paginates(string $modelClass, string $routePrefix): void
    {
        $this->asManager();
        for ($index = 1; $index <= 17; $index++) {
            $modelClass::create(['category_name' => sprintf('Kategori %02d', $index), 'is_active' => $index !== 17]);
        }

        $this->get(route($routePrefix.'.index'))->assertOk()
            ->assertViewHas('categories', fn ($categories) => $categories->total() === 17 && $categories->count() === 15);
        $this->get(route($routePrefix.'.index', ['q' => 'Kategori', 'status' => 'inactive']))
            ->assertOk()->assertSee('Kategori 17')->assertDontSee('Kategori 01')
            ->assertViewHas('categories', fn ($categories) => $categories->total() === 1);
        $this->get(route($routePrefix.'.index', ['q' => 'Tidak ditemukan']))
            ->assertOk()->assertSee('Tidak ada kategori yang sesuai.');
    }

    #[DataProvider('categoryTypes')]
    public function test_category_name_is_required_and_status_must_be_boolean(string $modelClass, string $routePrefix): void
    {
        $this->asManager();

        $this->post(route($routePrefix.'.store'), ['category_name' => '  ', 'is_active' => 'aktif'])
            ->assertSessionHasErrors([
                'category_name' => 'Nama kategori wajib diisi.',
                'is_active' => 'Status kategori harus aktif atau nonaktif.',
            ]);
        $this->assertSame(0, $modelClass::count());
    }

    public static function invalidCategoryFields(): array
    {
        $cases = [];
        $payloads = [
            'nama terlalu panjang' => ['category_name', str_repeat('A', 256), 'Nama kategori maksimal 255 karakter.'],
            'nama bukan teks' => ['category_name', ['Akses'], 'Nama kategori harus berupa teks.'],
            'deskripsi bukan teks' => ['description', ['Akses'], 'Deskripsi harus berupa teks.'],
            'status kosong' => ['is_active', null, 'Status kategori wajib dipilih.'],
        ];
        foreach (self::categoryTypes() as $type => [$modelClass, $routePrefix]) {
            foreach ($payloads as $label => [$field, $value, $message]) {
                $cases[$type.' '.$label] = [$modelClass, $routePrefix, $field, $value, $message];
            }
        }

        return $cases;
    }

    #[DataProvider('invalidCategoryFields')]
    public function test_category_rejects_invalid_field_values(string $modelClass, string $routePrefix, string $field, mixed $value, string $message): void
    {
        $this->asManager();

        $this->post(route($routePrefix.'.store'), array_replace(['category_name' => 'Akses', 'is_active' => 1], [$field => $value]))
            ->assertSessionHasErrors([$field => $message]);

        $this->assertSame(0, $modelClass::count());
    }

    #[DataProvider('categoryTypes')]
    public function test_duplicate_trimmed_name_is_rejected_on_create_and_update(string $modelClass, string $routePrefix): void
    {
        $this->asManager();
        $existing = $modelClass::create(['category_name' => 'Pendidikan', 'is_active' => true]);
        $other = $modelClass::create(['category_name' => 'Kesehatan', 'is_active' => true]);

        $this->post(route($routePrefix.'.store'), ['category_name' => '  Pendidikan  ', 'is_active' => 1])
            ->assertSessionHasErrors(['category_name' => 'Nama kategori sudah digunakan.']);
        $this->put(route($routePrefix.'.update', $other), ['category_name' => 'Pendidikan', 'is_active' => 1])
            ->assertSessionHasErrors(['category_name' => 'Nama kategori sudah digunakan.']);

        $this->assertSame('Kesehatan', $other->fresh()->category_name);
        $this->assertSame('Pendidikan', $existing->fresh()->category_name);
        $this->assertSame(2, $modelClass::count());
    }

    #[DataProvider('categoryTypes')]
    public function test_category_updates_without_timestamps_and_can_be_deactivated_and_reactivated(string $modelClass, string $routePrefix): void
    {
        $this->asManager();
        $category = $modelClass::create(['category_name' => 'Pendidikan', 'is_active' => true]);

        $this->put(route($routePrefix.'.update', $category), ['category_name' => ' Pendidikan ', 'description' => null, 'is_active' => 0])
            ->assertRedirect(route($routePrefix.'.show', $category))->assertSessionHas('success');
        $this->assertFalse($category->fresh()->is_active);
        $this->assertSame(0, $modelClass::active()->count());

        $this->put(route($routePrefix.'.update', $category), ['category_name' => 'Pendidikan inklusif', 'is_active' => 1])
            ->assertRedirect(route($routePrefix.'.show', $category));
        $this->assertTrue($category->fresh()->is_active);
        $this->assertSame('Pendidikan inklusif', $category->fresh()->category_name);
        $this->assertSame(1, $modelClass::active()->count());
    }

    #[DataProvider('categoryTypes')]
    public function test_unused_category_can_be_deleted(string $modelClass, string $routePrefix): void
    {
        $this->asManager();
        $category = $modelClass::create(['category_name' => 'Tidak terpakai', 'is_active' => false]);

        $this->delete(route($routePrefix.'.destroy', $category))
            ->assertRedirect(route($routePrefix.'.index'))->assertSessionHas('success');

        $this->assertModelMissing($category);
    }

    public function test_used_facility_category_cannot_be_deleted_and_remains_in_historical_relation_when_inactive(): void
    {
        $this->asManager();
        $category = FacilityCategory::create(['category_name' => 'Taman', 'is_active' => false]);
        $facility = Facility::create(['category_id' => $category->getKey(), 'name' => 'Taman kota']);

        $this->from(route('manage.facility-categories.show', $category))
            ->delete(route('manage.facility-categories.destroy', $category))
            ->assertRedirect(route('manage.facility-categories.show', $category))
            ->assertSessionHas('error', 'Kategori sedang digunakan fasilitas. Nonaktifkan kategori agar histori tetap tersedia.');

        $this->assertModelExists($category);
        $this->assertModelExists($facility);
        $this->assertSame('Taman', $facility->fresh()->category->category_name);
        $this->assertSame(0, FacilityCategory::active()->count());
    }

    public function test_used_issue_category_cannot_be_deleted_and_remains_in_historical_relation_when_inactive(): void
    {
        $user = $this->asManager();
        $category = IssueCategory::create(['category_name' => 'Jalur terhalang', 'is_active' => false]);
        $report = $this->createReport($user, $category);

        $this->from(route('manage.issue-categories.show', $category))
            ->delete(route('manage.issue-categories.destroy', $category))
            ->assertRedirect(route('manage.issue-categories.show', $category))
            ->assertSessionHas('error', 'Kategori sedang digunakan laporan. Nonaktifkan kategori agar histori tetap tersedia.');

        $this->assertModelExists($category);
        $this->assertModelExists($report);
        $this->assertSame('Jalur terhalang', $report->fresh()->issueCategory->category_name);
        $this->assertSame(0, IssueCategory::active()->count());
    }

    #[DataProvider('categoryTypes')]
    public function test_database_enforces_category_name_uniqueness_for_concurrent_writes(string $modelClass, string $routePrefix): void
    {
        $modelClass::create(['category_name' => 'Pendidikan', 'is_active' => true]);

        $this->expectException(UniqueConstraintViolationException::class);

        $modelClass::create(['category_name' => 'Pendidikan', 'is_active' => false]);
    }

    private function asManager(): User
    {
        $role = Role::firstOrCreate(['role_name' => 'Pengelola Fasilitas']);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);
        $this->actingAs($user);

        return $user;
    }

    private function createReport(User $user, IssueCategory $category): Report
    {
        $facilityCategory = FacilityCategory::create(['category_name' => 'Fasilitas umum']);
        $facility = Facility::create(['category_id' => $facilityCategory->getKey(), 'name' => 'Fasilitas kota']);
        $period = ReportingPeriod::create(['period_name' => 'Oktober', 'start_date' => '2026-10-01', 'end_date' => '2026-10-31']);
        $status = VerificationStatus::create(['status_name' => 'Menunggu Verifikasi']);

        return Report::create([
            'report_number' => 'KAT-001',
            'user_id' => $user->id,
            'facility_id' => $facility->getKey(),
            'issue_category_id' => $category->getKey(),
            'status_id' => $status->getKey(),
            'period_id' => $period->getKey(),
            'description' => 'Akses terhalang.',
        ]);
    }
}
