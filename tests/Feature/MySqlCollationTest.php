<?php

namespace Tests\Feature;

use App\Models\FacilityCategory;
use App\Models\IssueCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MySqlCollationTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const PASSWORD = 'Aksesibel-Aman-2026';

    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array(config('database.connections.'.config('database.default').'.driver'), ['mysql', 'mariadb'], true)) {
            $this->markTestSkipped('Kontrak utf8mb4_unicode_ci memerlukan database testing MySQL/MariaDB yang terpisah.');
        }
    }

    public function test_relevant_columns_use_the_projects_mysql_collation(): void
    {
        $columns = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::connection()->getDatabaseName())
            ->whereIn('TABLE_NAME', ['facility_categories', 'issue_categories', 'users', 'roles'])
            ->whereIn('COLUMN_NAME', ['category_name', 'email', 'role_name', 'account_status'])
            ->get(['TABLE_NAME', 'COLUMN_NAME', 'COLLATION_NAME']);

        $this->assertCount(5, $columns);
        foreach ($columns as $column) {
            $this->assertSame('utf8mb4_unicode_ci', $column->COLLATION_NAME, $column->TABLE_NAME.'.'.$column->COLUMN_NAME);
        }
    }

    public static function equivalentCategoryNames(): array
    {
        $cases = [];
        $variants = [
            'huruf besar' => 'KAFÉ PUBLIK',
            'tanpa aksen' => 'Kafe Publik',
            'spasi belakang' => 'Kafé Publik   ',
            'spasi tepi' => '  Kafé Publik  ',
        ];
        foreach (self::categoryTypes() as $type => [$modelClass, $tableName, $routePrefix]) {
            foreach ($variants as $label => $variant) {
                $cases[$type.' '.$label] = [$modelClass, $tableName, $routePrefix, $variant];
            }
        }

        return $cases;
    }

    #[DataProvider('equivalentCategoryNames')]
    public function test_category_form_rejects_collation_equivalent_and_trimmed_duplicates(string $modelClass, string $tableName, string $routePrefix, string $variant): void
    {
        $manager = $this->manager();
        $original = $modelClass::create(['category_name' => 'Kafé Publik', 'is_active' => true]);

        $this->actingAs($manager)->post(route($routePrefix.'.store'), ['category_name' => $variant, 'is_active' => 1])
            ->assertSessionHasErrors(['category_name' => 'Nama kategori sudah digunakan.']);

        $this->assertDatabaseCount($tableName, 1);
        $this->assertSame('Kafé Publik', $original->fresh()->category_name);
    }

    public static function collationEquivalentIndexNames(): array
    {
        $cases = [];
        foreach (self::categoryTypes() as $type => [$modelClass, $tableName, $routePrefix]) {
            foreach (['huruf besar' => 'KAFÉ PUBLIK', 'tanpa aksen' => 'Kafe Publik', 'spasi belakang' => 'Kafé Publik   '] as $label => $variant) {
                $cases[$type.' '.$label] = [$modelClass, $variant];
            }
        }

        return $cases;
    }

    #[DataProvider('collationEquivalentIndexNames')]
    public function test_category_unique_index_rejects_collation_equivalent_direct_writes(string $modelClass, string $variant): void
    {
        $modelClass::create(['category_name' => 'Kafé Publik', 'is_active' => true]);

        $this->expectException(UniqueConstraintViolationException::class);

        $modelClass::create(['category_name' => $variant, 'is_active' => false]);
    }

    public function test_managed_user_form_rejects_email_that_differs_only_in_case(): void
    {
        $manager = $this->manager();
        $publicRole = Role::create(['role_name' => Role::PUBLIC]);
        $original = User::factory()->create(['email' => 'Pemilik@example.com', 'role_id' => $publicRole->getKey()]);

        $this->actingAs($manager)->post(route('manage.users.store'), [
            'name' => 'Akun duplikat',
            'email' => 'pemilik@example.com',
            'role_id' => $publicRole->getKey(),
            'account_status' => 'active',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 2);
        $this->assertSame('Pemilik@example.com', $original->fresh()->email);
    }

    public function test_public_registration_rejects_email_that_differs_only_in_case(): void
    {
        $publicRole = Role::create(['role_name' => Role::PUBLIC]);
        $original = User::factory()->create(['email' => 'Pemilik@example.com', 'role_id' => $publicRole->getKey()]);

        $this->post(route('register.store'), [
            'name' => 'Akun duplikat',
            'email' => 'pemilik@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
        $this->assertSame('Pemilik@example.com', $original->fresh()->email);
    }

    public function test_email_unique_index_rejects_case_equivalent_direct_writes(): void
    {
        User::factory()->create(['email' => 'Pemilik@example.com']);

        $this->expectException(UniqueConstraintViolationException::class);

        User::factory()->create(['email' => 'pemilik@example.com']);
    }

    public static function noncanonicalActiveStatuses(): array
    {
        return [
            'huruf besar' => ['ACTIVE'],
            'aksen' => ['áctive'],
            'spasi belakang' => ['active '],
        ];
    }

    #[DataProvider('noncanonicalActiveStatuses')]
    public function test_login_refuses_noncanonical_status_even_when_mysql_matches_active(string $status): void
    {
        $role = Role::create(['role_name' => Role::FACILITY_MANAGER]);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'password' => self::PASSWORD, 'account_status' => $status]);
        $this->assertTrue(User::query()->whereKey($user->getKey())->where('account_status', 'active')->exists());

        $this->post(route('login.store'), ['email' => $user->email, 'password' => self::PASSWORD])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame($status, $user->fresh()->account_status);
    }

    #[DataProvider('noncanonicalActiveStatuses')]
    public function test_noncanonical_status_does_not_count_as_an_active_backup_manager(string $status): void
    {
        $manager = $this->manager();
        $backup = User::factory()->create(['role_id' => $manager->role_id, 'account_status' => $status]);
        $publicRole = Role::create(['role_name' => Role::PUBLIC]);
        $this->assertTrue(User::query()->whereKey($backup->getKey())->where('account_status', 'active')->exists());

        $this->actingAs($manager)->put(route('manage.users.update', $manager), $this->demotionPayload($manager, $publicRole))
            ->assertSessionHasErrors('role_id');

        $this->assertSame(Role::FACILITY_MANAGER, $manager->fresh()->role->role_name);
        $this->assertSame('active', $manager->fresh()->account_status);
        $this->assertSame($status, $backup->fresh()->account_status);
    }

    public static function noncanonicalManagerRoles(): array
    {
        return [
            'huruf besar' => ['PENGELOLA FASILITAS'],
            'aksen' => ['Pengelola Fásilitas'],
            'spasi belakang' => ['Pengelola Fasilitas '],
        ];
    }

    #[DataProvider('noncanonicalManagerRoles')]
    public function test_collation_equivalent_role_does_not_gain_management_or_internal_access(string $roleName): void
    {
        $role = Role::create(['role_name' => $roleName]);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);
        $this->assertTrue(Role::query()->whereKey($role->getKey())->where('role_name', Role::FACILITY_MANAGER)->exists());

        $this->actingAs($user)->get(route('manage.users.index'))->assertForbidden();
        $this->get(route('account'))->assertForbidden();

        $this->assertFalse($user->fresh()->canManageSupportingData());
    }

    #[DataProvider('noncanonicalManagerRoles')]
    public function test_collation_equivalent_role_does_not_count_as_a_backup_manager(string $roleName): void
    {
        $manager = $this->manager();
        $legacyRole = Role::create(['role_name' => $roleName]);
        $backup = User::factory()->create(['role_id' => $legacyRole->getKey(), 'account_status' => 'active']);
        $publicRole = Role::create(['role_name' => Role::PUBLIC]);
        $this->assertTrue(Role::query()->whereKey($legacyRole->getKey())->where('role_name', Role::FACILITY_MANAGER)->exists());

        $this->actingAs($manager)->put(route('manage.users.update', $manager), $this->demotionPayload($manager, $publicRole))
            ->assertSessionHasErrors('role_id');

        $this->assertSame(Role::FACILITY_MANAGER, $manager->fresh()->role->role_name);
        $this->assertSame($legacyRole->getKey(), $backup->fresh()->role_id);
        $this->assertFalse($backup->fresh()->canManageSupportingData());
    }

    public static function noncanonicalPublicRoles(): array
    {
        return [
            'huruf besar' => ['MASYARAKAT'],
            'aksen' => ['Masyarákat'],
            'spasi belakang' => ['Masyarakat '],
        ];
    }

    #[DataProvider('noncanonicalPublicRoles')]
    public function test_registration_does_not_assign_a_collation_equivalent_unknown_role(string $roleName): void
    {
        $legacyRole = Role::create(['role_name' => $roleName]);
        $this->assertTrue(Role::query()->whereKey($legacyRole->getKey())->where('role_name', Role::PUBLIC)->exists());

        $this->post(route('register.store'), [
            'name' => 'Warga baru',
            'email' => 'baru@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])->assertSessionHasErrors(['email' => 'Peran Masyarakat belum tersedia. Pengelola perlu menjalankan MasterDataSeeder.']);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    private static function categoryTypes(): array
    {
        return [
            'fasilitas' => [FacilityCategory::class, 'facility_categories', 'manage.facility-categories'],
            'kendala' => [IssueCategory::class, 'issue_categories', 'manage.issue-categories'],
        ];
    }

    private function manager(): User
    {
        $role = Role::firstOrCreate(['role_name' => Role::FACILITY_MANAGER]);

        return User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);
    }

    private function demotionPayload(User $manager, Role $publicRole): array
    {
        return [
            'name' => $manager->name,
            'email' => $manager->email,
            'role_id' => $publicRole->getKey(),
            'account_status' => 'active',
            'password' => '',
            'password_confirmation' => '',
        ];
    }
}
