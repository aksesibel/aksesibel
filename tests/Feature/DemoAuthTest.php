<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoAuthTest extends TestCase
{
    use RefreshDatabase;

    private Role $roleAdmin;
    private Role $roleOfficer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleAdmin = Role::create(['role_name' => 'Admin']);
        $this->roleOfficer = Role::create(['role_name' => 'Petugas Tindak Lanjut']);
    }

    public function test_demo_login_as_admin_succeeds_and_redirects_to_ready_reports(): void
    {
        $admin = User::factory()->create([
            'role_id'        => $this->roleAdmin->role_id,
            'account_status' => 'active',
        ]);

        $response = $this->get('/login-as/admin');

        $response->assertRedirect(route('follow-up-assignments.ready-reports'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_demo_login_as_officer_succeeds_and_redirects_to_index(): void
    {
        $officer = User::factory()->create([
            'role_id'        => $this->roleOfficer->role_id,
            'account_status' => 'active',
        ]);

        $response = $this->get('/login-as/petugas');

        $response->assertRedirect(route('follow-up-assignments.index'));
        $this->assertAuthenticatedAs($officer);
    }

    public function test_demo_login_as_rejects_unknown_role_with_404(): void
    {
        // Pastikan role tidak dikenal TIDAK jatuh ke akun Admin
        User::factory()->create([
            'role_id'        => $this->roleAdmin->role_id,
            'account_status' => 'active',
        ]);

        $response = $this->get('/login-as/guest');
        $response->assertNotFound();
        $this->assertGuest();

        $response2 = $this->get('/login-as/random_role');
        $response2->assertNotFound();
        $this->assertGuest();
    }

    public function test_demo_login_as_rejects_inactive_account_with_403(): void
    {
        // Akun admin ada tetapi status inactive
        User::factory()->create([
            'role_id'        => $this->roleAdmin->role_id,
            'account_status' => 'inactive',
        ]);

        $response = $this->get('/login-as/admin');
        $response->assertForbidden();
        $this->assertGuest();
    }

    public function test_demo_login_as_denied_in_production_environment(): void
    {
        User::factory()->create([
            'role_id'        => $this->roleAdmin->role_id,
            'account_status' => 'active',
        ]);

        // Simulasikan lingkungan produksi
        $this->app['env'] = 'production';

        $response = $this->get('/login-as/admin');
        $response->assertNotFound();
        $this->assertGuest();
    }

    public function test_login_route_accessible_for_auth_redirection(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk();
    }
}
