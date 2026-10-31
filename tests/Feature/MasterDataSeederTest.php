<?php

namespace Tests\Feature;

use App\Models\FacilityCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterDataSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_clean_installation_seeds_only_the_three_proposal_roles_and_no_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(['Admin', 'Masyarakat', 'Pengelola Fasilitas'], Role::orderBy('role_name')->pluck('role_name')->all());
        $this->assertDatabaseCount('users', 0);
    }

    public function test_reseeding_does_not_duplicate_master_data_or_reset_existing_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);
        $role = Role::where('role_name', 'Masyarakat')->firstOrFail();
        $user = User::factory()->create(['email' => 'test@example.com', 'password' => 'Password-Pribadi-2026', 'role_id' => $role->getKey(), 'account_status' => 'suspended']);
        $oldHash = $user->password;
        $category = FacilityCategory::firstOrFail();
        $category->update(['description' => 'Deskripsi yang sudah dikelola', 'is_active' => false]);
        $counts = [Role::count(), FacilityCategory::count()];

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($counts, [Role::count(), FacilityCategory::count()]);
        $this->assertSame($oldHash, $user->fresh()->password);
        $this->assertTrue(Hash::check('Password-Pribadi-2026', $user->fresh()->password));
        $this->assertSame('suspended', $user->fresh()->account_status);
        $this->assertSame($role->getKey(), $user->fresh()->role_id);
        $this->assertSame('Deskripsi yang sudah dikelola', $category->fresh()->description);
        $this->assertFalse($category->fresh()->is_active);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_existing_legacy_roles_keep_their_identity_and_users_after_seeding(): void
    {
        $legacyRole = Role::create(['role_name' => 'Verifikator', 'description' => 'Peran modul lama']);
        $legacyUser = User::factory()->create(['role_id' => $legacyRole->getKey(), 'account_status' => 'active']);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame('Verifikator', $legacyRole->fresh()->role_name);
        $this->assertSame('Peran modul lama', $legacyRole->fresh()->description);
        $this->assertSame($legacyRole->getKey(), $legacyUser->fresh()->role_id);
        $this->assertDatabaseHas('roles', ['role_name' => 'Pengelola Fasilitas']);
    }
}
