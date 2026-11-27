<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed wilayah DKI Jakarta — dapat dijalankan ulang tanpa duplikasi.
 * Mencakup: 1 provinsi, 5 kota/kab administrasi, seluruh kecamatan,
 * dan kelurahan untuk Kec. Menteng, Gambir, Tanah Abang, Setiabudi.
 */
class RegionSeeder extends Seeder
{
    public function run(): void
    {
        // ---- 1. Provinsi ----
        $provinsiId = $this->upsert('31', 'DKI Jakarta', 'PROVINSI', null);

        // ---- 2. Kota / Kabupaten Administrasi ----
        $jakpus  = $this->upsert('31.71', 'Kota Jakarta Pusat',   'KOTA_KABUPATEN', $provinsiId);
        $jakut   = $this->upsert('31.72', 'Kota Jakarta Utara',   'KOTA_KABUPATEN', $provinsiId);
        $jakbar  = $this->upsert('31.73', 'Kota Jakarta Barat',   'KOTA_KABUPATEN', $provinsiId);
        $jaksel  = $this->upsert('31.74', 'Kota Jakarta Selatan', 'KOTA_KABUPATEN', $provinsiId);
        $jaktim  = $this->upsert('31.75', 'Kota Jakarta Timur',   'KOTA_KABUPATEN', $provinsiId);
        $kepu    = $this->upsert('31.01', 'Kab. Kepulauan Seribu', 'KOTA_KABUPATEN', $provinsiId);

        // ---- 3. Kecamatan Jakarta Pusat ----
        $menteng     = $this->upsert('31.71.01', 'Menteng',     'KECAMATAN', $jakpus);
        $gambir      = $this->upsert('31.71.02', 'Gambir',      'KECAMATAN', $jakpus);
        $tanahAbang  = $this->upsert('31.71.03', 'Tanah Abang', 'KECAMATAN', $jakpus);
        $sawahBesar  = $this->upsert('31.71.04', 'Sawah Besar', 'KECAMATAN', $jakpus);
        $kemayoran   = $this->upsert('31.71.05', 'Kemayoran',   'KECAMATAN', $jakpus);
        $senenKec    = $this->upsert('31.71.06', 'Senen',       'KECAMATAN', $jakpus);
        $cetK        = $this->upsert('31.71.07', 'Cempaka Putih','KECAMATAN', $jakpus);
        $johar       = $this->upsert('31.71.08', 'Johar Baru',  'KECAMATAN', $jakpus);

        // ---- 3b. Kecamatan Jakarta Selatan ----
        $setiabudi   = $this->upsert('31.74.01', 'Setiabudi',    'KECAMATAN', $jaksel);
        $tebet       = $this->upsert('31.74.02', 'Tebet',        'KECAMATAN', $jaksel);
        $mpasca      = $this->upsert('31.74.03', 'Mampang Prapatan', 'KECAMATAN', $jaksel);
        $pasar       = $this->upsert('31.74.04', 'Pasar Minggu', 'KECAMATAN', $jaksel);
        $kebJr       = $this->upsert('31.74.05', 'Kebayoran Baru','KECAMATAN', $jaksel);
        $kebLm       = $this->upsert('31.74.06', 'Kebayoran Lama','KECAMATAN', $jaksel);
        $cilandak    = $this->upsert('31.74.07', 'Cilandak',     'KECAMATAN', $jaksel);
        $pesanggrahan= $this->upsert('31.74.08', 'Pesanggrahan', 'KECAMATAN', $jaksel);
        $jagakarsa   = $this->upsert('31.74.09', 'Jagakarsa',    'KECAMATAN', $jaksel);
        $pancoran    = $this->upsert('31.74.10', 'Pancoran',     'KECAMATAN', $jaksel);

        // ---- 3c. Kecamatan Jakarta Timur ----
        $kampMelayu  = $this->upsert('31.75.01', 'Jatinegara',   'KECAMATAN', $jaktim);
        $matraman    = $this->upsert('31.75.02', 'Matraman',     'KECAMATAN', $jaktim);
        $pulo        = $this->upsert('31.75.03', 'Pulo Gadung',  'KECAMATAN', $jaktim);
        $cakung      = $this->upsert('31.75.04', 'Cakung',       'KECAMATAN', $jaktim);
        $duren       = $this->upsert('31.75.05', 'Duren Sawit',  'KECAMATAN', $jaktim);
        $kramat      = $this->upsert('31.75.06', 'Kramat Jati',  'KECAMATAN', $jaktim);
        $pasar2      = $this->upsert('31.75.07', 'Pasar Rebo',   'KECAMATAN', $jaktim);
        $ciracas     = $this->upsert('31.75.08', 'Ciracas',      'KECAMATAN', $jaktim);
        $cipayung    = $this->upsert('31.75.09', 'Cipayung',     'KECAMATAN', $jaktim);

        // ---- 4. Kelurahan Kec. Menteng ----
        $this->upsert('31.71.01.1001', 'Menteng',         'KELURAHAN', $menteng);
        $this->upsert('31.71.01.1002', 'Pegangsaan',      'KELURAHAN', $menteng);
        $this->upsert('31.71.01.1003', 'Cikini',          'KELURAHAN', $menteng);
        $this->upsert('31.71.01.1004', 'Gondangdia',      'KELURAHAN', $menteng);
        $this->upsert('31.71.01.1005', 'Kebon Sirih',     'KELURAHAN', $menteng);

        // ---- 4b. Kelurahan Kec. Gambir ----
        $this->upsert('31.71.02.1001', 'Gambir',          'KELURAHAN', $gambir);
        $this->upsert('31.71.02.1002', 'Cideng',          'KELURAHAN', $gambir);
        $this->upsert('31.71.02.1003', 'Petojo Utara',    'KELURAHAN', $gambir);
        $this->upsert('31.71.02.1004', 'Petojo Selatan',  'KELURAHAN', $gambir);
        $this->upsert('31.71.02.1005', 'Duri Pulo',       'KELURAHAN', $gambir);
        $this->upsert('31.71.02.1006', 'Kebon Kelapa',    'KELURAHAN', $gambir);

        // ---- 4c. Kelurahan Kec. Tanah Abang ----
        $this->upsert('31.71.03.1001', 'Tanah Abang',     'KELURAHAN', $tanahAbang);
        $this->upsert('31.71.03.1002', 'Kampung Bali',    'KELURAHAN', $tanahAbang);
        $this->upsert('31.71.03.1003', 'Kebon Melati',    'KELURAHAN', $tanahAbang);
        $this->upsert('31.71.03.1004', 'Kebon Kacang',    'KELURAHAN', $tanahAbang);
        $this->upsert('31.71.03.1005', 'Bendungan Hilir', 'KELURAHAN', $tanahAbang);
        $this->upsert('31.71.03.1006', 'Karet Tengsin',   'KELURAHAN', $tanahAbang);
        $this->upsert('31.71.03.1007', 'Petamburan',      'KELURAHAN', $tanahAbang);

        // ---- 4d. Kelurahan Kec. Setiabudi ----
        $this->upsert('31.74.01.1001', 'Setiabudi',       'KELURAHAN', $setiabudi);
        $this->upsert('31.74.01.1002', 'Kuningan Timur',  'KELURAHAN', $setiabudi);
        $this->upsert('31.74.01.1003', 'Karet',           'KELURAHAN', $setiabudi);
        $this->upsert('31.74.01.1004', 'Karet Semanggi',  'KELURAHAN', $setiabudi);
        $this->upsert('31.74.01.1005', 'Karet Kuningan',  'KELURAHAN', $setiabudi);
        $this->upsert('31.74.01.1006', 'Guntur',          'KELURAHAN', $setiabudi);
        $this->upsert('31.74.01.1007', 'Pasar Manggis',   'KELURAHAN', $setiabudi);
        $this->upsert('31.74.01.1008', 'Menteng Atas',    'KELURAHAN', $setiabudi);
    }

    /**
     * Insert or update region, kembalikan region_id.
     */
    private function upsert(string $code, string $name, string $level, ?int $parentId): int
    {
        $existing = DB::table('regions')->where('code', $code)->first();

        if ($existing) {
            DB::table('regions')->where('code', $code)->update([
                'name'       => $name,
                'level'      => $level,
                'parent_id'  => $parentId,
                'updated_at' => now(),
            ]);
            return $existing->region_id;
        }

        return DB::table('regions')->insertGetId([
            'code'       => $code,
            'name'       => $name,
            'level'      => $level,
            'parent_id'  => $parentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
