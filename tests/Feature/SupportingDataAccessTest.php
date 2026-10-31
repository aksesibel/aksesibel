<?php

namespace Tests\Feature;

use App\Models\FacilityCategory;
use App\Models\IssueCategory;
use App\Models\ReportingPeriod;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SupportingDataAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @return array<string, array{?string, string, bool}> */
    public static function permissionCases(): array
    {
        return [
            'pengelola aktif' => ['Pengelola Fasilitas', 'active', true],
            'masyarakat' => ['Masyarakat', 'active', false],
            'admin bukan superadmin' => ['Admin', 'active', false],
            'role lama verifikator' => ['Verifikator', 'active', false],
            'role lama petugas' => ['Petugas Tindak Lanjut', 'active', false],
            'role tidak dikenal' => ['Tidak Dikenal', 'active', false],
            'tanpa role' => [null, 'active', false],
            'pengelola nonaktif' => ['Pengelola Fasilitas', 'inactive', false],
            'pengelola ditangguhkan' => ['Pengelola Fasilitas', 'suspended', false],
        ];
    }

    #[DataProvider('permissionCases')]
    public function test_gate_only_allows_active_facility_managers(?string $roleName, string $status, bool $allowed): void
    {
        $role = $roleName === null ? null : Role::create(['role_name' => $roleName]);
        $user = User::factory()->create(['role_id' => $role?->getKey(), 'account_status' => $status]);

        $this->assertSame($allowed, Gate::forUser($user)->allows('manage-supporting-data'));
    }

    /** @return array<string, array{string}> */
    public static function internalIndexes(): array
    {
        return [
            'pengguna' => ['manage.users.index'],
            'kategori fasilitas' => ['manage.facility-categories.index'],
            'kategori kendala' => ['manage.issue-categories.index'],
            'periode' => ['manage.reporting-periods.index'],
            'statistik' => ['manage.statistics'],
        ];
    }

    #[DataProvider('internalIndexes')]
    public function test_guests_are_redirected_from_internal_urls(string $routeName): void
    {
        $this->get(route($routeName))->assertRedirectToRoute('login');
    }

    /** @return array<string, array{string, string, bool}> */
    public static function protectedEndpoints(): array
    {
        $endpoints = [];

        foreach (['users', 'facility-categories', 'issue-categories', 'reporting-periods'] as $resource) {
            foreach (['index' => 'GET', 'create' => 'GET', 'store' => 'POST', 'show' => 'GET', 'edit' => 'GET', 'update' => 'PUT'] as $action => $method) {
                $endpoints[$resource.'.'.$action] = [$method, 'manage.'.$resource.'.'.$action, in_array($action, ['show', 'edit', 'update'], true)];
            }

            if ($resource !== 'users') {
                $endpoints[$resource.'.destroy'] = ['DELETE', 'manage.'.$resource.'.destroy', true];
            }
        }

        $endpoints['users.status'] = ['PATCH', 'manage.users.status', true];
        $endpoints['statistics'] = ['GET', 'manage.statistics', false];

        return $endpoints;
    }

    #[DataProvider('protectedEndpoints')]
    public function test_admin_is_forbidden_from_every_supporting_data_endpoint(string $method, string $routeName, bool $recordRequired): void
    {
        $adminRole = Role::create(['role_name' => 'Admin']);
        $user = User::factory()->create(['role_id' => $adminRole->getKey(), 'account_status' => 'active']);
        $facilityCategory = FacilityCategory::create(['category_name' => 'Taman']);
        $issueCategory = IssueCategory::create(['category_name' => 'Akses terhalang']);
        $period = ReportingPeriod::create(['period_name' => 'November 2026', 'start_date' => '2026-11-01', 'end_date' => '2026-11-30']);
        $record = match (true) {
            str_contains($routeName, 'facility-categories') => $facilityCategory,
            str_contains($routeName, 'issue-categories') => $issueCategory,
            str_contains($routeName, 'reporting-periods') => $period,
            default => $user,
        };

        $this->actingAs($user)->call($method, route($routeName, $recordRequired ? [$record] : []))->assertForbidden();

        $this->assertModelExists($facilityCategory);
        $this->assertModelExists($issueCategory);
        $this->assertModelExists($period);
        $this->assertSame('Admin', $user->fresh()->role->role_name);
    }

    #[DataProvider('internalIndexes')]
    public function test_active_facility_manager_can_open_each_module(string $routeName): void
    {
        $role = Role::create(['role_name' => 'Pengelola Fasilitas']);
        $manager = User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);

        $this->actingAs($manager)->get(route($routeName))->assertOk();
    }

    /** @return array<string, array{?string}> */
    public static function unknownRoles(): array
    {
        return ['verifikator' => ['Verifikator'], 'petugas' => ['Petugas Tindak Lanjut'], 'tidak dikenal' => ['Tidak Dikenal'], 'kosong' => [null]];
    }

    #[DataProvider('unknownRoles')]
    public function test_unknown_roles_have_no_account_page_access(?string $roleName): void
    {
        $role = $roleName === null ? null : Role::create(['role_name' => $roleName]);
        $user = User::factory()->create(['role_id' => $role?->getKey(), 'account_status' => 'active']);

        $this->actingAs($user)->get(route('account'))->assertForbidden();
    }

    /** @return array<string, array{?string}> */
    public static function nonManagerRoles(): array
    {
        return ['masyarakat' => ['Masyarakat'], 'tanpa peran' => [null], 'tidak dikenal' => ['Tidak Dikenal']];
    }

    #[DataProvider('nonManagerRoles')]
    public function test_non_manager_roles_cannot_access_a_direct_user_management_url(?string $roleName): void
    {
        $role = $roleName === null ? null : Role::create(['role_name' => $roleName]);
        $user = User::factory()->create(['role_id' => $role?->getKey(), 'account_status' => 'active']);

        $this->actingAs($user)->get(route('manage.users.index'))->assertForbidden();
    }
}
