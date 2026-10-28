<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Data master harus masuk lebih dulu (roles, status, kategori, dll.)
        $this->call(MasterDataSeeder::class);

        // User tes untuk login saat development (password: "password")
        $adminRoleId = DB::table('roles')->where('role_name', 'Admin')->value('role_id');

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'role_id' => $adminRoleId,
            ]
        );
    }
}
