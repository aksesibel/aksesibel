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


## Detail Modul Lokasi (PBI-03)

Modul lokasi menangani alamat, wilayah administratif, dan koordinat untuk tampilan daftar dan peta.

- **Model `Region` hierarkis** (self-relation): `PROVINSI > KOTA_KABUPATEN > KECAMATAN > KELURAHAN` dengan kode Kemendagri.
- **Perluasan `Facility`**: wilayah berjenjang (`province_id`, `city_region_id`, `district_id`, `region_id`), koordinat (`latitude`, `longitude`), kode pos, catatan lokasi, catatan pintu masuk aksesibel, transportasi terdekat, dan sumber koordinat. Kolom `city` lama tetap dipertahankan sebagai cadangan (kompatibel dengan data terdahulu).
- **Form admin** dengan dropdown wilayah berjenjang, pemilih titik peta Leaflet (klik/seret penanda), input angka lat/lng, tombol geser arah (alternatif tanpa mouse), geokoding alamat, "Gunakan lokasi saya", serta pratinjau peta.
- **Halaman publik `/fasilitas`** dengan pengalih **Daftar | Peta**, marker clustering, "Cari di area ini", "Dekat saya", filter wilayah, dan tautan yang dapat dibagikan.
- **Halaman detail `/fasilitas/{id}`** dengan rantai wilayah lengkap, peta mini, "Salin alamat", dan "Petunjuk arah".

### Seed Wilayah dan Impor CSV

`RegionSeeder` bersifat **idempoten** (upsert berdasarkan `code`). Saat ini memuat provinsi **DKI Jakarta**, 5 kota/kabupaten administrasi, seluruh kecamatan, dan kelurahan untuk Kecamatan **Menteng, Gambir, Tanah Abang, dan Setiabudi**.

Untuk wilayah lain, sediakan CSV dengan format `code,name,level,parent_code`:

```csv
code,name,level,parent_code
31,DKI Jakarta,PROVINSI,
31.71,Kota Jakarta Pusat,KOTA_KABUPATEN,31
31.71.01,Menteng,KECAMATAN,31.71
31.71.01.1001,Menteng,KELURAHAN,31.71.01
```

Level valid: `PROVINSI | KOTA_KABUPATEN | KECAMATAN | KELURAHAN`.

```bash
php scripts/import-regions.php path/to/wilayah.csv
php scripts/import-regions.php path/to/wilayah.csv --dry-run   # uji tanpa menyimpan
```

Skrip memakai upsert berdasarkan `code`. Data wilayah lengkap Indonesia dapat diambil dari <https://github.com/cahyadsn/wilayah> (lisensi MIT).

### Variabel Lingkungan Peta & Geokoding

| Variabel | Default | Keterangan |
| --- | --- | --- |
| `OSM_TILE_URL` | `https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png` | URL tile peta. Ganti bila memakai penyedia lain. |
| `OSM_TILE_ATTRIBUTION` | `© OpenStreetMap contributors` | Atribusi tile; wajib menampilkan atribusi penyedia. |
| `OSM_TILE_MAX_ZOOM` | `19` | Zoom maksimum tile. |
| `OSM_DIRECTIONS_URL` | `https://www.openstreetmap.org/directions` | Basis URL "Petunjuk arah". |
| `GEOCODING_USER_AGENT` | `Aksesibel/1.0 (+https://aksesibel.id; contact@aksesibel.id)` | **Wajib jelas** sesuai kebijakan Nominatim. Ganti dengan kontak nyata. |
| `GEOCODING_ENDPOINT` | `https://nominatim.openstreetmap.org/search` | Endpoint Nominatim. |
| `GEOCODING_CACHE_HOURS` | `24` | Lama cache hasil geokoding (jam). |
| `FACILITIES_PER_PAGE` | `9` | Jumlah fasilitas per halaman. |
| `MAP_MAX_FEATURES` | `500` | Maksimum fitur GeoJSON per permintaan peta. |

### API Lokasi & Peta

