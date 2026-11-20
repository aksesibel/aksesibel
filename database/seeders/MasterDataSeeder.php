<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data master awal. Aman dijalankan berulang kali (tidak membuat duplikat).
 */
class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed('roles', 'role_name', [
            ['role_name' => 'Admin'],
            ['role_name' => 'Masyarakat'],
            ['role_name' => 'Verifikator'],
            ['role_name' => 'Petugas Tindak Lanjut'],
        ]);

        $this->seed('verification_statuses', 'status_name', [
            ['status_name' => 'Menunggu Verifikasi'],
            ['status_name' => 'Terverifikasi'],
            ['status_name' => 'Ditolak'],
            ['status_name' => 'Dalam Tindak Lanjut'],
            ['status_name' => 'Selesai'],
        ]);

        // Kategori fasilitas — 4 kategori utama yang dapat dikelola admin
        $categories = [
            [
                'category_name' => 'Transportasi',
                'slug'          => 'transportasi',
                'description'   => 'Stasiun, terminal, halte, bandara, dan fasilitas transportasi publik lainnya.',
                'icon'          => 'Train',
                'is_active'     => true,
                'sort_order'    => 1,
            ],
            [
                'category_name' => 'Taman',
                'slug'          => 'taman',
                'description'   => 'Taman kota, ruang terbuka hijau, dan taman bermain.',
                'icon'          => 'Trees',
                'is_active'     => true,
                'sort_order'    => 2,
            ],
            [
                'category_name' => 'Rumah Sakit',
                'slug'          => 'rumah-sakit',
                'description'   => 'Rumah sakit, puskesmas, klinik, dan fasilitas kesehatan lainnya.',
                'icon'          => 'Hospital',
                'is_active'     => true,
                'sort_order'    => 3,
            ],
            [
                'category_name' => 'Gedung Pelayanan Publik',
                'slug'          => 'gedung-pelayanan-publik',
                'description'   => 'Kantor kelurahan, kecamatan, Samsat, perpustakaan, dan kantor pelayanan publik lainnya.',
                'icon'          => 'Landmark',
                'is_active'     => true,
                'sort_order'    => 4,
            ],
        ];

        foreach ($categories as $cat) {
            DB::table('facility_categories')->updateOrInsert(
                ['slug' => $cat['slug']],
                $cat
            );
        }

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
            DB::table($table)->updateOrInsert([$key => $row[$key]], $row);
        }
    }
}
