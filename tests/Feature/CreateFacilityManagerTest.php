<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateFacilityManagerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const PASSWORD = 'Password-Interaktif-2026';

    public function test_noninteractive_execution_refuses_without_changing_existing_accounts(): void
    {
        $role = Role::create(['role_name' => 'Pengelola Fasilitas']);
        $user = User::factory()->create(['role_id' => $role->getKey(), 'account_status' => 'active']);
        $oldHash = $user->password;

        $this->artisan('aksesibel:create-manager', ['--no-interaction' => true])
            ->expectsOutputToContain('Jalankan perintah secara interaktif.')
            ->assertFailed();

        $this->assertDatabaseCount('users', 1);
        $this->assertSame($oldHash, $user->fresh()->password);
    }

    public function test_missing_manager_role_refuses_before_requesting_credentials(): void
    {
        $this->artisan('aksesibel:create-manager')
            ->expectsOutputToContain('MasterDataSeeder')
            ->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_interactive_command_creates_an_active_manager_and_hashes_the_password(): void
    {
        $role = Role::create(['role_name' => 'Pengelola Fasilitas']);

        $this->artisan('aksesibel:create-manager')
            ->expectsQuestion('Nama pengelola', '  Pengelola Baru  ')
            ->expectsQuestion('Email', '  pengelola@example.com  ')
            ->expectsQuestion('Password (minimal 12 karakter)', self::PASSWORD)
            ->expectsQuestion('Konfirmasi password', self::PASSWORD)
            ->expectsOutputToContain('berhasil dibuat')
            ->doesntExpectOutputToContain(self::PASSWORD)
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 1);
        $user = User::where('email', 'pengelola@example.com')->firstOrFail();
        $this->assertSame('Pengelola Baru', $user->name);
        $this->assertSame($role->getKey(), $user->role_id);
        $this->assertSame('active', $user->account_status);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));
        $this->assertNotSame(self::PASSWORD, $user->password);
    }

    public function test_existing_email_cannot_be_used_to_overwrite_an_account_or_password(): void
    {
        Role::create(['role_name' => 'Pengelola Fasilitas']);
        $publicRole = Role::create(['role_name' => 'Masyarakat']);
        $user = User::factory()->create(['email' => 'existing@example.com', 'role_id' => $publicRole->getKey(), 'account_status' => 'inactive']);
        $oldHash = $user->password;

        $this->artisan('aksesibel:create-manager')
            ->expectsQuestion('Nama pengelola', 'Pengambil Alih')
            ->expectsQuestion('Email', $user->email)
            ->expectsQuestion('Password (minimal 12 karakter)', self::PASSWORD)
            ->expectsQuestion('Konfirmasi password', self::PASSWORD)
            ->doesntExpectOutputToContain(self::PASSWORD)
            ->assertFailed();

        $this->assertDatabaseCount('users', 1);
        $this->assertSame($oldHash, $user->fresh()->password);
        $this->assertSame($publicRole->getKey(), $user->fresh()->role_id);
        $this->assertSame('inactive', $user->fresh()->account_status);
    }

    public function test_invalid_confirmation_does_not_create_an_account(): void
    {
        Role::create(['role_name' => 'Pengelola Fasilitas']);

        $this->artisan('aksesibel:create-manager')
            ->expectsQuestion('Nama pengelola', 'Pengelola Baru')
            ->expectsQuestion('Email', 'pengelola@example.com')
            ->expectsQuestion('Password (minimal 12 karakter)', self::PASSWORD)
            ->expectsQuestion('Konfirmasi password', 'Password-Berbeda-2026')
            ->doesntExpectOutputToContain(self::PASSWORD)
            ->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_competing_email_insert_returns_a_safe_error_and_preserves_the_first_account(): void
    {
        Role::create(['role_name' => 'Pengelola Fasilitas']);
        $publicRole = Role::create(['role_name' => 'Masyarakat']);
        $existingHash = Hash::make('Password-Pemilik-2026');
        $injected = false;
        $dispatcher = User::getEventDispatcher();
        User::setEventDispatcher(clone $dispatcher);

        try {
            User::creating(function (User $candidate) use (&$injected, $publicRole, $existingHash): void {
                if ($injected || $candidate->email !== 'bersamaan@example.com') {
                    return;
                }

                $injected = true;
                DB::table('users')->insert([
                    'name' => 'Pemilik Akun Pertama',
                    'email' => $candidate->email,
                    'password' => $existingHash,
                    'role_id' => $publicRole->getKey(),
                    'account_status' => 'inactive',
                    'created_at' => '2026-11-01 10:00:00',
                    'updated_at' => '2026-11-01 10:00:00',
                ]);
            });

            $this->artisan('aksesibel:create-manager')
                ->expectsQuestion('Nama pengelola', 'Pengelola yang Kalah Cepat')
                ->expectsQuestion('Email', 'bersamaan@example.com')
                ->expectsQuestion('Password (minimal 12 karakter)', self::PASSWORD)
                ->expectsQuestion('Konfirmasi password', self::PASSWORD)
                ->expectsOutputToContain('Email sudah digunakan. Akun lama tidak diubah.')
                ->doesntExpectOutputToContain(self::PASSWORD)
                ->doesntExpectOutputToContain($existingHash)
                ->doesntExpectOutputToContain('SQLSTATE')
                ->doesntExpectOutputToContain('insert into')
                ->assertFailed();
        } finally {
            User::setEventDispatcher($dispatcher);
        }

        $this->assertTrue($injected);
        $this->assertDatabaseCount('users', 1);
        $user = User::where('email', 'bersamaan@example.com')->firstOrFail();
        $this->assertSame('Pemilik Akun Pertama', $user->name);
        $this->assertSame($existingHash, $user->password);
        $this->assertSame($publicRole->getKey(), $user->role_id);
        $this->assertSame('inactive', $user->account_status);
    }
}