| Metode | Endpoint | Keterangan |
| --- | --- | --- |
| GET | `/api/wilayah?level=&parent_id=` | Daftar wilayah (publik) untuk dropdown berjenjang. |
| GET | `/api/wilayah/{id}` | Detail wilayah + rantai induk (provinsi > kota > kecamatan > kelurahan). |
| GET | `/api/fasilitas/peta` | GeoJSON `FeatureCollection` untuk peta. Query: `bbox=minLng,minLat,maxLng,maxLat`, `q`, `category`/`category_id`, `status`/`accessibility_status`, `province_id`, `city_id`, `district_id`, `region_id`. Hanya fasilitas berkoordinat; maksimum `MAP_MAX_FEATURES` fitur (ada metadata `truncated`). |
| POST | `/api/geocode` | Proxy geokoding ke Nominatim (admin). Body: `{ "q": "alamat" }`. |

### Kebijakan OpenStreetMap & Nominatim

- **Atribusi**: setiap peta menampilkan "© OpenStreetMap contributors".
- **Tile OSM standar** hanya untuk trafik ringan. Untuk produksi berskala besar, ganti ke penyedia tile lain (mis. MapTiler, Stadia Maps) lewat `OSM_TILE_URL` beserta atributinya.
- **Nominatim**: proxy `/api/geocode` mematuhi kebijakan penggunaan — menyertakan `User-Agent` yang jelas, membatasi **1 permintaan/detik** (per pengguna dan global), serta meng-cache hasil. Lihat <https://operations.osmfoundation.org/policies/nominatim/>.

### Privasi, Performa, dan Aksesibilitas Peta

- Izin lokasi hanya diminta setelah pengguna menekan tombol. **Koordinat pengguna tidak disimpan di server**; `lat`/`lng` hanya dipakai sekali untuk menghitung jarak/urutan.
- Leaflet dan tile dimuat **dinamis (lazy)** hanya saat tampilan peta dibuka; permintaan bounding box saat peta digeser di-*debounce* dan permintaan sebelumnya dibatalkan.
- Peta bukan satu-satunya cara mengakses data — semua informasi juga tersedia dalam daftar. Peta memiliki `aria-label` dan tautan "Lewati peta"; penanda/klaster dapat difokuskan dengan keyboard; perubahan hasil diumumkan lewat `aria-live`; kontrol peta berukuran minimal **44×44px** dan menghormati `prefers-reduced-motion`.
- **Koordinat data contoh perlu diverifikasi** sebelum dipakai untuk keperluan resmi. Fasilitas lama tanpa koordinat tetap valid dan ditandai **"Lokasi belum ditentukan"**.

### Opsi Lanjutan: PostgreSQL + PostGIS

Saat ini koordinat disimpan sebagai kolom biasa dan kueri peta memakai **bounding box** + rumus **Haversine** di PHP (kompatibel dengan SQLite/MySQL). Bila jumlah fasilitas bertambah besar atau butuh kueri spasial lanjutan, pertimbangkan PostgreSQL + PostGIS:

```sql
ALTER TABLE facilities ADD COLUMN geom geometry(Point, 4326);
UPDATE facilities
   SET geom = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)
 WHERE latitude IS NOT NULL AND longitude IS NOT NULL;
CREATE INDEX facilities_geom_idx ON facilities USING GIST (geom);
```

Lalu ganti filter bounding box dengan `ST_Within`/`ST_MakeEnvelope` dan pengurutan jarak dengan `ST_Distance`/`ST_DWithin`. Langkah ini opsional.


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
| `facility_categories` | Transportasi, Taman, Rumah Sakit, Gedung Pelayanan Publik |
| `accessibility_types` | Ramp / Jalur Kursi Roda, Toilet Difabel, Guiding Block, Parkir Difabel, Lift, Handrail |
| `issue_categories` | Fasilitas rusak, Fasilitas tidak tersedia, Akses terhalang, ... |
| `verification_statuses` | Menunggu Verifikasi, Terverifikasi, Ditolak, ... |

