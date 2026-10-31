<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const PASSWORD = 'Aksesibel-Aman-2026';

    private function manager(): User
    {
        $role = Role::firstOrCreate(['role_name' => 'Pengelola Fasilitas']);

        return User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);
    }

    /** @return array<string, mixed> */
    private function validPayload(Role $role): array
    {
        return [
            'name' => 'Pengguna Baru',
            'email' => 'baru@example.com',
            'role_id' => $role->getKey(),
            'account_status' => 'active',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ];
    }

    public function test_manager_creates_a_user_with_a_hashed_password_and_only_validated_attributes(): void
    {
        $manager = $this->manager();
        $role = Role::create(['role_name' => 'Masyarakat']);
        $payload = $this->validPayload($role);
        $payload['name'] = '  Pengguna Baru  ';
        $payload['email_verified_at'] = '2026-01-01';
        $payload['remember_token'] = 'attacker-token';

        $response = $this->actingAs($manager)->post(route('manage.users.store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $user = User::where('email', 'baru@example.com')->firstOrFail();
        $this->assertSame('Pengguna Baru', $user->name);
        $this->assertSame($role->getKey(), $user->role_id);
        $this->assertSame('active', $user->account_status);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));
        $this->assertNotSame(self::PASSWORD, $user->password);
        $this->assertNull($user->email_verified_at);
        $this->assertNotSame('attacker-token', $user->remember_token);
    }

    /** @return array<string, array{array<string, mixed>, array<int, string>}> */
    public static function invalidPayloads(): array
    {
        return [
            'nama kosong' => [['name' => '  '], ['name']],
            'nama terlalu panjang' => [['name' => str_repeat('a', 256)], ['name']],
            'email tidak valid' => [['email' => 'bukan-email'], ['email']],
            'email duplikat' => [['email' => 'existing@example.com'], ['email']],
            'role tidak tersedia' => [['role_id' => 999999], ['role_id']],
            'role kosong' => [['role_id' => null], ['role_id']],
            'status tidak tersedia' => [['account_status' => 'superuser'], ['account_status']],
            'password pendek' => [['password' => 'pendek', 'password_confirmation' => 'pendek'], ['password']],
            'konfirmasi salah' => [['password_confirmation' => 'Berbeda-Dari-Password'], ['password']],
        ];
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_user_details_are_rejected_without_creating_an_account(array $changes, array $errors): void
    {
        $manager = $this->manager();
        $role = Role::create(['role_name' => 'Masyarakat']);
        User::factory()->create(['email' => 'existing@example.com', 'role_id' => $role->getKey(), 'account_status' => 'active']);

        $response = $this->actingAs($manager)->post(route('manage.users.store'), array_replace($this->validPayload($role), $changes));

        $response->assertSessionHasErrors($errors);
        $this->assertDatabaseCount('users', 2);
    }

    public function test_editing_without_a_password_preserves_the_existing_hash_and_changes_role(): void
    {
        $manager = $this->manager();
        $role = Role::create(['role_name' => 'Masyarakat']);
        $adminRole = Role::create(['role_name' => 'Admin']);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);
        $oldHash = $user->password;
        $payload = array_replace($this->validPayload($adminRole), [
            'name' => 'Nama Diubah',
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response = $this->actingAs($manager)->put(route('manage.users.update', $user), $payload);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertSame($oldHash, $user->fresh()->password);
        $this->assertSame('Nama Diubah', $user->fresh()->name);
        $this->assertSame($adminRole->getKey(), $user->fresh()->role_id);
    }

    public function test_editing_with_a_new_confirmed_password_replaces_its_hash(): void
    {
        $manager = $this->manager();
        $role = Role::create(['role_name' => 'Masyarakat']);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);
        $oldHash = $user->password;

        $response = $this->actingAs($manager)->put(route('manage.users.update', $user), array_replace($this->validPayload($role), ['email' => $user->email]));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertNotSame($oldHash, $user->fresh()->password);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->fresh()->password));
    }

    /** @return array<string, array{string}> */
    public static function accountStatuses(): array
    {
        return ['aktif' => ['active'], 'nonaktif' => ['inactive'], 'ditangguhkan' => ['suspended']];
    }

    #[DataProvider('accountStatuses')]
    public function test_manager_can_set_a_regular_users_account_status(string $status): void
    {
        $manager = $this->manager();
        $role = Role::create(['role_name' => 'Masyarakat']);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'inactive']);

        $response = $this->actingAs($manager)->patch(route('manage.users.status', $user), ['account_status' => $status]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertSame($status, $user->fresh()->account_status);
        $this->assertModelExists($user);
    }

    public function test_manager_cannot_deactivate_their_own_account_even_with_a_backup_manager(): void
    {
        $manager = $this->manager();
        $this->manager();

        $response = $this->actingAs($manager)->patch(route('manage.users.status', $manager), ['account_status' => 'inactive']);

        $response->assertSessionHasErrors('account_status');
        $this->assertSame('active', $manager->fresh()->account_status);
    }

    public function test_edit_form_cannot_bypass_self_suspension_protection(): void
    {
        $manager = $this->manager();
        $this->manager();
        $role = Role::findOrFail($manager->role_id);

        $response = $this->actingAs($manager)->put(route('manage.users.update', $manager), array_replace($this->validPayload($role), [
            'email' => $manager->email,
            'account_status' => 'suspended',
            'password' => '',
            'password_confirmation' => '',
        ]));

        $response->assertSessionHasErrors('account_status');
        $this->assertSame('active', $manager->fresh()->account_status);
    }

    public function test_last_active_manager_cannot_be_demoted_even_if_an_inactive_manager_exists(): void
    {
        $manager = $this->manager();
        $inactiveManager = $this->manager();
        $inactiveManager->update(['account_status' => 'inactive']);
        $role = Role::create(['role_name' => 'Masyarakat']);

        $response = $this->actingAs($manager)->put(route('manage.users.update', $manager), array_replace($this->validPayload($role), ['email' => $manager->email, 'password' => '', 'password_confirmation' => '']));

        $response->assertSessionHasErrors();
        $this->assertSame('Pengelola Fasilitas', $manager->fresh()->role->role_name);
        $this->assertSame('active', $manager->fresh()->account_status);
    }

    public function test_manager_can_be_demoted_when_another_active_manager_remains(): void
    {
        $manager = $this->manager();
        $this->manager();
        $role = Role::create(['role_name' => 'Masyarakat']);

        $response = $this->actingAs($manager)->put(route('manage.users.update', $manager), array_replace($this->validPayload($role), ['email' => $manager->email, 'password' => '', 'password_confirmation' => '']));

        $response->assertRedirectToRoute('account');
        $response->assertSessionHasNoErrors();
        $this->assertSame($role->getKey(), $manager->fresh()->role_id);

        $this->get(route('account'))->assertOk();
        $this->get(route('manage.users.index'))->assertForbidden();
    }

    public function test_unknown_role_is_not_counted_as_a_backup_for_the_last_manager(): void
    {
        $manager = $this->manager();
        $lookalikeRole = Role::create(['role_name' => 'pengelola fasilitas']);
        User::factory()->create(['role_id' => $lookalikeRole->getKey(), 'account_status' => 'active']);
        $role = Role::create(['role_name' => 'Masyarakat']);

        $response = $this->actingAs($manager)->put(route('manage.users.update', $manager), array_replace($this->validPayload($role), ['email' => $manager->email, 'password' => '', 'password_confirmation' => '']));

        $response->assertSessionHasErrors();
        $this->assertSame('Pengelola Fasilitas', $manager->fresh()->role->role_name);
    }

    public function test_unknown_uppercase_status_is_not_counted_as_an_active_backup_manager(): void
    {
        $manager = $this->manager();
        $backup = $this->manager();
        $backup->update(['account_status' => 'ACTIVE']);
        $role = Role::create(['role_name' => 'Masyarakat']);

        $response = $this->actingAs($manager)->put(route('manage.users.update', $manager), array_replace($this->validPayload($role), ['email' => $manager->email, 'password' => '', 'password_confirmation' => '']));

        $response->assertSessionHasErrors();
        $this->assertSame('Pengelola Fasilitas', $manager->fresh()->role->role_name);
    }

    public function test_active_managers_using_duplicate_canonical_role_rows_are_counted_together(): void
    {
        $manager = $this->manager();
        $duplicateRole = Role::create(['role_name' => 'Pengelola Fasilitas']);
        $backup = User::factory()->create(['role_id' => $duplicateRole->getKey(), 'account_status' => 'active']);
        $publicRole = Role::create(['role_name' => 'Masyarakat']);

        $response = $this->actingAs($manager)->put(route('manage.users.update', $manager), array_replace($this->validPayload($publicRole), ['email' => $manager->email, 'password' => '', 'password_confirmation' => '']));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertSame($publicRole->getKey(), $manager->fresh()->role_id);
        $this->assertSame('Pengelola Fasilitas', $backup->fresh()->role->role_name);
        $this->assertSame('active', $backup->fresh()->account_status);
    }

    public function test_user_list_applies_name_email_role_and_status_filters_together(): void
    {
        $manager = $this->manager();
        $role = Role::create(['role_name' => 'Masyarakat']);
        $matching = User::factory()->create(['name' => 'Rani', 'email' => 'cari@example.com', 'role_id' => $role->getKey(), 'account_status' => 'suspended']);
        User::factory()->create(['name' => 'Rani Lain', 'email' => 'cari-lain@example.com', 'role_id' => $role->getKey(), 'account_status' => 'active']);
        User::factory()->create(['name' => 'Rani Pengelola', 'email' => 'cari-pengelola@example.com', 'role_id' => $manager->role_id, 'account_status' => 'suspended']);

        $response = $this->actingAs($manager)->get(route('manage.users.index', ['q' => 'cari', 'role_id' => $role->getKey(), 'account_status' => 'suspended']));

        $response->assertOk();
        $response->assertViewHas('users', fn (LengthAwarePaginator $users): bool => $users->total() === 1 && $users->first()->is($matching));
    }

    public function test_user_list_is_paginated(): void
    {
        $manager = $this->manager();
        User::factory()->count(31)->create(['account_status' => 'active']);

        $response = $this->actingAs($manager)->get(route('manage.users.index'));

        $response->assertOk();
        $response->assertViewHas('users', fn (LengthAwarePaginator $users): bool => $users->total() === 32 && $users->hasMorePages());
    }

    public function test_detail_escapes_user_names_and_does_not_expose_passwords(): void
    {
        $manager = $this->manager();
        $user = User::factory()->create(['name' => '<script>alert("xss")</script>', 'password' => self::PASSWORD, 'account_status' => 'active']);

        $response = $this->actingAs($manager)->get(route('manage.users.show', $user));

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertDontSee($user->name, false);
        $response->assertDontSee($user->password, false);
        $response->assertDontSee(self::PASSWORD, false);
    }
}
