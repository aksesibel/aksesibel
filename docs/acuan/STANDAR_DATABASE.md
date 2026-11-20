# Standar Database & Pembagian Modul — Aksesibel

Dokumen ini jadi acuan bersama supaya database di laptop semua anggota **identik** dan pekerjaan antar modul **tidak tumpang tindih**.

> Bagian bertanda **(usulan)** perlu disepakati kelompok sebelum dipakai. Setelah sepakat, hapus tanda itu dan jadikan aturan tetap.

---

## 1. Prinsip Utama

1. **Satu sumber kebenaran = file migration di repo.** Struktur database tidak boleh diubah lewat phpMyAdmin.
2. **Database lokal tiap orang, isinya dibentuk oleh migration + seeder.** Kalau semua orang menjalankan migration dan seeder yang sama, hasilnya sama.
3. **Setiap tabel punya satu pemilik (modul).** Hanya pemilik yang boleh mengubah struktur tabelnya. Modul lain memakai tabel itu lewat relasi model.
4. **Perubahan database selalu lewat Pull Request**, dan wajib menyertakan migration + model + (kalau perlu) seeder dalam PR yang sama.

---

## 2. Pengaturan Database yang Wajib Sama

| Item | Nilai |
|---|---|
| Nama database | `aksesibel` |
| Charset / collation | `utf8mb4` / `utf8mb4_unicode_ci` |
| Engine | InnoDB (default) |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `127.0.0.1` |
| `DB_USERNAME` | `root` |
| Zona waktu aplikasi | `Asia/Jakarta` (`config/app.php`) |

Yang boleh beda antar laptop hanya `DB_PORT` dan `DB_PASSWORD` di `.env` pribadi (XAMPP: 3306 dan kosong, MAMP: 8889 dan `root`). **`.env` tidak pernah di-commit.** Kalau ada variabel baru yang dibutuhkan semua orang, tambahkan ke `.env.example` tanpa nilai rahasia.

---

## 3. Kepemilikan Tabel per Modul

Pembagian ini mengikuti **pembagian PBI di proposal** (6 modul, masing-masing 3 PBI). Pemilik modul = pemilik tabel.

| PBI | Modul | Penanggung jawab | Tabel yang dimiliki |
|---|---|---|---|
| 01-03 | Direktori Fasilitas | Elyasa Reva Kinanthy | `facilities`, `facility_categories`, `facility_locations` |
| 04-06 | Data Aksesibilitas | Annisa Shafira Putri Puspita | `accessibility_types`, `facility_accessibilities`, `accessibility_inspections` |
| 07-09 | Laporan Masyarakat | Riyana Kartika Pratiwi | `reports`, `issue_categories`, `report_evidences` |
| 10-12 | Verifikasi | Siti Bilqis | `verifications`, `verification_histories`, `verification_statuses` |
| 13-15 | Tindak Lanjut | Muhammad Fachri Fathurrahman | `follow_up_assignments`, `follow_up_progress`, `follow_up_evidences` |
| 16-18 | Data Pendukung dan Statistik | Rajendra Aulya Marsa Ilham | `users`, `roles`, `reporting_periods`, serta dashboard statistik (query lintas tabel, tanpa tabel sendiri) |

Tabel bawaan Laravel (`cache`, `jobs`, `sessions`, `password_reset_tokens`, dst.) tidak diubah siapa pun.

### Tumpang tindih PBI yang harus diputuskan bersama (usulan)

| Hal | PBI yang beririsan | Usulan pembagian |
|---|---|---|
| Kategori fasilitas | PBI-02 (Elyasa) dan PBI-17 (Rajendra) | Tabel, model, dan halaman CRUD dibuat **satu kali oleh Elyasa**. Rajendra menautkan halaman itu ke menu pengaturan dan memakai datanya untuk filter dan statistik. |
| Kategori kendala | PBI-08 (Riyana) dan PBI-17 (Rajendra) | Sama: tabel, model, dan CRUD dibuat **satu kali oleh Riyana**. Rajendra hanya menautkan dan memakai datanya. |
| Status laporan (`reports.status_id`) | PBI-07 (Riyana), PBI-10/12 (Siti), PBI-14 (Fachri) | Kolom dimiliki Riyana. Status diubah **hanya lewat satu method di model `Report`** (misalnya `changeStatus()`), dipanggil oleh modul Verifikasi dan Tindak Lanjut. Jangan ada yang meng-update kolom ini langsung dari controller sendiri. |
| Data master di `MasterDataSeeder` | semua pemilik tabel master | Tiap blok data diisi oleh **pemilik tabelnya** lewat PR (roles: Rajendra, kategori fasilitas: Elyasa, kategori kendala: Riyana, jenis aksesibilitas: Annisa, status verifikasi: Siti). |