`DatabaseSeeder` memanggil `MasterDataSeeder`, `RegionSeeder` (wilayah DKI Jakarta), dan `FacilitySeeder` (contoh fasilitas). Instalasi bersih tidak membuat akun pengguna; gunakan `php artisan aksesibel:create-facility-manager` atau menu pendaftaran untuk membuat akun.

---

## Pengujian

```bash
php artisan test
```

Pengujian mencakup seluruh modul, termasuk: validasi lokasi (lat/lng di luar rentang, hanya salah satu terisi, kode pos salah, hierarki wilayah tidak konsisten, peringatan koordinat di luar Indonesia), fungsi Haversine & pengurutan jarak, filter bounding box `/api/fasilitas/peta` termasuk pembatasan jumlah fitur, endpoint wilayah berjenjang, dan jaminan bahwa fasilitas lama tanpa koordinat tetap valid serta tampil.

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

<details>
<summary>Panduan pengerjaan Modul Laporan Masyarakat (PBI-07–09)</summary>

Penanggung jawab modul: Riyana Kartika Pratiwi. PBI: 07, 08, dan 09.

Dokumen ini mencatat pekerjaan nyata dan membantu reviewer menghubungkan PBI,
perubahan kode, hasil uji, serta commit. Dokumen bukan bukti bahwa fitur sudah
diimplementasikan. Penilaian akhir tetap mengikuti rubrik dosen.

## Isi

- [Rencana harian dan pesan commit](rencana-harian.md)
- [Kriteria penerimaan PBI-07–09](kriteria-penerimaan.md)
- [Kontrak dan ketergantungan integrasi](integrasi.md)
- [Jurnal harian dan bukti pengerjaan](jurnal-harian.md)

## Hasil hari pertama

Empat hasil analisis: pembagian pekerjaan per tanggal, kriteria penerimaan,
pemetaan skema yang sudah ada, dan daftar ketergantungan antarmodul.
Hari pertama hanya menambah lima dokumen dalam folder ini. Perilaku aplikasi
belum berubah; implementasi fitur dimulai pada hari berikutnya.

Pesan commit:

```text
docs(laporan): petakan PBI dan rencana pengerjaan harian
```

Deskripsi commit:

```text
Petakan kriteria penerimaan PBI-07, PBI-08, dan PBI-09.
Susun target harian sampai 29 November 2026 dan format jurnal pengerjaan.
Catat relasi database, ketergantungan login, fasilitas, periode, dan status.
```

## Memasukkan hasil hari pertama di laptop melalui GitHub Desktop

1. Ekstrak paket hari pertama di luar folder project.
2. Salin folder `docs/laporan-masyarakat` ke
   `C:\Users\User\Documents\GitHub\aksesibel\docs\laporan-masyarakat`.
   Gabungkan dengan folder `docs` yang sudah ada bila diperlukan; pertahankan
   dokumen lain milik kelompok.
3. Buka GitHub Desktop, pilih repository `aksesibel`, lalu pilih branch kerja
   **`Riyana_branch`**, sesuai nama yang terlihat pada sesi setup.
4. Buka tab **Changes**. Periksa dan centang lima file dokumentasi modul ini.
   Perubahan lain yang sudah ada di laptop diperiksa terpisah.
5. Isi **Summary** dan **Description** dengan teks di atas.
6. Pastikan akun dan email Git pada pengaturan GitHub Desktop adalah milikmu,
   lalu klik **Commit to Riyana_branch**.
7. Klik **Push origin**. Jika branch belum dipublikasikan, gunakan
   **Publish branch**. Push memerlukan akses tulis ke repo kelompok.
8. Periksa **History** dan halaman branch di GitHub. Catat SHA commit milikmu
   pada bukti harian pribadi atau jurnal hari berikutnya.

Commit yang dibuat di salinan repo pendamping tidak otomatis masuk ke laptop
atau akun GitHub milikmu. Langkah Desktop di atas membuat commit dari hasil
analisis ini menggunakan identitas Git yang terpasang di laptopmu.

