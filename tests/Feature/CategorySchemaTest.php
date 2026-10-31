<?php

namespace Tests\Feature;

use App\Models\FacilityCategory;
use App\Models\IssueCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class CategorySchemaTest extends TestCase
{
    use LazilyRefreshDatabase;

    public static function categoryTypes(): array
    {
        return [
            'kategori fasilitas' => [FacilityCategory::class, 'facility_categories', 'manage.facility-categories'],
            'kategori kendala' => [IssueCategory::class, 'issue_categories', 'manage.issue-categories'],
        ];
    }

    #[DataProvider('categoryTypes')]
    public function test_down_up_preserves_rows_and_restores_unique_protection(string $modelClass, string $tableName, string $routePrefix): void
    {
        $facilityCategory = FacilityCategory::create(['category_name' => 'Transportasi', 'description' => 'Tetap tersimpan', 'is_active' => false]);
        $issueCategory = IssueCategory::create(['category_name' => 'Jalur terhalang', 'description' => 'Histori kategori', 'is_active' => true]);
        $category = $tableName === 'facility_categories' ? $facilityCategory : $issueCategory;
        $migration = $this->categoryNameMigration();

        $migration->down();
        $duplicate = $modelClass::create(['category_name' => $category->category_name]);
        $this->assertDatabaseCount($tableName, 2);
        $this->assertSame('Tetap tersimpan', $facilityCategory->fresh()->description);
        $this->assertFalse($facilityCategory->fresh()->is_active);
        $this->assertSame('Histori kategori', $issueCategory->fresh()->description);
        $this->assertTrue($issueCategory->fresh()->is_active);

        $duplicate->delete();
        $migration->up();

        $this->assertModelExists($facilityCategory);
        $this->assertModelExists($issueCategory);
        $this->expectException(UniqueConstraintViolationException::class);

        $modelClass::create(['category_name' => $category->category_name]);
    }

    #[DataProvider('categoryTypes')]
    public function test_preflight_rejects_existing_duplicates_without_deleting_data_or_partially_adding_indexes(string $modelClass, string $tableName, string $routePrefix): void
    {
        FacilityCategory::create(['category_name' => 'Transportasi', 'description' => 'Kategori fasilitas lama', 'is_active' => false]);
        IssueCategory::create(['category_name' => 'Jalur terhalang', 'description' => 'Kategori kendala lama', 'is_active' => true]);
        $migration = $this->categoryNameMigration();
        $migration->down();
        $categoryName = $modelClass::sole()->category_name;
        $modelClass::create(['category_name' => $categoryName, 'description' => 'Duplikat lama tetap disimpan', 'is_active' => true]);
        $beforeFacilityCategories = DB::table('facility_categories')->orderBy('category_id')->get()->toJson();
        $beforeIssueCategories = DB::table('issue_categories')->orderBy('issue_category_id')->get()->toJson();

        try {
            $migration->up();
            $this->fail('Migration wajib menolak duplikat yang sudah ada.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Nama kategori duplikat pada '.$tableName, $exception->getMessage());
            $this->assertStringContainsString('tidak ada kategori yang dihapus otomatis', $exception->getMessage());
        }

        $this->assertSame($beforeFacilityCategories, DB::table('facility_categories')->orderBy('category_id')->get()->toJson());
        $this->assertSame($beforeIssueCategories, DB::table('issue_categories')->orderBy('issue_category_id')->get()->toJson());
        $this->assertFalse(Schema::hasIndex('facility_categories', ['category_name'], 'unique'));
        $this->assertFalse(Schema::hasIndex('issue_categories', ['category_name'], 'unique'));
    }

    #[DataProvider('categoryTypes')]
    public function test_trimmed_validation_and_unique_index_follow_native_sqlite_case_sensitivity(string $modelClass, string $tableName, string $routePrefix): void
    {
        $role = Role::firstOrCreate(['role_name' => 'Pengelola Fasilitas']);
        $this->actingAs(User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']));
        $original = $modelClass::create(['category_name' => 'Transportasi', 'is_active' => true]);

        $this->post(route($routePrefix.'.store'), ['category_name' => '  transportasi  ', 'is_active' => 1])
            ->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertDatabaseHas($tableName, ['category_name' => 'transportasi']);
        $this->assertDatabaseCount($tableName, 2);
        $this->assertSame('Transportasi', $original->fresh()->category_name);

        $this->post(route($routePrefix.'.store'), ['category_name' => ' transportasi ', 'is_active' => 1])
            ->assertSessionHasErrors(['category_name' => 'Nama kategori sudah digunakan.']);
        $this->assertDatabaseCount($tableName, 2);
        $this->expectException(UniqueConstraintViolationException::class);

        $modelClass::create(['category_name' => 'transportasi']);
    }

    public static function legacyWhitespaceNames(): array
    {
        $cases = [];
        foreach (self::categoryTypes() as $type => [$modelClass, $tableName, $routePrefix]) {
            $cases[$type.' spasi biasa'] = [$modelClass, $tableName, ' Taman '];
            $cases[$type.' spasi unicode'] = [$modelClass, $tableName, "\u{00A0}Taman\u{00A0}"];
        }

        return $cases;
    }

    #[DataProvider('legacyWhitespaceNames')]
    public function test_preflight_rejects_legacy_edge_whitespace_without_changing_rows_or_adding_indexes(string $modelClass, string $tableName, string $legacyName): void
    {
        $migration = $this->categoryNameMigration();
        $migration->down();
        $category = $modelClass::create(['category_name' => $legacyName, 'description' => 'Referensi histori harus utuh', 'is_active' => false]);
        $beforeFacilityCategories = DB::table('facility_categories')->orderBy('category_id')->get()->toJson();
        $beforeIssueCategories = DB::table('issue_categories')->orderBy('issue_category_id')->get()->toJson();

        try {
            $migration->up();
            $this->fail('Migration wajib menolak nama lama yang belum dipangkas.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString($tableName.' (ID '.$category->getKey().') memiliki spasi tepi', $exception->getMessage());
            $this->assertStringContainsString('Rapikan nama dan tinjau duplikasi serta referensinya secara manual', $exception->getMessage());
            $this->assertStringContainsString('tidak ada data yang diubah otomatis', $exception->getMessage());
        }

        $this->assertSame($beforeFacilityCategories, DB::table('facility_categories')->orderBy('category_id')->get()->toJson());
        $this->assertSame($beforeIssueCategories, DB::table('issue_categories')->orderBy('issue_category_id')->get()->toJson());
        $this->assertFalse(Schema::hasIndex('facility_categories', ['category_name'], 'unique'));
        $this->assertFalse(Schema::hasIndex('issue_categories', ['category_name'], 'unique'));
    }

    #[DataProvider('categoryTypes')]
    public function test_up_resumes_after_partial_index_creation_without_touching_category_data(string $modelClass, string $tableName, string $routePrefix): void
    {
        FacilityCategory::create(['category_name' => 'Transportasi', 'description' => 'Kategori fasilitas lama', 'is_active' => false]);
        IssueCategory::create(['category_name' => 'Jalur terhalang', 'description' => 'Kategori kendala lama', 'is_active' => true]);
        $missingTable = $tableName === 'facility_categories' ? 'issue_categories' : 'facility_categories';
        Schema::table($missingTable, function (Blueprint $table) {
            $table->dropUnique(['category_name']);
        });
        $beforeFacilityCategories = DB::table('facility_categories')->orderBy('category_id')->get()->toJson();
        $beforeIssueCategories = DB::table('issue_categories')->orderBy('issue_category_id')->get()->toJson();
        $migration = $this->categoryNameMigration();

        $migration->up();
        $migration->up();

        $this->assertSame($beforeFacilityCategories, DB::table('facility_categories')->orderBy('category_id')->get()->toJson());
        $this->assertSame($beforeIssueCategories, DB::table('issue_categories')->orderBy('issue_category_id')->get()->toJson());
        $this->assertTrue(Schema::hasIndex('facility_categories', 'facility_categories_category_name_unique', 'unique'));
        $this->assertTrue(Schema::hasIndex('issue_categories', 'issue_categories_category_name_unique', 'unique'));
    }

    private function categoryNameMigration(): Migration
    {
        return require database_path('migrations/2026_10_31_174442_add_unique_names_to_categories.php');
    }
}
