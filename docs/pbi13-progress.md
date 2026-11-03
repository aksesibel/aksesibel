# PBI-13 — Kelola Penugasan Tindak Lanjut

**Penulis:** Muhammad Fachri Fathurrahman
**Tanggal mulai:** 2026-10-31
**Branch:** `fachri` → push ke `origin/fachri`
**Status:** Tahap 3 selesai — antarmuka penugasan

---

## 1. Kebutuhan & Batas Pekerjaan

### Kebutuhan (dari proposal)

- Pengelola Fasilitas menentukan petugas penanggung jawab laporan, catatan penugasan, dan tenggat (opsional).
- Laporan harus sudah **diverifikasi, valid, membutuhkan penanganan, dan diteruskan ke tindak lanjut** sebelum ditugaskan.
- Status "Terverifikasi" saja belum membuktikan laporan diteruskan.
- Penugasan dapat dilihat pihak berwenang, termasuk petugas yang ditunjuk.
- Kewenangan Pengelola Fasilitas tidak otomatis sama dengan Admin pemeriksa laporan.

### Batas (scope boundary)

- **Hanya PBI-13** — penugasan tindak lanjut.
- **Bukan PBI-14** (progres tindak lanjut) — tabel `follow_up_progress` tidak disentuh selain pemeriksaan relasi.
- **Bukan PBI-15** (bukti tindak lanjut) — tabel `follow_up_evidences` tidak disentuh.
- Tidak mengubah struktur tabel `reports`, `users`, atau `verifications`.

---

## 2. Temuan Aktual

### 2.1 Stack & Versi

| Item             | Nilai                        |
| ---------------- | ---------------------------- |
| Laravel          | ^13.17 (composer.json)       |
| PHP              | 8.2.4 (XAMPP)                |
| Database         | MySQL via XAMPP (lokal)      |
| Vendor dir       | **Belum ada** (perlu `composer install` sebelum tahap coding) |

### 2.2 Tabel `follow_up_assignments` (migration 100016)

```
assignment_id   — PK, auto-increment
report_id       — FK → reports.report_id (cascadeOnDelete)
assigned_to     — FK → users.id
assigned_by     — FK → users.id
assignment_status — string, default 'assigned'
assigned_at     — dateTime, useCurrent
due_date        — date, nullable
```

**Tidak ada** kolom `assignment_notes` / catatan penugasan.
Proposal **membutuhkan** catatan penugasan → perlu migration tambahan untuk menambah kolom ini.

**Tidak ada** `created_at`/`updated_at` (sesuai referensi; `$timestamps = false` di model).

### 2.3 Model `FollowUpAssignment`

- Path: `app/Models/FollowUpAssignment.php`
- PK: `assignment_id`, `$timestamps = false`, `$guarded = []`.
- Relasi sudah lengkap: `report()`, `assignee()`, `assigner()`, `progress()`.
- **Tidak ada** scope, method bisnis, atau constraint validasi apa pun di model.

### 2.4 Model `Report`

- Path: `app/Models/Report.php`
- Kolom status: `status_id` → FK ke `verification_statuses.status_id`.
- Relasi: `followUpAssignments()`, `verifications()`, `evidences()`, dll.
- **Tidak ada** method `changeStatus()` atau helper status apa pun.
- Status diubah langsung via update `status_id`.

### 2.5 Status Laporan (verification_statuses via seeder)

| status_name              | Keterangan semantik           |
| ------------------------ | ----------------------------- |
| Menunggu Verifikasi      | Baru masuk                    |
| Terverifikasi            | Sudah diverifikasi            |
| Ditolak                  | Ditolak verifikator           |
| Dalam Tindak Lanjut      | Sudah diteruskan              |
| Selesai                  | Tuntas                        |

**Implikasi PBI-13:** Penugasan hanya boleh dibuat jika status laporan = "Dalam Tindak Lanjut", bukan sekadar "Terverifikasi".

### 2.6 Role (dari seeder)

| role_name                |
| ------------------------ |
| Admin                    |
| Masyarakat               |
| Verifikator              |
| Petugas Tindak Lanjut    |

