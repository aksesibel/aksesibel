# PBI-16–18 — Data Pendukung dan Statistik

Penanggung jawab: **Rajendra Aulya Marsa Ilham — 102022580015**.

## Fitur yang selesai

- PBI-16: pencarian nama/email, filter peran/status, pagination, tambah/detail/edit pengguna, pengaturan peran, aktivasi/nonaktivasi/penangguhan. Password di-hash Laravel; password edit kosong dipertahankan. Pengguna tidak dihapus agar histori utuh.
- PBI-17: CRUD tunggal kategori fasilitas dan kendala, pencarian/pagination, informasi penggunaan, aktivasi/nonaktivasi, validasi nama yang dipangkas dan unik. Penghapusan kategori terpakai ditolak dengan saran penonaktifan; FK tetap restrictive.
- PBI-18: CRUD periode, validasi tanggal, pencarian/pagination, larangan menghapus periode terpakai. Periode boleh bertumpang tindih; perubahan tanggal tidak mengubah hubungan laporan.
- Statistik: jumlah fasilitas global berlabel, jumlah laporan sesuai filter, agregasi status/kendala/kategori fasilitas, grafik batang HTML/CSS dengan tabel pendamping, rekap kelompok berpagination, reset filter, kondisi kosong, dan pesan dependensi tabel yang spesifik. Tidak ada tabel statistik baru atau perubahan status laporan.
- Autentikasi sesi minimal: registrasi Masyarakat, login terbatas 5 percobaan/menit berdasarkan email+IP, regenerasi sesi, logout POST/CSRF, serta profil terbatas. Akun nonaktif/ditangguhkan dikeluarkan pada request berikutnya, termasuk sesi lama. Peran kosong/tidak dikenal ditolak dari akses internal.

## Route dan hak akses

| Route | Nama route | Akses |
| --- | --- | --- |
| `/`, GET `/login`, GET `/register` | `home`, `login`, `register` | Publik; formulir autentikasi khusus guest |
| POST `/login`, POST `/register` | `login.store`, `register.store` | Guest + throttle |
| GET `/account`, PATCH `/profile` | `account`, `profile.update` | Akun aktif dengan salah satu dari tiga peran proposal |
| POST `/logout` | `logout` | Akun terautentikasi |
| `/pengelola/users` | `manage.users.*` | Pengelola Fasilitas aktif; resource tanpa destroy |
| PATCH `/pengelola/users/{user}/status` | `manage.users.status` | Pengelola Fasilitas aktif |
| `/pengelola/facility-categories` | `manage.facility-categories.*` | Pengelola Fasilitas aktif; resource CRUD |
| `/pengelola/issue-categories` | `manage.issue-categories.*` | Pengelola Fasilitas aktif; resource CRUD |
| `/pengelola/reporting-periods` | `manage.reporting-periods.*` | Pengelola Fasilitas aktif; resource CRUD |
| GET `/pengelola/statistics` | `manage.statistics` | Pengelola Fasilitas aktif |

Resource menyediakan GET daftar, GET `/create`, POST tambah, GET `/{id}`, GET `/{id}/edit`, PUT/PATCH edit, dan DELETE bila disebut CRUD. Parameter binding kategori/periode memakai `facility_category`, `issue_category`, `reporting_period` dan PK khusus model, bukan asumsi `id`.

Gate `manage-supporting-data` memeriksa **nama persis** `Pengelola Fasilitas` dan status `active`. Semua endpoint modul dilindungi middleware server; Admin hanya berwenang pada modul verifikasi sesuai proposal dan tidak mendapat akses pengelolaan ini. Profil/registrasi menolak input `role_id`/`account_status`. Registrasi selalu memakai Masyarakat aktif. Hak peran lama tidak diperluas.

Perubahan pengguna melalui `UserManagement` mengunci baris role secara berurutan dalam transaksi, lalu membaca ulang pengguna aktif dengan locking read dan memeriksa kewenangan pelaku. Dengan demikian dua pengelola tidak dapat saling mencabut hak secara bersamaan hingga pengelola aktif habis. Semua perubahan role/status dari modul lain harus mengikuti service yang sama. Perubahan langsung SQL atau penghapusan peran di luar service berada di luar jaminan ini. Akun sendiri tidak dapat dinonaktifkan/ditangguhkan; demosi sendiri diizinkan hanya bila pengelola aktif lain tersedia.