### Aturan lintas modul

- **Baca / relasi ke tabel modul lain: boleh** (lewat relasi di model, contoh `$report->facility`).
- **Mengubah struktur tabel modul lain: tidak boleh.** Kalau butuh kolom baru di tabel orang lain, buat **Issue** di GitHub atau kabari di grup, lalu pemilik tabel yang membuat migration-nya.
- **Menulis data ke tabel modul lain** (insert/update dari controller kamu) harus disepakati dulu dengan pemilik tabel, terutama untuk status dan relasi.
- Data master (kategori, status, jenis aksesibilitas, role) hanya diisi lewat seeder milik pemilik tabelnya.

---

## 4. Aturan Penamaan

| Hal | Aturan | Contoh |
|---|---|---|
| Nama tabel | `snake_case`, jamak, bahasa Inggris | `follow_up_assignments` |
| Primary key | sesuai ERD, `<nama_singular>_id` | `facility_id`, `report_id` |
| Foreign key | **sama dengan nama PK tabel tujuan** | `facility_id` menunjuk `facilities.facility_id` |
| FK ke user | nama peran + `_id` | `inspector_id`, `verifier_id`, `assigned_to`, `assigned_by`, `actor_id`, `user_id` |
| Kolom boolean | awalan `is_` | `is_active` |
| Kolom waktu | akhiran `_at` | `verified_at`, `assigned_at` |
| Kolom tanggal | akhiran `_date` | `due_date`, `inspection_date` |
| Kolom status | akhiran `_status` | `assignment_status` |
| Nama model | `PascalCase`, singular | `FollowUpAssignment` |
| Nama relasi | `camelCase`; belongsTo singular, hasMany jamak | `facility()`, `reports()` |

Catatan khusus `users`: memakai `id` (PK) dan `password`, **bukan** `user_id` dan `password_hash` seperti di gambar ERD, karena sistem login Laravel mengharapkan nama itu. Semua FK ke user tetap bernama sesuai ERD.

---

## 5. Nilai Status yang Disepakati (usulan)

Kolom status bertipe `string`, jadi isinya bisa berbeda-beda kalau tidak distandarkan. Pakai **kode huruf kecil bahasa Inggris** berikut. Teks yang tampil di layar boleh bahasa Indonesia.

| Kolom | Nilai yang diizinkan |
|---|---|
| `users.account_status` | `active`, `inactive`, `suspended` |
| `facilities.facility_status` | `active`, `inactive` |
| `facility_accessibilities.availability_status` | `available`, `unavailable` |
| `facility_accessibilities.condition_status` dan `accessibility_inspections.condition_result` | `good`, `minor_damage`, `major_damage` |
| `accessibility_inspections.information_source` | `field_inspection`, `public_report`, `official_data` |
| `verifications.verification_result` | `approved`, `rejected`, `need_more_info` |
| `follow_up_assignments.assignment_status` | `assigned`, `in_progress`, `completed`, `cancelled` |
| `follow_up_progress.progress_status` | `started`, `in_progress`, `completed`, `blocked` |
| `report_evidences.file_type` dan `follow_up_evidences.file_type` | `image`, `document`, `video` |

Status laporan (`reports.status_id`) mengikuti tabel `verification_statuses` (data master dari seeder). Sesuai proposal, minimal ada status: Menunggu Verifikasi, Terverifikasi, Ditolak, Memerlukan Pemeriksaan Lanjutan, dan Dibatalkan (pembatalan laporan oleh pelapor), ditambah status tindak lanjut (Dalam Tindak Lanjut, Selesai). **Jangan menulis nama status langsung di kode**; cari lewat nama atau ID dari tabel itu.

Cara memakai di kode: simpan sebagai konstanta di model pemiliknya.

```php
// app/Models/FollowUpAssignment.php
public const STATUS_ASSIGNED    = 'assigned';
public const STATUS_IN_PROGRESS = 'in_progress';
public const STATUS_COMPLETED   = 'completed';
public const STATUS_CANCELLED   = 'cancelled';

// pemakaian
FollowUpAssignment::create(['assignment_status' => FollowUpAssignment::STATUS_ASSIGNED, ...]);
```

Kalau butuh nilai status baru, tambahkan ke tabel di atas lewat PR dulu, baru dipakai di kode.

**Format nomor laporan (usulan):** `LPR-YYYYMMDD-NNNN`, contoh `LPR-20261029-0001`. Dibuat oleh modul Laporan Masyarakat (Riyana).

---

## 6. Aturan Migration

1. Buat dengan artisan, jangan buat nama file manual:
   ```bash
   php artisan make:migration add_priority_to_follow_up_assignments_table
   ```
