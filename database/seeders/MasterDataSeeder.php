<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data master bersama. Peran mengikuti proposal; peran lama tetap dipertahankan.
 * Aman dijalankan berulang kali (tidak membuat duplikat).
 */
class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed('roles', 'role_name', [
            ['role_name' => 'Admin'],
            ['role_name' => 'Masyarakat'],
            ['role_name' => 'Pengelola Fasilitas'],
        ]);

        $this->seed('verification_statuses', 'status_name', [
            ['status_name' => 'Menunggu Verifikasi'],
            ['status_name' => 'Terverifikasi'],
            ['status_name' => 'Ditolak'],
            ['status_name' => 'Dalam Tindak Lanjut'],
            ['status_name' => 'Selesai'],
        ]);

        $this->seed('facility_categories', 'category_name', [
            ['category_name' => 'Taman'],
            ['category_name' => 'Fasilitas Kesehatan'],
            ['category_name' => 'Pendidikan'],
            ['category_name' => 'Transportasi Umum'],
            ['category_name' => 'Perkantoran Pemerintah'],
        ]);

        $this->seed('accessibility_types', 'type_name', [
            ['type_name' => 'Ramp / Jalur Kursi Roda'],
            ['type_name' => 'Toilet Difabel'],
            ['type_name' => 'Guiding Block'],
            ['type_name' => 'Parkir Difabel'],
            ['type_name' => 'Lift'],
            ['type_name' => 'Handrail'],
        ]);

        $this->seed('issue_categories', 'category_name', [
            ['category_name' => 'Fasilitas rusak'],
            ['category_name' => 'Fasilitas tidak tersedia'],
            ['category_name' => 'Akses terhalang'],
            ['category_name' => 'Informasi tidak sesuai'],
        ]);
    }

    private function seed(string $table, string $key, array $rows): void
    {
        foreach ($rows as $row) {
            if (! DB::table($table)->where($key, $row[$key])->exists()) {
                DB::table($table)->insert($row);
            }
        }
    }
}