## Menjalankan secara lokal

Gunakan aplikasi pada **root repository**, bukan salinan untracked `aksesibel/`. PHP 8.3+ diperlukan; implementasi diverifikasi memakai PHP 8.4.2 dan Laravel 13.39.0.

1. Jalankan `composer install` dan `npm ci` (atau `npm install` bila belum ada lockfile).
2. Siapkan `.env` lokal dari `.env.example` bila belum tersedia, lalu `php artisan key:generate` hanya untuk instalasi baru. Pertahankan konfigurasi MySQL/MariaDB milik proyek. Nama variabel koneksi: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`; jangan menyalin rahasia ke dokumentasi. Zona waktu aplikasi `Asia/Jakarta`.
3. Setelah server database yang dipilih tersedia, jalankan **`php artisan migrate`**. Tidak perlu impor SQL. Migration lama tidak diubah. Migration baru hanya menambahkan unique index nama kedua kategori mengikuti collation database. Bila kategori lama duplikat atau masih memiliki spasi tepi, migration berhenti sebelum penambahan index; tinjau nama dan referensi bersama kelompok tanpa menghapus histori otomatis.
4. Jalankan `php artisan db:seed --class=MasterDataSeeder`. Seeder menambah master yang belum ada, mempertahankan isi/status yang telah diedit, dan tidak membuat akun/password tetap. `DatabaseSeeder` juga hanya memanggil master.
5. Buat akun awal dengan `php artisan aksesibel:create-manager`. Perintah meminta nama, email, password tersembunyi minimal 12 karakter, dan konfirmasi. Perintah menolak mode `--no-interaction` dan tidak mengubah akun lama.
6. Jalankan `npm run build`, kemudian `composer run dev` atau `php artisan serve` dengan `npm run dev`. Masuk memakai akun yang dibuat pada langkah 5.

Jangan menggunakan `composer setup`, `migrate:fresh`, `db:wipe`, atau reset database untuk memasang fitur ini pada database kerja.

## Pengujian

```sh
php artisan test --compact
php artisan route:list --except-vendor -v
vendor/bin/pint --dirty --format agent
npm run build
```

`phpunit.xml` memaksa `APP_ENV=testing`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, dan `DB_URL` kosong. `Tests\TestCase` memeriksa konfigurasi **sebelum** trait refresh database berjalan, dan membatalkan tes jika tidak sesuai. Tes tidak menyentuh database kerja. Cache/session testing memakai array. PHPUnit memakai key khusus tes dan mematikan Vite saat tes HTTP; build frontend diperiksa terpisah.

Cakupan tes meliputi gate seluruh peran/status, URL langsung, login/logout/throttle, pencabutan sesi lama, registrasi/profil antieskalasi, hashing/edit password, validasi pengguna dan pengelola terakhir, kedua CRUD kategori dan histori nonaktif, periode terpakai, filter/pagination/statistik tanpa hitung ganda meskipun banyak bukti/riwayat/progres, serta seeder aman dijalankan ulang.

SQLite tidak meniru collation MySQL/MariaDB atau row locking InnoDB. Validasi unique dan unique index mengikuti collation database yang digunakan: pada `utf8mb4_unicode_ci`, variasi kapital/aksen yang dianggap sama oleh database akan ditolak. Tes SQLite mempertahankan perilaku collation native SQLite, tidak memaksakan perbandingan lowercase yang berbeda dari database. Verifikasi konkurensi dua transaksi secara nyata dan collation target perlu server MySQL/MariaDB testing terpisah; jangan mengarahkannya ke database kerja.

## Kontrak integrasi modul lain

- Gunakan model/tabel kategori yang **sama**: `FacilityCategory` / `facility_categories` dan `IssueCategory` / `issue_categories`. Tidak ada kategori atau CRUD tandingan.
- Pilihan kategori baru: `FacilityCategory::active()->orderBy('category_name')->get()` dan `IssueCategory::active()->orderBy('category_name')->get()`. Pemilik modul fasilitas/laporan perlu memakai query ini dan validasi kategori aktif ketika memilih kategori baru. Untuk edit data historis, pertahankan FK kategori nonaktif apabila pilihan tidak diubah. Controller modul lain belum tersedia, sehingga tidak diubah.
- Jangan memasang global scope kategori aktif. Relasi `Facility::category` dan `Report::issueCategory` harus tetap membaca kategori historis nonaktif.
- Filter statistik: `period_id` membatasi `reports.period_id`, `category_id` membatasi `facilities.category_id`, `issue_category_id` membatasi `reports.issue_category_id`. Bukan rentang `created_at`; tidak ada filter tanggal khusus. Kartu fasilitas mencakup semua fasilitas global, termasuk nonaktif. Rekap dikelompokkan kategori fasilitas × kategori kendala × status.
- `App\Queries\ReportStatistics` mengagregasi di database dan hanya join dimensi many-to-one. Jangan menambah join bukti/riwayat/progres tanpa agregasi terlebih dahulu. Grafik dan tabel memakai basis query filter yang sama.
- Pertahankan PK khusus serta `timestamps=false` pada kategori/periode. Tidak ada `is_active` periode dan tidak ada perubahan schema modul lain.
- Untuk endpoint internal modul lain gunakan autentikasi, pemeriksaan akun aktif (sudah dipasang pada group web), dan otorisasi peran/policy sesuai proposal. Admin, Masyarakat, dan Pengelola Fasilitas memiliki lingkup berbeda; gate modul ini tidak memberikan hak operasional/verifikasi/pelaporan secara otomatis.

## Dependensi dan konflik

Proposal, `STANDAR_DATABASE.md`, dan `aksesibel_schema.sql` tidak ditemukan saat implementasi; migration repository dan pembagian peran dalam permintaan menjadi acuan. Seluruh model/tabel referensi tersedia dalam migration. Antarmuka/endpoint pelaporan pribadi, verifikasi, serta operasional fasilitas milik anggota lain belum tersedia; halaman akun menyebut dependensi tersebut secara spesifik.

Seeder sebelumnya berisi Admin, Masyarakat, Verifikator, dan Petugas Tindak Lanjut. Instalasi baru kini menambah hanya tiga peran proposal. Pada database lama seeder **tidak menghapus/mengganti arti peran atau memindahkan pengguna**. Role Verifikator/Petugas Tindak Lanjut dan peran tidak dikenal tidak otomatis memperoleh akses internal. Kelompok perlu mengonfirmasi pemetaan legacy bila masih digunakan; fitur ini tidak membuat pemetaan diam-diam. Pemeriksaan baca-saja database lokal saat implementasi gagal tersambung (kode koneksi 2002), sehingga penggunaan legacy aktual belum terverifikasi dan migration/seed tidak dijalankan pada database kerja.

Laravel Boost dipasang sesuai instruksi bootstrap AGENTS.md. Berkas panduan dan konfigurasi agent yang dihasilkan termasuk perubahan setup tersebut. Tidak ada commit, push, atau deployment.

## Hasil verifikasi implementasi

- `php artisan test --compact`: **175 tes lulus, 780 asersi**, memakai SQLite `:memory:` terisolasi.
- `vendor/bin/pint --dirty --format agent` dan `git diff --check`: lulus.

- Route: seluruh 29 endpoint `/pengelola/*` menggunakan `auth` dan `can:manage-supporting-data` (37 route aplikasi keseluruhan).
- `npm run build`: lulus, termasuk asset CSS/JS dan font yang dikonfigurasi proyek. Unduhan font memerlukan akses jaringan pada build pertama.
- `php artisan view:cache`: lulus; semua template Blade berhasil dikompilasi.
- `composer validate --strict`: lulus.
- `composer audit --locked`: menemukan satu advisory medium praeksisting pada `symfony/mime v8.1.7`, [CVE-2026-45070](https://symfony.com/cve-2026-45070). Versi tersebut sama dengan baseline repository. Pembaruan dependency ini perlu ditangani pemilik dependency proyek; audit belum bersih.
- Pengujian real browser dan konkurensi/collation MySQL/MariaDB belum dijalankan; hasil HTTP diuji melalui feature tests dan build frontend.
