<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReportTestingSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // 1. Ambil kategori fasilitas
            $categoryId = DB::table('facility_categories')
                ->where('category_name', 'Taman')
                ->value('category_id');

            if (!$categoryId) {
                throw new RuntimeException(
                    'Kategori fasilitas Taman belum tersedia.'
                );
            }

            // 2. Buat atau perbarui fasilitas pengujian
            DB::table('facilities')->updateOrInsert(
                [
                    'name' => '[UJI PBI] Taman Kota Aksesibel',
                ],
                [
                    'category_id' => $categoryId,
                    'description' => 'Fasilitas taman publik untuk pengujian modul laporan masyarakat.',
                    'address' => 'Bandung, Jawa Barat',
                    'operating_hours' => '08.00 - 17.00',
                    'contact' => '0220000000',
                    'facility_status' => 'active',
                ]
            );

            $facilityId = DB::table('facilities')
                ->where('name', '[UJI PBI] Taman Kota Aksesibel')
                ->value('facility_id');

            // 3. Tambahkan item aksesibilitas
            $accessibilityItems = [
                [
                    'type_name' => 'Ramp / Jalur Kursi Roda',
                    'location' => 'Pintu masuk utama',
                    'condition' => 'Baik',
                    'availability' => 'Tersedia',
                    'notes' => 'Ramp untuk akses pengguna kursi roda.',
                ],
                [
                    'type_name' => 'Toilet Difabel',
                    'location' => 'Area toilet sebelah timur',
                    'condition' => 'Baik',
                    'availability' => 'Tersedia',
                    'notes' => 'Toilet khusus pengguna disabilitas.',
                ],
            ];

            foreach ($accessibilityItems as $item) {
                $typeId = DB::table('accessibility_types')
                    ->where('type_name', $item['type_name'])
                    ->value('accessibility_type_id');

                if (!$typeId) {
                    throw new RuntimeException(
                        'Jenis aksesibilitas tidak ditemukan: ' .
                        $item['type_name']
                    );
                }

                DB::table('facility_accessibilities')->updateOrInsert(
                    [
                        'facility_id' => $facilityId,
                        'accessibility_type_id' => $typeId,
                        'location_description' => $item['location'],
                    ],
                    [
                        'condition_status' => $item['condition'],
                        'availability_status' => $item['availability'],
                        'notes' => $item['notes'],
                    ]
                );
            }

            // 4. Buat periode pelaporan pengujian
            DB::table('reporting_periods')->updateOrInsert(
                [
                    'period_name' => '[UJI PBI] Periode November-Desember 2026',
                ],
                [
                    'start_date' => '2026-11-01',
                    'end_date' => '2026-12-31',
                ]
            );
        });
    }
}