**Tidak ada** role "Pengelola Fasilitas" secara eksplisit. Akan diputuskan apakah:
- (a) Admin yang berperan sebagai Pengelola Fasilitas (assign), atau
- (b) Role baru ditambahkan.

### 2.7 Verifikasi & Penerusan Laporan

- `verifications` menyimpan siapa yang memverifikasi, hasilnya, dan catatan.
- `verification_histories` mencatat transisi status (`previous_status_id` → `new_status_id`).
- **Belum ada logika penerusan** (forwarding) yang terpisah dari perubahan status.
  Transisi ke "Dalam Tindak Lanjut" dicatat di `verification_histories` sebagai bukti penerusan.

### 2.8 Infrastruktur Pendukung

| Komponen            | Status                                 |
| ------------------- | -------------------------------------- |
| Controllers         | Hanya `Controller.php` (base class)    |
| Policies            | Tidak ada                              |
| FormRequest         | Tidak ada                              |
| Middleware kustom   | Tidak ada                              |
| Routes              | Hanya route `/` → welcome              |
| Views               | Hanya `welcome.blade.php`              |
| Tests               | Hanya ExampleTest (Feature + Unit)     |

### 2.9 Aturan Jumlah Penugasan Aktif

**Belum ada** aturan (constraint/scope) yang membatasi jumlah penugasan aktif per laporan.
Migration menggunakan `report_id` tanpa unique constraint — artinya satu laporan bisa punya **banyak** penugasan.
Perlu diputuskan:
- (a) Satu laporan → satu penugasan aktif (validasi di level aplikasi), atau
- (b) Satu laporan → banyak penugasan (masing-masing petugas berbeda).

### 2.10 Mekanisme Perubahan Status Bersama

**Tidak ada** event, observer, atau service class yang mengubah status secara otomatis saat penugasan dibuat.
Perlu dibangun: saat penugasan dibuat, status laporan sebaiknya sudah "Dalam Tindak Lanjut" (prasyarat), bukan diubah otomatis oleh PBI-13.

---

## 3. File Relevan

| File                                                              | Fungsi                          |
| ----------------------------------------------------------------- | ------------------------------- |
| `database/migrations/2026_10_28_100016_create_follow_up_assignments_table.php` | Skema tabel penugasan |
| `app/Models/FollowUpAssignment.php`                               | Model penugasan                 |
| `app/Models/Report.php`                                           | Model laporan + relasi          |
| `app/Models/User.php`                                             | Model user + relasi assignment  |
| `app/Models/Verification.php`                                     | Model verifikasi                |
| `app/Models/VerificationHistory.php`                              | Riwayat transisi status         |
| `app/Models/VerificationStatus.php`                               | Lookup status                   |
| `app/Models/Role.php`                                             | Lookup role                     |
| `database/seeders/MasterDataSeeder.php`                           | Data awal role & status         |
| `routes/web.php`                                                  | Route (masih kosong)            |
| `app/Http/Controllers/Controller.php`                             | Base controller                 |

---

## 4. Keputusan yang Tersedia vs Belum Tersedia

### ✅ Tersedia (dapat langsung dipakai)

1. Tabel `follow_up_assignments` sudah ada dan sudah di-migrate.
2. Model `FollowUpAssignment` lengkap dengan relasi.
3. Status "Dalam Tindak Lanjut" tersedia sebagai prasyarat penugasan.
4. Relasi `Report::followUpAssignments()` dan `User::assignmentsReceived()/assignmentsGiven()` sudah ada.

### ❓ Belum tersedia / perlu keputusan

| # | Hal                                   | Opsi                                                        |
|---|---------------------------------------|-------------------------------------------------------------|
| 1 | Kolom catatan penugasan               | Tambah kolom `assignment_notes` (text, nullable) via migration baru. |
| 2 | Role "Pengelola Fasilitas"            | (a) Gunakan Admin, atau (b) tambah role baru di seeder.     |
| 3 | Jumlah penugasan aktif per laporan    | (a) Satu saja, atau (b) bisa banyak.                       |
| 4 | Siapa yang boleh melihat penugasan    | Pengelola + petugas yang ditunjuk. Detail otorisasi belum dibangun. |
| 5 | Transisi status otomatis vs manual    | Apakah PBI-13 mengubah status laporan saat menugaskan, atau status sudah "Dalam Tindak Lanjut" sebelumnya. |