2. **Jangan pernah mengedit migration yang sudah di-merge ke `main`.** Perubahan apa pun dibuat sebagai migration baru.
3. Satu migration = satu perubahan yang jelas (tambah tabel, atau tambah kolom).
4. Method `down()` wajib diisi supaya bisa di-rollback.
5. Foreign key pakai `constrained('nama_tabel', 'nama_pk')` sesuai pola migration yang sudah ada.
6. Jangan menghapus atau mengganti nama kolom tanpa kabar ke grup. Itu bisa merusak fitur modul lain.
7. Migration hanya boleh menyentuh tabel milik modulmu. Kalau perlu tabel baru, pemiliknya adalah modulmu, dan catat di tabel bagian 3.
8. Kolom baru di tabel yang sudah punya data harus `nullable()` atau punya `default()`.

---

## 7. Aturan Model

1. Satu tabel = satu model, sudah tersedia di `app/Models/`. **Jangan buat model duplikat** untuk tabel yang sama.
2. Boleh **menambah** method (relasi baru, scope, accessor, konstanta status). **Jangan mengubah** `$table`, `$primaryKey`, `$timestamps`, atau relasi yang sudah ada tanpa diskusi.
3. Perubahan di model milik modul lain dilakukan lewat PR dan diberi tahu ke pemiliknya.
4. Model memakai `$guarded = []`. Karena itu **validasi input wajib dilakukan di controller** (`$request->validate(...)`) sebelum `create()` atau `update()`.

---

## 8. Aturan Seeder

1. `MasterDataSeeder` (data master) berisi beberapa blok. **Tiap blok diisi pemilik tabelnya** (lihat bagian 3). Anggota lain mengusulkan tambahan lewat PR atau Issue.
2. Tiap modul membuat seeder sendiri untuk data contoh, dengan nama `NamaModulSeeder`, contoh `FacilitySeeder`, `ReportSeeder`.
3. Semua seeder harus **idempotent** (aman dijalankan berulang), pakai `updateOrInsert` atau `updateOrCreate`, bukan `create` mentah.
4. Seeder dipanggil dari `DatabaseSeeder` dengan urutan mengikuti dependensi:
   ```php
   $this->call([
       MasterDataSeeder::class,   // roles, status, kategori, jenis aksesibilitas
       FacilitySeeder::class,     // facilities, lokasi, aksesibilitas
       ReportSeeder::class,       // reports, bukti laporan
       VerificationSeeder::class, // verifikasi + riwayat
       FollowUpSeeder::class,     // penugasan, progress, bukti
   ]);
   ```
5. Data contoh memakai nama dan alamat fiktif atau umum, **tanpa data pribadi asli**.

---

## 9. Alur Sinkronisasi Harian

**Sebelum mulai kerja**
```bash
git checkout main
git pull
git checkout Nama_Branch
git merge main
composer install
php artisan migrate
```

**Saat mengubah database**
1. Buat migration baru (hanya untuk tabel modulmu).
2. Sesuaikan model dan seeder yang terkait.
3. Tes di lokal:
   ```bash
   php artisan migrate:fresh --seed
   ```
4. Commit migration + model + seeder dalam **satu PR**, tulis di deskripsi PR: tabel apa yang berubah dan kenapa.
5. Setelah PR di-merge, kabari grup: "ada migration baru, jalankan `git pull` lalu `php artisan migrate`".

**Reset ke kondisi standar** (menghapus semua data di database *lokal*):
```bash
php artisan migrate:fresh --seed
```

**Cek database kamu sama dengan teman**
```bash
php artisan migrate:status
```
Semua baris harus berstatus `Ran`, dan daftar migration harus sama dengan di `main`.

---

## 10. Larangan

- Mengubah struktur tabel langsung di phpMyAdmin.
- Mengedit migration lama yang sudah di-merge.
- Meng-commit `.env`, folder `vendor/`, atau file export `.sql` berisi data asli.
- Mengubah tabel atau model milik modul lain tanpa kesepakatan.
- Push langsung ke `main` untuk perubahan database. Selalu lewat branch dan Pull Request.
- Menjalankan `migrate:fresh` di database bersama (kalau suatu saat dipakai database cloud bersama).

---

## 11. Checklist Pull Request (untuk reviewer)

- [ ] Migration baru hanya menyentuh tabel milik modul pengirim PR
- [ ] Tidak ada migration lama yang diedit
- [ ] `down()` terisi
- [ ] Nama tabel, kolom, dan FK sesuai bagian 4
- [ ] Nilai status memakai daftar di bagian 5
- [ ] Model dan seeder ikut diperbarui
- [ ] `php artisan migrate:fresh --seed` berhasil di lokal
- [ ] Tidak ada `.env`, `vendor/`, atau file `.sql` di dalam PR
