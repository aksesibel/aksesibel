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

        // Ambil role IDs
        $adminRoleId = DB::table('roles')->where('role_name', 'Admin')->value('role_id');
        $officerRoleId = DB::table('roles')->where('role_name', 'Petugas Tindak Lanjut')->value('role_id');
        $citizenRoleId = DB::table('roles')->where('role_name', 'Masyarakat')->value('role_id');

        // 1. Akun Admin
        User::updateOrCreate(
            ['email' => 'admin@aksesibel.test'],
            [
                'name' => 'Administrator Aksesibel',
                'password' => bcrypt('password'),
                'role_id' => $adminRoleId,
                'account_status' => 'active',
            ]
        );

        // 2. Akun Petugas Tindak Lanjut
        $officer = User::updateOrCreate(
            ['email' => 'petugas@aksesibel.test'],
            [
                'name' => 'Budi Santoso (Petugas)',
                'password' => bcrypt('password'),
                'role_id' => $officerRoleId,
                'account_status' => 'active',
            ]
        );

        // 3. Akun Pelapor (Masyarakat)
        $citizen = User::updateOrCreate(
            ['email' => 'pelapor@aksesibel.test'],
            [
                'name' => 'Warga Masyarakat',
                'password' => bcrypt('password'),
                'role_id' => $citizenRoleId,
                'account_status' => 'active',
            ]
        );

        // 4. Sample Laporan Siap Ditugaskan (Sesuai Desain Figma Gambar 1-3)
        $facilityCatPark = \App\Models\FacilityCategory::firstOrCreate(['category_name' => 'Taman']);
        $facilityCatHealth = \App\Models\FacilityCategory::firstOrCreate(['category_name' => 'Fasilitas Kesehatan']);
        $facilityCatTransport = \App\Models\FacilityCategory::firstOrCreate(['category_name' => 'Transportasi Umum']);

        $facility1 = \App\Models\Facility::firstOrCreate(
            ['name' => 'Taman Literasi Martha Tiahahu'],
            ['category_id' => $facilityCatPark->category_id]
        );
        $facility2 = \App\Models\Facility::firstOrCreate(
            ['name' => 'Puskesmas Menteng'],
            ['category_id' => $facilityCatHealth->category_id]
        );
        $facility3 = \App\Models\Facility::firstOrCreate(
            ['name' => 'Stasiun KRL Gondangdia'],
            ['category_id' => $facilityCatTransport->category_id]
        );

        $issueGuiding = \App\Models\IssueCategory::firstOrCreate(['category_name' => 'Jalur pemandu terhalang pot tanaman']);
        $issueHandrail = \App\Models\IssueCategory::firstOrCreate(['category_name' => 'Pegangan toilet longgar']);
        $issueLift = \App\Models\IssueCategory::firstOrCreate(['category_name' => 'Lift tidak merespons tombol']);

        $period = \App\Models\ReportingPeriod::firstOrCreate(
            ['period_name' => 'Periode 2026'],
            ['start_date' => now()->startOfYear(), 'end_date' => now()->endOfYear()]
        );

        $statusFollowUp = \App\Models\VerificationStatus::where('status_name', 'Dalam Tindak Lanjut')->first();

        if ($statusFollowUp && $period) {
            \App\Models\Report::updateOrCreate(
                ['report_number' => 'AKS-261014-024'],
                [
                    'user_id' => $citizen->id,
                    'facility_id' => $facility1->facility_id,
                    'issue_category_id' => $issueGuiding->issue_category_id,
                    'status_id' => $statusFollowUp->status_id,
                    'period_id' => $period->period_id,
                    'description' => "Dua pot tanaman menutupi jalur pemandu dekat pintu masuk utara. Pengunjung perlu keluar dari jalur untuk melewatinya. Mohon pot dipindahkan agar jalur dapat digunakan kembali.",
                    'created_at' => now()->subHours(2),
                ]
            );

            \App\Models\Report::updateOrCreate(
                ['report_number' => 'AKS-261014-023'],
                [
                    'user_id' => $citizen->id,
                    'facility_id' => $facility2->facility_id,
                    'issue_category_id' => $issueHandrail->issue_category_id,
                    'status_id' => $statusFollowUp->status_id,
                    'period_id' => $period->period_id,
                    'description' => "Pegangan tangan (handrail) di toilet difabel lantai 1 bautnya kendur dan membahayakan pengguna kursi roda.",
                    'created_at' => now()->subHours(4),
                ]
            );

            \App\Models\Report::updateOrCreate(
                ['report_number' => 'AKS-261013-021'],
                [
                    'user_id' => $citizen->id,
                    'facility_id' => $facility3->facility_id,
                    'issue_category_id' => $issueLift->issue_category_id,
                    'status_id' => $statusFollowUp->status_id,
                    'period_id' => $period->period_id,
                    'description' => "Tombol prioritas difabel pada lift peron 2 tidak menyala dan pintu tidak terbuka otomatis.",
                    'created_at' => now()->subDay(),
                ]
            );
        }
    }
}