---

## 5. Rencana Tahap 2–4

### Tahap 2 — Migration & Validasi Bisnis

- Buat migration tambahan: `add_assignment_notes_to_follow_up_assignments_table`.
- Buat `FollowUpAssignmentController` dengan method `index`, `create`, `store`, `show`.
- Buat `StoreFollowUpAssignmentRequest` (FormRequest) dengan validasi:
  - `report_id` wajib, ada di `reports`, status = "Dalam Tindak Lanjut".
  - `assigned_to` wajib, ada di `users`, role = Petugas Tindak Lanjut.
  - `assignment_notes` opsional, text.
  - `due_date` opsional, date, >= hari ini.
- Tambah route resource (atau route manual) di `web.php`.
- Otorisasi: middleware role untuk Pengelola Fasilitas / Admin.

### Tahap 3 — View & Tampilan

- Buat layout utama jika belum ada (atau gunakan yang tersedia dari PBI lain).
- Buat view: daftar penugasan, form buat penugasan, detail penugasan.
- Tampilkan info laporan, petugas, status, catatan, tenggat.
- Responsif dan sesuai desain proyek.

### Tahap 4 — Pengujian & Finalisasi

- Unit test: validasi FormRequest, model scope/method.
- Feature test: CRUD penugasan, otorisasi role, prasyarat status laporan.
- Pastikan tidak merusak fitur yang ada.
- Commit dan push ke `origin/fachri`.

---

## 6. Aturan Git, Database, & Pembatasan

### Git

- Branch kerja: `fachri`.
- Push hanya ke `origin/fachri`.
- **Dilarang:** push/merge ke main, buat PR, force push, deploy.
- Pertahankan perubahan yang sudah ada di branch.

### Database

- Gunakan koneksi lokal XAMPP/MySQL yang sudah dikonfigurasi.
- **Dilarang:** import SQL, menimpa `.env`, reset database, `migrate:fresh`.
- Migration baru hanya untuk kolom tambahan (bukan ubah tabel existing selain `follow_up_assignments`).
- Jangan tampilkan kredensial database.

### Pembatasan Perubahan

- Tidak mengubah struktur tabel `reports`, `users`, atau `verifications`.
- Tidak membuat model duplikat — gunakan yang sudah ada.
- Migration repository adalah sumber kebenaran.

---

## 7. Status Pemeriksaan

- [x] Periksa AGENTS.md, README, Git remote/branch
- [x] Periksa versi Laravel & PHP
- [x] Periksa migration `follow_up_assignments`
- [x] Periksa model `FollowUpAssignment`, `Report`, `User`
- [x] Periksa role dan status dari seeder
- [x] Periksa verifikasi, penerusan, dan riwayat status
- [x] Periksa controller, route, policy, middleware, view, test
- [x] Identifikasi kolom catatan penugasan (belum ada)
- [x] Identifikasi aturan jumlah penugasan (belum ada constraint)
- [x] Identifikasi mekanisme perubahan status (belum ada otomasi)

---

## 8. Langkah Berikutnya

1. **Upgrade PHP ke >= 8.3** agar artisan, migration, dan PHPUnit dapat berjalan.
2. Jalankan `php artisan migrate` untuk menerapkan migration `assignment_notes`.
3. Jalankan `vendor/bin/phpunit --filter=FollowUpAssignment` untuk validasi tes.
4. Mulai tahap 3: view/antarmuka penugasan.

---

## 10. Tahap 3 — Catatan Implementasi Antarmuka (Views)

### 10.1 File yang Dibuat / Diubah

