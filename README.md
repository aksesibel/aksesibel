# Aksesibel

**Platform informasi aksesibilitas fasilitas publik: direktori fasilitas, pelaporan masyarakat, verifikasi, dan tindak lanjut.**

---

## Tentang Aplikasi

Aksesibel membantu masyarakat, terutama penyandang disabilitas, lansia, ibu hamil, pengguna kursi roda, dan pendamping, mengetahui kondisi sarana aksesibilitas di fasilitas publik **sebelum** mereka berkunjung. Selain direktori informasi, Aksesibel menyediakan jalur pelaporan: masyarakat dapat melaporkan kendala (misalnya lift rusak atau *ramp* terhalang), laporan diverifikasi, lalu diteruskan ke tindak lanjut sampai selesai, dengan dashboard statistik untuk pemantauan.


## Fitur Utama

| Kelompok | Fitur |
|---|---|
| **Direktori Fasilitas** | Daftar, pencarian, filter, detail fasilitas, dan lokasi pada peta |
| **Data Fasilitas** | Kelola fasilitas, kategori fasilitas, dan lokasi |
| **Data Aksesibilitas** | Kelola jenis aksesibilitas, sarana aksesibilitas per fasilitas, dan catatan pemeriksaan |
| **Laporan Masyarakat** | Buat, lihat, ubah, batalkan laporan; kategori kendala; unggah bukti |
| **Verifikasi** | Pemeriksaan laporan, riwayat verifikasi, dan status verifikasi |
| **Tindak Lanjut** | Penugasan, progres penanganan, dan bukti tindak lanjut |
| **Data Pendukung dan Statistik** | Pengguna dan hak akses, kategori, periode pelaporan, dashboard statistik |


## Peran Pengguna

| Peran | Hak akses |
|---|---|
| **Pengunjung** (tanpa akun) | Melihat dan mencari fasilitas, detail aksesibilitas, dan peta. Tidak perlu login. |
| **Masyarakat** (pengguna terdaftar) | Membuat dan memantau laporan sendiri, mengunggah bukti sendiri. |
| **Admin** | Memeriksa antrean laporan dan menetapkan hasil verifikasi. Tidak mengelola master data, akun, penugasan, atau progres. |
| **Pengelola Fasilitas** | Mengelola data fasilitas, kategori, lokasi, aksesibilitas, pemeriksaan, tindak lanjut, pengguna, periode, dan statistik. |

Nama peran di database: `Admin`, `Masyarakat`, `Pengelola Fasilitas`.

---

## Tim Pengembang

**Kelompok:** B-C | **Project Manager:** Rajendra Aulya Marsa Ilham

| Anggota | Modul | PBI | Tabel yang dimiliki |
|---|---|---|---|
| Elyasa Reva Kinanthy | Direktori Fasilitas | PBI-01, PBI-02, PBI-03 | `facilities`, `facility_categories`, `facility_locations` |
| Annisa Shafira Putri Puspita | Data Aksesibilitas | PBI-04, PBI-05, PBI-06 | `accessibility_types`, `facility_accessibilities`, `accessibility_inspections` |
| Riyana Kartika Pratiwi | Laporan Masyarakat | PBI-07, PBI-08, PBI-09 | `reports`, `issue_categories`, `report_evidences` |
| Siti Bilqis | Verifikasi | PBI-10, PBI-11, PBI-12 | `verifications`, `verification_histories`, `verification_statuses` |
| Muhammad Fachri Fathurrahman | Tindak Lanjut | PBI-13, PBI-14, PBI-15 | `follow_up_assignments`, `follow_up_progress`, `follow_up_evidences` |
| Rajendra Aulya Marsa Ilham | Data Pendukung dan Statistik | PBI-16, PBI-17, PBI-18 | `users`, `roles`, `reporting_periods`, serta dashboard statistik |

Total **18 PBI** (6 anggota x 3 PBI). Setiap anggota bekerja di branch sendiri dan menggabungkan hasilnya ke `main` lewat Pull Request.

## Teknologi

| Komponen | Teknologi |
|---|---|
| Backend | PHP (8.3), [Laravel](https://laravel.com) (13.17) |
| Database | MySQL (InnoDB, `utf8mb4_unicode_ci`) |
| Frontend | Blade, CSS kustom (tanpa Bootstrap), JavaScript |
| Build asset | Vite, Node.js, npm |
| Alat bantu | Git dan GitHub, Jira, Slack (daily report) |

---

## Instalasi dan Konfigurasi

### Prasyarat

- PHP dan [Composer](https://getcomposer.org)
- MySQL
- Node.js dan npm
- Git

### Langkah

```bash
# 1. Clone dan masuk ke folder proyek
git clone https://github.com/aksesibel/aksesibel.git
cd [isi: nama folder]

# 2. Pasang dependensi
composer install
npm install

# 3. Siapkan environment
cp .env.example .env
php artisan key:generate
```

Buat database kosong bernama `aksesibel` (charset `utf8mb4`, collation `utf8mb4_unicode_ci`), lalu sesuaikan `.env` (lihat tabel di bawah).

```bash
# 4. Buat tabel dan isi data awal
php artisan migrate --seed

# 5. Tautkan folder storage (untuk foto yang diunggah)
php artisan storage:link

# 6. Jalankan aplikasi (dua terminal)
npm run dev
php artisan serve
```

Aplikasi dapat dibuka di `http://127.0.0.1:8000`.

### Konfigurasi database yang wajib sama

| Item | Nilai |
|---|---|
| Nama database | `aksesibel` |
| Charset / collation | `utf8mb4` / `utf8mb4_unicode_ci` |
| Engine | InnoDB (bawaan) |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `127.0.0.1` |
| `DB_USERNAME` | `root` |

### Reset database lokal

```bash
php artisan migrate:fresh --seed
```

Perintah ini menghapus semua data di database **lokal**. Cek kesamaan dengan tim lewat `php artisan migrate:status` (semua berstatus `Ran`).

---

## Data Awal (Seeder)

`MasterDataSeeder` mengisi data master. Tiap blok diisi oleh pemilik tabelnya dan aman dijalankan berulang (`updateOrInsert`).

| Tabel | Isi contoh |
|---|---|
| `roles` | Admin, Masyarakat, Pengelola Fasilitas |
| `facility_categories` | Taman, Fasilitas Kesehatan, Pendidikan, ... |
| `accessibility_types` | Ramp / Jalur Kursi Roda, Toilet Difabel, Guiding Block, Parkir Difabel, Lift, Handrail |
| `issue_categories` | Fasilitas rusak, Fasilitas tidak tersedia, Akses terhalang, ... |
| `verification_statuses` | Menunggu Verifikasi, Terverifikasi, Ditolak, ... |

---

## Alur Kerja

**Sebelum mulai kerja**

```bash
git checkout main
git pull
git checkout <nama-branch-anda>
git merge main
composer install
php artisan migrate
```

## Dokumentasi Modul

Dokumentasi implementasi PBI-16–18: [Modul Data Pendukung dan Statistik](MODUL_DATA_PENDUKUNG.md).
