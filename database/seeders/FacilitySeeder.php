<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * CATATAN: Koordinat di bawah adalah perkiraan — perlu diverifikasi
 * sebelum digunakan di produksi. Lihat README untuk detail.
 */
class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = DB::table('facility_categories')->pluck('category_id', 'slug');

        // Ambil region_id berdasarkan kode Kemendagri
        $regions = DB::table('regions')->pluck('region_id', 'code');

        $facilities = [
            [
                'category_slug'  => 'gedung-pelayanan-publik',
                'name'           => 'Perpustakaan Jakarta – Cikini',
                'description'    => 'Perpustakaan umum milik Pemerintah Provinsi DKI Jakarta yang menyediakan koleksi buku dan fasilitas baca. Gedung telah dilengkapi dengan jalur kursi roda, lift, dan toilet difabel di setiap lantai.',
                'address'        => 'Jl. Cikini Raya No.73',
                'city'           => 'Jakarta',
                'postal_code'    => '10330',
                // Kelurahan Cikini, Kec. Menteng, Kota Jakarta Pusat, DKI Jakarta
                'kelurahan_code' => '31.71.01.1003',
                'kecamatan_code' => '31.71.01',
                'kota_code'      => '31.71',
                'provinsi_code'  => '31',
                'latitude'       => -6.1944,
                'longitude'      => 106.8373,
                'coord_source'   => 'GEOKODING',
                'location_note'  => 'Gedung berada di sisi selatan Jl. Cikini Raya, dekat Taman Ismail Marzuki.',
                'entrance_note'  => 'Pintu masuk aksesibel tersedia di sisi kiri gedung dengan ramp dan tombol otomatis.',
                'nearby_transit' => 'Halte TransJakarta Cikini 400m, Stasiun Cikini 300m',
                'phone'          => '(021) 3140050',
                'email'          => 'info@perpustakaanjakarta.go.id',
                'status'         => 'AKSES_PENUH',
            ],
            [
                'category_slug'  => 'transportasi',
                'name'           => 'Stasiun MRT Dukuh Atas BNI',
                'description'    => 'Stasiun MRT Jakarta yang terletak di kawasan Dukuh Atas. Dilengkapi dengan elevator, eskalator, guiding block, dan jalur kursi roda yang terhubung ke stasiun KRL dan halte TransJakarta.',
                'address'        => 'Jl. Jend. Sudirman, Dukuh Atas',
                'city'           => 'Jakarta',
                'postal_code'    => '10220',
                'kelurahan_code' => '31.74.01.1004',  // Karet Semanggi
                'kecamatan_code' => '31.74.01',
                'kota_code'      => '31.74',
                'provinsi_code'  => '31',
                'latitude'       => -6.2009,
                'longitude'      => 106.8228,
                'coord_source'   => 'GEOKODING',
                'location_note'  => 'Terletak di bawah Jl. Sudirman, pintu masuk utama dari sisi barat dan timur.',
                'entrance_note'  => 'Elevator tersedia di semua pintu masuk. Lantai taktil tersedia sepanjang peron.',
                'nearby_transit' => 'Stasiun KRL Sudirman 150m, Halte TransJakarta Dukuh Atas 1 & 2 100m',
                'phone'          => '(021) 2708888',
                'email'          => 'cs@jakartamrt.co.id',
                'status'         => 'AKSES_PENUH',
            ],
            [
                'category_slug'  => 'taman',
                'name'           => 'Taman Literasi Martha Tiahahu',
                'description'    => 'Taman literasi terbuka di kawasan Blok M yang dilengkapi area baca, ruang diskusi, dan kafe. Jalur pejalan kaki sudah aksesibel namun beberapa area bertekstur kerikil yang menyulitkan pengguna kursi roda.',
                'address'        => 'Jl. Sisingamangaraja, Blok M',
                'city'           => 'Jakarta',
                'postal_code'    => '12110',
                'kelurahan_code' => null,
                'kecamatan_code' => null,
                'kota_code'      => '31.74',
                'provinsi_code'  => '31',
                'latitude'       => -6.2441,
                'longitude'      => 106.7978,
                'coord_source'   => 'GEOKODING',
                'location_note'  => 'Berada di depan Terminal Blok M, seberang Mal Blok M.',
                'entrance_note'  => 'Jalur masuk utama sudah rata. Area kerikil di sekitar patung Martha Tiahahu masih menjadi kendala.',
                'nearby_transit' => 'Halte TransJakarta Blok M 100m, Stasiun MRT Blok M BCA 200m',
                'phone'          => null,
                'email'          => null,
                'status'         => 'AKSES_SEBAGIAN',
            ],
            [
                'category_slug'  => 'rumah-sakit',
                'name'           => 'RSUD Tarakan Jakarta',
                'description'    => 'Rumah sakit umum daerah milik Pemerintah DKI Jakarta. Memiliki jalur kursi roda, parkir difabel, dan layanan prioritas untuk penyandang disabilitas. Proses renovasi lantai 3 masih berlangsung.',
                'address'        => 'Jl. Kyai Caringin No.7, Cideng',
                'city'           => 'Jakarta',
                'postal_code'    => '10150',
                'kelurahan_code' => '31.71.02.1002',  // Cideng
                'kecamatan_code' => '31.71.02',
                'kota_code'      => '31.71',
                'provinsi_code'  => '31',
                'latitude'       => -6.1693,
                'longitude'      => 106.8148,
                'coord_source'   => 'GEOKODING',
                'location_note'  => 'Di sisi selatan Jl. Kyai Caringin, dekat Stasiun Tanah Abang.',
                'entrance_note'  => 'Pintu IGD aksesibel 24 jam. Pintu poliklinik dilengkapi ramp dan kursi roda tersedia di resepsi.',
                'nearby_transit' => 'Stasiun KRL Tanah Abang 700m, Halte TransJakarta Tanah Abang 500m',
                'phone'          => '(021) 6301548',
                'email'          => 'rsudtarakan@jakarta.go.id',
                'status'         => 'AKSES_SEBAGIAN',
            ],
            [
                'category_slug'  => 'gedung-pelayanan-publik',
                'name'           => 'Balai Kota DKI Jakarta',
                'description'    => 'Pusat pemerintahan Provinsi DKI Jakarta. Akses ke lobi utama tersedia melalui ramp, namun tidak semua gedung perkantoran di kompleks ini memiliki fasilitas aksesibel.',
                'address'        => 'Jl. Medan Merdeka Selatan No.8-9',
                'city'           => 'Jakarta',
                'postal_code'    => '10110',
                'kelurahan_code' => '31.71.02.1001',  // Gambir
                'kecamatan_code' => '31.71.02',
                'kota_code'      => '31.71',
                'provinsi_code'  => '31',
                'latitude'       => -6.1702,
                'longitude'      => 106.8296,
                'coord_source'   => 'GEOKODING',
                'location_note'  => 'Kompleks Balai Kota menghadap Lapangan Monas sisi selatan.',
                'entrance_note'  => 'Pintu masuk utama (Jl. Medan Merdeka Selatan) ada ramp. Gedung B dan C belum aksesibel penuh.',
                'nearby_transit' => 'Halte TransJakarta Balai Kota 50m, Stasiun MRT Bundaran HI 1,2km',
                'phone'          => '(021) 3822720',
                'email'          => null,
                'status'         => 'BELUM_AKSESIBEL',
            ],
            [
                'category_slug'  => 'transportasi',
                'name'           => 'Terminal Kampung Melayu',
                'description'    => 'Terminal bus antarkota dan dalam kota di Jakarta Timur. Kondisi aksesibilitas sedang dalam proses evaluasi oleh Dinas Perhubungan DKI Jakarta.',
                'address'        => 'Jl. Jatinegara Barat No.1, Kampung Melayu',
                'city'           => 'Jakarta',
                'postal_code'    => '13330',
                'kelurahan_code' => null,
                'kecamatan_code' => '31.75.01',  // Jatinegara
                'kota_code'      => '31.75',
                'provinsi_code'  => '31',
                'latitude'       => -6.2178,
                'longitude'      => 106.8681,
                'coord_source'   => 'GEOKODING',
                'location_note'  => 'Di perempatan Jatinegara Barat – Jl. Jatinegara Timur.',
                'entrance_note'  => null,
                'nearby_transit' => 'Stasiun KRL Jatinegara 500m, Halte TransJakarta Kampung Melayu 200m',
                'phone'          => '(021) 8191047',
                'email'          => null,
                'status'         => 'MENUNGGU_KONFIRMASI',
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
        $operatingHoursMRT = json_encode(array_fill_keys(
            ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'],
            ['open' => '05:00', 'close' => '24:00']
        ));
        $operatingHoursTaman = json_encode(array_fill_keys(
            ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'],
            ['open' => '06:00', 'close' => '22:00']
        ));
        $operatingHoursRs = json_encode(array_fill_keys(
            ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'],
            ['buka_24_jam' => true]
        ));

        $operatingHoursMap = [
            'Perpustakaan Jakarta – Cikini' => $operatingHoursDefault,
            'Stasiun MRT Dukuh Atas BNI'    => $operatingHoursMRT,
            'Taman Literasi Martha Tiahahu' => $operatingHoursTaman,
            'RSUD Tarakan Jakarta'          => $operatingHoursRs,
            'Balai Kota DKI Jakarta'        => $operatingHoursDefault,
            'Terminal Kampung Melayu'       => $operatingHoursMRT,
        ];

        foreach ($facilities as $data) {
            $categoryId  = $categoryIds[$data['category_slug']] ?? null;
            $regionId    = $data['kelurahan_code']  ? ($regions[$data['kelurahan_code']]  ?? null) : null;
            $districtId  = $data['kecamatan_code']  ? ($regions[$data['kecamatan_code']]  ?? null) : null;
            $cityRegId   = $data['kota_code']       ? ($regions[$data['kota_code']]       ?? null) : null;
            $provinceId  = $data['provinsi_code']   ? ($regions[$data['provinsi_code']]   ?? null) : null;

            DB::table('facilities')->updateOrInsert(
                ['name' => $data['name']],
                [
                    'category_id'            => $categoryId,
                    'name'                   => $data['name'],
                    'description'            => $data['description'],
                    'address'                => $data['address'],
                    'city'                   => $data['city'],
                    'region_id'              => $regionId,
                    'district_id'            => $districtId,
                    'city_region_id'         => $cityRegId,
                    'province_id'            => $provinceId,
                    'postal_code'            => $data['postal_code'],
                    'latitude'               => $data['latitude'],
                    'longitude'              => $data['longitude'],
                    'coordinate_source'      => $data['coord_source'],
                    'coordinate_updated_at'  => now(),
                    'location_note'          => $data['location_note'],
                    'entrance_note'          => $data['entrance_note'],
                    'nearby_transit'         => $data['nearby_transit'],
                    'operating_hours'        => $operatingHoursMap[$data['name']],
                    'contact'                => $data['phone'],
                    'phone'                  => $data['phone'],
                    'email'                  => $data['email'],
                    'facility_status'        => 'active',
                    'accessibility_status'   => $data['status'],
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]
            );
        }
    }
}