| File | Keterangan |
|------|-----------|
| `resources/views/layouts/app.blade.php` | Layout utama modern, navigasi menu PBI-13, indikator role, alert flash, aksesibel |
| `resources/views/follow-up-assignments/ready-reports.blade.php` | **Tampilan 1:** Daftar laporan berstatus "Dalam Tindak Lanjut" siap ditugaskan |
| `resources/views/follow-up-assignments/create.blade.php` | **Tampilan 2:** Form penugasan (ringkasan laporan, dropdown petugas aktif, tenggat, catatan) |
| `resources/views/follow-up-assignments/index.blade.php` | **Tampilan 3:** Daftar penugasan (nomor laporan, fasilitas, petugas, pemberi, tenggat, status, aksi) |
| `resources/views/follow-up-assignments/show.blade.php` | **Tampilan 4:** Detail penugasan (ringkasan laporan, verifikasi terkait, catatan penugasan, navigasi) |
| `app/Http/Controllers/FollowUpAssignmentController.php` | Tambah method `readyReports`, query param `report_id` pada `create`, eager loading verifikasi |
| `routes/web.php` | Registrasi rute `follow-up-assignments.ready-reports` dengan middleware `role:Admin` |
| `tests/Feature/FollowUpAssignmentTest.php` | Pengujian aksesibilitas rute view untuk Admin dan Petugas |

### 10.2 Penerapan Ketentuan Desain & Bisnis

1. **Empat Tampilan Terhubung:**
   - **Daftar Laporan Siap Ditugaskan:** Hanya menampilkan laporan berstatus "Dalam Tindak Lanjut" lengkap dengan nomor laporan, fasilitas, kategori kendala, dan tombol "Tugaskan Petugas".
   - **Form Penugasan:** Menampilkan ringkasan laporan terpilih, daftar petugas aktif (role 'Petugas Tindak Lanjut'), batas minimal tenggat waktu (hari ini), input catatan penugasan, tombol Simpan dan Batal.
   - **Daftar Penugasan:** Memisahkan kewenangan (Admin melihat seluruh penugasan; Petugas Tindak Lanjut hanya melihat tugas miliknya).
   - **Detail Penugasan:** Menampilkan detail tugas, catatan penugasan, riwayat verifikasi relevan, serta ringkasan fasilitas tanpa mengekspos data pribadi pelapor.
2. **Aksesibilitas & Standar UI:**
   - Navigasi keyboard penuh (`skip to main content`, `aria-label`, `aria-describedby`, `tabindex`).
   - Indikator status jelas dengan kontras warna memadai.
   - Penanganan kondisi kosong (*empty state*) berdesain ramah pengguna di setiap tabel.
   - Pesan validasi error pada setiap input serta flash notification berhasil/gagal.
3. **Privasi Pelapor:**
   - Identitas pribadi pelapor (nama, telepon, email pelapor) dirahasiakan dan tidak ditampilkan pada antarmuka penugasan.

### 10.3 Cara Mencoba Alur Antarmuka

1. **Akses Daftar Penugasan:**
   - URL: `/follow-up-assignments`
   - Petugas Tindak Lanjut melihat daftar penugasan pribadinya.
   - Admin melihat seluruh penugasan dan memiliki tombol akses ke pembuatan penugasan baru.
2. **Akses Laporan Siap Ditugaskan (Admin):**
   - URL: `/follow-up-assignments/ready-reports`
   - Klik tombol **"Tugaskan Petugas"** pada baris laporan yang diinginkan.
3. **Pengisian Form Penugasan:**
   - URL: `/follow-up-assignments/create?report_id={id}`
   - Form otomatis memuat kartu ringkasan laporan.
   - Pilih petugas aktif, tentukan tenggat waktu (opsional), dan isi catatan instruksi penugasan.
   - Klik **"Simpan Penugasan"**.
4. **Halaman Detail Penugasan:**
   - URL: `/follow-up-assignments/{id}`
   - Menampilkan detail penugasan yang tersimpan, catatan instruksi, serta riwayat verifikasi laporan terkait.


### 9.1 File yang Dibuat/Diubah

