<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        // Ambil category_id berdasarkan slug (lebih stabil daripada nama)
        $categoryIds = DB::table('facility_categories')
            ->pluck('category_id', 'slug');

        $facilities = [
            [
                'category_slug' => 'gedung-pelayanan-publik',   // Perpustakaan → Gedung Pelayanan Publik
                'name'          => 'Perpustakaan Jakarta – Cikini',
                'description'   => 'Perpustakaan umum milik Pemerintah Provinsi DKI Jakarta yang menyediakan koleksi buku dan fasilitas baca. Gedung telah dilengkapi dengan jalur kursi roda, lift, dan toilet difabel di setiap lantai.',
                'address'       => 'Jl. Cikini Raya No.73',
                'city'          => 'Jakarta',
                'phone'         => '(021) 3140050',
                'email'         => 'info@perpustakaanjakarta.go.id',
                'status'        => 'AKSES_PENUH',
            ],
            [
                'category_slug' => 'transportasi',
                'name'          => 'Stasiun MRT Dukuh Atas BNI',
                'description'   => 'Stasiun MRT Jakarta yang terletak di kawasan Dukuh Atas. Dilengkapi dengan elevator, eskalator, guiding block, dan jalur kursi roda yang terhubung ke stasiun KRL dan halte TransJakarta.',
                'address'       => 'Jl. Sudirman, Dukuh Atas',
                'city'          => 'Jakarta',
                'phone'         => '(021) 2708888',
                'email'         => 'cs@jakartamrt.co.id',
                'status'        => 'AKSES_PENUH',
            ],
            [
                'category_slug' => 'taman',
                'name'          => 'Taman Literasi Martha Tiahahu',
                'description'   => 'Taman literasi terbuka di kawasan Blok M yang dilengkapi area baca, ruang diskusi, dan kafe. Jalur pejalan kaki sudah aksesibel namun beberapa area bertekstur kerikil yang menyulitkan pengguna kursi roda.',
                'address'       => 'Jl. Sisingamangaraja, Blok M',
                'city'          => 'Jakarta',
                'phone'         => null,
                'email'         => null,
                'status'        => 'AKSES_SEBAGIAN',
            ],
            [
                'category_slug' => 'rumah-sakit',
                'name'          => 'RSUD Tarakan Jakarta',
                'description'   => 'Rumah sakit umum daerah milik Pemerintah DKI Jakarta. Memiliki jalur kursi roda, parkir difabel, dan layanan prioritas untuk penyandang disabilitas. Proses renovasi lantai 3 masih berlangsung.',
                'address'       => 'Jl. Kyai Caringin No.7, Cideng',
                'city'          => 'Jakarta',
                'phone'         => '(021) 6301548',
                'email'         => 'rsudtarakan@jakarta.go.id',
                'status'        => 'AKSES_SEBAGIAN',
            ],
            [
                'category_slug' => 'gedung-pelayanan-publik',
                'name'          => 'Balai Kota DKI Jakarta',
                'description'   => 'Pusat pemerintahan Provinsi DKI Jakarta. Akses ke lobi utama tersedia melalui ramp, namun tidak semua gedung perkantoran di kompleks ini memiliki fasilitas aksesibel.',
                'address'       => 'Jl. Medan Merdeka Selatan No.8-9',
                'city'          => 'Jakarta',
                'phone'         => '(021) 3822720',
                'email'         => null,
                'status'        => 'BELUM_AKSESIBEL',
            ],
            [
                'category_slug' => 'transportasi',
                'name'          => 'Terminal Kampung Melayu',
                'description'   => 'Terminal bus antarkota dan dalam kota di Jakarta Timur. Kondisi aksesibilitas sedang dalam proses evaluasi oleh Dinas Perhubungan DKI Jakarta.',
                'address'       => 'Jl. Jatinegara Barat, Kampung Melayu',
                'city'          => 'Jakarta',
                'phone'         => '(021) 8191047',
                'email'         => null,
                'status'        => 'MENUNGGU_KONFIRMASI',
            ],
        ];

        $operatingHoursDefault = json_encode([
            'senin'  => ['open' => '08:00', 'close' => '17:00'],
            'selasa' => ['open' => '08:00', 'close' => '17:00'],
            'rabu'   => ['open' => '08:00', 'close' => '17:00'],
            'kamis'  => ['open' => '08:00', 'close' => '17:00'],
            'jumat'  => ['open' => '08:00', 'close' => '16:30'],
            'sabtu'  => ['open' => '09:00', 'close' => '15:00'],
            'minggu' => ['closed' => true],
        ]);

        $operatingHoursMRT = json_encode([
            'senin'  => ['open' => '05:00', 'close' => '24:00'],
            'selasa' => ['open' => '05:00', 'close' => '24:00'],
            'rabu'   => ['open' => '05:00', 'close' => '24:00'],
            'kamis'  => ['open' => '05:00', 'close' => '24:00'],
            'jumat'  => ['open' => '05:00', 'close' => '24:00'],
            'sabtu'  => ['open' => '05:00', 'close' => '24:00'],
            'minggu' => ['open' => '05:00', 'close' => '24:00'],
        ]);

        $operatingHoursTaman = json_encode([
            'senin'  => ['open' => '06:00', 'close' => '22:00'],
            'selasa' => ['open' => '06:00', 'close' => '22:00'],
            'rabu'   => ['open' => '06:00', 'close' => '22:00'],
            'kamis'  => ['open' => '06:00', 'close' => '22:00'],
            'jumat'  => ['open' => '06:00', 'close' => '22:00'],
            'sabtu'  => ['open' => '06:00', 'close' => '22:00'],
            'minggu' => ['open' => '06:00', 'close' => '22:00'],
        ]);

        $operatingHoursRs = json_encode([
            'senin'  => ['buka_24_jam' => true],
            'selasa' => ['buka_24_jam' => true],
            'rabu'   => ['buka_24_jam' => true],
            'kamis'  => ['buka_24_jam' => true],
            'jumat'  => ['buka_24_jam' => true],
            'sabtu'  => ['buka_24_jam' => true],
            'minggu' => ['buka_24_jam' => true],
        ]);

        $operatingHoursMap = [
            'Perpustakaan Jakarta – Cikini' => $operatingHoursDefault,
            'Stasiun MRT Dukuh Atas BNI'    => $operatingHoursMRT,
            'Taman Literasi Martha Tiahahu' => $operatingHoursTaman,
            'RSUD Tarakan Jakarta'          => $operatingHoursRs,
            'Balai Kota DKI Jakarta'        => $operatingHoursDefault,
            'Terminal Kampung Melayu'       => $operatingHoursMRT,
        ];

        foreach ($facilities as $data) {
            $categoryId = $categoryIds[$data['category_slug']] ?? null;

            DB::table('facilities')->updateOrInsert(
                ['name' => $data['name']],
                [
                    'category_id'          => $categoryId,
                    'name'                 => $data['name'],
                    'description'          => $data['description'],
                    'address'              => $data['address'],
                    'city'                 => $data['city'],
                    'operating_hours'      => $operatingHoursMap[$data['name']],
                    'contact'              => $data['phone'],
                    'phone'                => $data['phone'],
                    'email'                => $data['email'],
                    'facility_status'      => 'active',
                    'accessibility_status' => $data['status'],
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]
            );
        }
    }
}
