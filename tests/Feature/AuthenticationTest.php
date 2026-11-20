<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const PASSWORD = 'Aksesibel-Aman-2026';

    public function test_registration_only_creates_an_active_masyarakat_account(): void
    {
        $role = Role::create(['role_name' => 'Masyarakat']);

        $response = $this->post(route('register.store'), [
            'name' => '  Pengguna Baru  ',
            'email' => 'baru@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
            'email_verified_at' => '2026-01-01',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $user = User::where('email', 'baru@example.com')->firstOrFail();
        $this->assertSame('Pengguna Baru', $user->name);
        $this->assertSame($role->getKey(), $user->role_id);
        $this->assertSame('active', $user->account_status);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));
    }

    public function test_registration_rejects_attempts_to_choose_role_or_status(): void
    {
        $managerRole = Role::create(['role_name' => 'Pengelola Fasilitas']);
        Role::create(['role_name' => 'Masyarakat']);

        $response = $this->post(route('register.store'), [
            'name' => 'Penyerang',
            'email' => 'penyerang@example.com',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
            'role_id' => $managerRole->getKey(),
            'account_status' => 'active',
        ]);

        $response->assertSessionHasErrors(['role_id', 'account_status']);
        $this->assertDatabaseMissing('users', ['email' => 'penyerang@example.com']);
        $this->assertGuest();
    }

    public function test_login_regenerates_session_for_an_active_account(): void
    {
        $role = Role::create(['role_name' => 'Masyarakat']);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'password' => self::PASSWORD, 'account_status' => 'active']);
        $this->withSession(['marker' => 'retained']);
        $oldSessionId = session()->getId();

        $response = $this->post(route('login.store'), ['email' => $user->email, 'password' => self::PASSWORD]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($oldSessionId, session()->getId());
    }

    public function test_wrong_password_never_authenticates_an_existing_active_account(): void
    {
        $role = Role::create(['role_name' => 'Masyarakat']);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'password' => self::PASSWORD, 'account_status' => 'active']);

        $response = $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Password-Yang-Salah']);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unknown_uppercase_status_cannot_log_in_as_an_active_account(): void
    {
        $role = Role::create(['role_name' => 'Pengelola Fasilitas']);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'password' => self::PASSWORD, 'account_status' => 'ACTIVE']);

        $response = $this->post(route('login.store'), ['email' => $user->email, 'password' => self::PASSWORD]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @return array<string, array{string}> */
    public static function disabledStatuses(): array
    {
        return ['nonaktif' => ['inactive'], 'ditangguhkan' => ['suspended']];
    }

    #[DataProvider('disabledStatuses')]
    public function test_disabled_accounts_cannot_log_in(string $status): void
    {
        $role = Role::create(['role_name' => 'Pengelola Fasilitas']);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'password' => self::PASSWORD, 'account_status' => $status]);

        $response = $this->post(route('login.store'), ['email' => $user->email, 'password' => self::PASSWORD]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[DataProvider('disabledStatuses')]
    public function test_existing_sessions_lose_internal_access_after_account_is_disabled(string $status): void
    {
        $role = Role::create(['role_name' => 'Pengelola Fasilitas']);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);
        $this->actingAs($user);
        User::whereKey($user->getKey())->update(['account_status' => $status]);

        $response = $this->get(route('manage.users.index'));

        $response->assertRedirectToRoute('login');
        $this->assertGuest();
    }

    public function test_existing_session_loses_management_access_after_role_is_changed(): void
    {
        $managerRole = Role::create(['role_name' => 'Pengelola Fasilitas']);
        $publicRole = Role::create(['role_name' => 'Masyarakat']);
        $user = User::factory()->create(['role_id' => $managerRole->getKey(), 'account_status' => 'active']);
        $user->load('role');
        $this->actingAs($user);
        User::whereKey($user->getKey())->update(['role_id' => $publicRole->getKey()]);

        $response = $this->get(route('manage.users.index'));

        $response->assertForbidden();
    }

    public function test_repeated_invalid_logins_are_throttled(): void
    {
        $this->freezeTime();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), ['email' => 'tidakada@example.com', 'password' => self::PASSWORD]);
        }

        $response = $this->post(route('login.store'), ['email' => 'tidakada@example.com', 'password' => self::PASSWORD]);

        $response->assertTooManyRequests();
        $this->assertGuest();
    }

    public function test_logout_invalidates_the_session_and_csrf_token(): void
    {
        $role = Role::create(['role_name' => 'Masyarakat']);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);
        $this->actingAs($user)->withSession(['private_marker' => 'remove', '_token' => 'old-csrf-token']);
        $oldSessionId = session()->getId();

        $response = $this->post(route('logout'));

        $response->assertRedirect();
        $response->assertSessionMissing('private_marker');
        $this->assertGuest();
        $this->assertNotSame($oldSessionId, session()->getId());
        $this->assertNotSame('old-csrf-token', session()->token());
    }

    public function test_profile_updates_cannot_change_role_or_status(): void
    {
        $role = Role::create(['role_name' => 'Masyarakat']);
        $managerRole = Role::create(['role_name' => 'Pengelola Fasilitas']);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $managerRole->getKey(),
            'account_status' => 'suspended',
        ]);

        $response->assertSessionHasErrors(['role_id', 'account_status']);
        $this->assertSame($role->getKey(), $user->fresh()->role_id);
        $this->assertSame('active', $user->fresh()->account_status);
    }

    public function test_profile_can_change_contact_details_without_changing_a_blank_password(): void
    {
        $role = Role::create(['role_name' => 'Masyarakat']);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);
        $oldPassword = $user->password;

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Nama Baru',
            'email' => 'alamat-baru@example.com',
            'password' => '',
            'password_confirmation' => '',
            'email_verified_at' => '2026-01-01',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertSame('Nama Baru', $user->fresh()->name);
        $this->assertSame('alamat-baru@example.com', $user->fresh()->email);
        $this->assertSame($oldPassword, $user->fresh()->password);
    }
}