| File | Keterangan |
|------|-----------|
| `database/migrations/2026_10_31_100001_add_assignment_notes_to_follow_up_assignments_table.php` | Migration tambah kolom `assignment_notes` (text, nullable) |
| `app/Http/Controllers/FollowUpAssignmentController.php` | Controller: index, create, store, show |
| `app/Http/Requests/StoreFollowUpAssignmentRequest.php` | FormRequest: validasi + otorisasi (hanya Admin) |
| `app/Http/Middleware/EnsureUserHasRole.php` | Middleware role-based access control |
| `app/Models/FollowUpAssignment.php` | Ditambahkan: `getRouteKeyName()`, `scopeWithStatus()` |
| `bootstrap/app.php` | Registrasi alias middleware `role` |
| `routes/web.php` | Route resource PBI-13 (index, create, store, show) |
| `tests/Feature/FollowUpAssignmentTest.php` | 10 test cases (auth, otorisasi, validasi, detail) |

### 9.2 Keputusan yang Diambil (Tahap 2)

| # | Keputusan | Alasan |
|---|-----------|--------|
| 1 | Admin bertindak sebagai Pengelola Fasilitas | Satu-satunya role otoritas yang tersedia di seeder |
| 2 | Banyak penugasan per laporan diizinkan | Skema tidak membatasi (FK biasa, bukan unique) — sesuai kebutuhan petugas berbeda |
| 3 | Status harus sudah "Dalam Tindak Lanjut" sebelum assign | PBI-13 tidak mengubah status laporan — prasyarat terpenuhi lebih dulu |
| 4 | `assigned_by` diambil dari user login | Tidak bisa dimanipulasi dari input klien |
| 5 | `assignment_status` default 'assigned' di server | Tidak terpapar ke input pengguna |

### 9.3 Validasi & Otorisasi

- **Autentikasi:** middleware `auth` — redirect ke login jika belum login.
- **Otorisasi akses:** middleware `role:Admin,Petugas Tindak Lanjut`.
- **Otorisasi create:** hanya Admin (di `StoreFollowUpAssignmentRequest::authorize()`).
- **Otorisasi show:** Petugas hanya bisa lihat penugasan miliknya sendiri.
- **Validasi report_id:** wajib, exists, status = "Dalam Tindak Lanjut".
- **Validasi assigned_to:** wajib, exists, role = "Petugas Tindak Lanjut", akun aktif.
- **Validasi due_date:** opsional, >= hari ini.
- **Validasi assignment_notes:** opsional, maks 2000 karakter.
- **Transaksi:** store dibungkus `DB::transaction()`.

### 9.4 Hasil Pemeriksaan

| Pemeriksaan | Hasil |
|-------------|-------|
| PHP lint semua file baru/diubah | ✅ Lulus (0 error) |
| PHPUnit test | ⚠️ Tidak dapat dijalankan — PHP 8.2.4, PHPUnit butuh >= 8.3 |
| Artisan (migrate, route:list) | ⚠️ Tidak dapat dijalankan — Symfony 8.1 butuh PHP >= 8.4 (property hooks) |
| Test DB config | ✅ SQLite :memory: — aman, tidak sentuh MySQL |

### 9.5 Kendala

1. **PHP 8.2.4 tidak kompatibel** dengan Laravel 13 / Symfony 8.1 / PHPUnit 12.
   - `artisan` dan `phpunit` tidak bisa dijalankan.
   - Migration dan tes belum terverifikasi secara runtime.
   - **Solusi:** upgrade PHP ke >= 8.4 (XAMPP terbaru atau `php.new`).
2. **Role "Pengelola Fasilitas"** belum ada di seeder — Admin digunakan sebagai pengganti.
   Jika tim memutuskan role terpisah, perlu update seeder dan middleware.

### 9.6 Kebutuhan untuk Tahap 3 (View)

- Layout utama (master layout Blade).
- Form buat penugasan: pilih laporan (dropdown terfilter), pilih petugas, catatan, tenggat.
- Tabel daftar penugasan dengan pagination.
- Halaman detail penugasan.
- Flash message untuk success/error.
- Aset CSS (Tailwind/Bootstrap — perlu keputusan tim).