## Rutinitas harian

1. Mulai dari perubahan sebelumnya yang sudah di-commit. Ambil update `main`
   melalui **Fetch origin**, pilih `main`, lalu **Pull origin** bila tersedia.
   Kembali ke `Riyana_branch` dan gunakan **Branch → Merge into current branch**
   untuk memasukkan `main` jika ada perubahan baru. Selesaikan konflik sebelum
   melanjutkan pekerjaan.
2. Baca target tanggal hari ini dan catatan integrasi. Jalankan
   `composer install` jika dependensi berubah, serta `php artisan migrate`
   jika ada migration baru. Database yang dipakai harus database lokal project.
3. Kerjakan satu hasil yang dapat diperiksa. Untuk tugas besar, pecah menjadi
   beberapa commit yang masing-masing menjelaskan satu perubahan.
4. Jalankan pemeriksaan yang relevan. Catat hasil sebenarnya di jurnal.
   Tes fitur diprioritaskan untuk akses pengguna, aturan status, validasi,
   penyimpanan data, dan upload. Dokumentasi cukup diperiksa isinya dan diff.
5. Periksa diff di Desktop. Sertakan kode, migration baru jika ada, dan tes
   terkait. Tambahkan jurnal pada commit yang sama agar bukti terhubung.
6. Isi pesan commit sesuai hasil aktual, commit, lalu push sebelum mengakhiri
   pekerjaan hari tersebut. Screenshot tampilan atau hasil uji boleh menjadi
   bukti pendamping selama tidak berisi kredensial atau data pribadi asli.

Targetnya minimal satu commit bermakna per hari sesuai pekerjaan yang selesai.
Pesan dalam kalender adalah usulan: gunakan setelah hasilnya benar-benar ada.
Jika terhambat integrasi, catat temuan dan hasil investigasi yang bisa diperiksa,
lalu lanjutkan pekerjaan mandiri yang tidak bergantung pada hambatan tersebut.

## Pemeriksaan sesuai jenis perubahan

| Perubahan | Pemeriksaan | Bukti yang dicatat |
|---|---|---|
| Dokumentasi | Baca ulang kriteria, tautan, tanggal, dan `git diff --check` | Berkas dan hasil pemeriksaan |
| Alur laporan | Tes pengguna pemilik/bukan pemilik, status, dan validasi | Nama tes serta hasilnya |
| Kategori | Tes role pengelola, validasi, kategori nonaktif, dan referensi lama | Nama tes serta hasilnya |
| Lampiran | Tes file valid/tidak valid, ukuran, akses file, dan cleanup | Hasil uji dengan file dummy |
| Tampilan | `npm run build`, pemeriksaan desktop dan ponsel | Hasil build dan screenshot |
| Integrasi akhir | Alur lengkap PBI, tes relevan, dan build | Daftar kasus lulus/gagal dan PR |

Perintah PHP dijalankan menggunakan PHP yang cocok dengan `composer.lock`.
Di sesi setup, PHP 8.5.12 dan Composer 2.11.0 berhasil dikenali. Pengaturan
`$env:Path` dan `$env:PHPRC` yang dibuat saat setup hanya berlaku pada terminal
tersebut; periksa `php -v` saat membuka terminal lain.

Perubahan yang dimasukkan ke commit mengikuti standar database kelompok:
`.env`, `vendor`, `node_modules`, hasil build, file upload pengguna, dan dump
berisi data asli tidak masuk commit. Migration yang sudah berada di `main`
dipertahankan; perubahan skema dibuat melalui migration baru.

## Batas penyiapan agent

`AGENTS.md` repo meminta pemasangan Laravel Boost sebelum perubahan aplikasi
oleh agent. Saat mulai mengubah kode aplikasi, ikuti bootstrap tersebut,
baca ulang pedoman yang dihasilkan, dan periksa diff dependensinya. Paket
hari pertama ini hanya dokumentasi dan tidak mengubah dependensi aplikasi.

</details>
