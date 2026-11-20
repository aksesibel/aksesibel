# Aksesibel

Platform informasi aksesibilitas fasilitas publik. Aplikasi ini dibangun dengan **Laravel 13** (Blade + Alpine.js + Tailwind) dan menyediakan pengelolaan fasilitas, kategori, serta **alamat, wilayah administratif, dan koordinat** untuk tampilan daftar dan peta.

> Catatan teknologi: spesifikasi awal menyebut Next.js + Prisma + Zod + react-leaflet, namun proyek yang berjalan adalah Laravel. Pemetaan padanannya:
> - Prisma → **Eloquent + Migration Laravel**
> - Zod → **FormRequest + Validator Laravel**
> - react-leaflet → **Leaflet + Leaflet.markercluster** (di-load dinamis di sisi klien)

## Fitur Lokasi

- **Model `Region` hierarkis** (self-relation): `PROVINSI > KOTA_KABUPATEN > KECAMATAN > KELURAHAN` dengan kode Kemendagri.
- **Perluasan `Facility`**: wilayah berjenjang (`province_id`, `city_region_id`, `district_id`, `region_id`), koordinat (`latitude`, `longitude`), kode pos, catatan lokasi, catatan pintu masuk aksesibel, transportasi terdekat, dan sumber koordinat.
- **Form admin** dengan dropdown wilayah berjenjang, pemilih titik peta Leaflet (klik/seret penanda), input angka lat/lng, tombol geser arah (alternatif tanpa mouse), geokoding alamat, "Gunakan lokasi saya", serta pratinjau peta.
- **Halaman publik `/fasilitas`** dengan pengalih **Daftar | Peta**, marker clustering, "Cari di area ini", "Dekat saya", filter wilayah, dan tautan yang dapat dibagikan.
- **Halaman detail `/fasilitas/{id}`** dengan rantai wilayah lengkap, peta mini, "Salin alamat", dan "Petunjuk arah".

## Persyaratan

- PHP 8.3+
- Composer
- MySQL (default) atau SQLite/PostgreSQL
- Node.js (opsional, untuk build aset Vite)

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

Jalankan server:

```bash
php artisan serve
```

## Basis Data

Konfigurasi koneksi ada di `.env` (`DB_CONNECTION`, dst.). Default proyek memakai **MySQL**. Untuk pengembangan cepat bisa memakai SQLite:

```
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

### Migrasi & Seed

```bash
php artisan migrate           # jalankan migrasi
php artisan db:seed           # seed data master, wilayah DKI Jakarta, dan contoh fasilitas
php artisan migrate:fresh --seed   # reset + seed ulang
```

Seed wilayah bersifat **idempoten** (upsert berdasarkan `code`), sehingga aman dijalankan berulang. Saat ini seed memuat:
- Provinsi **DKI Jakarta**
- 5 kota/kabupaten administrasi
- Seluruh kecamatan DKI Jakarta
- Seluruh kelurahan untuk Kecamatan **Menteng, Gambir, Tanah Abang, dan Setiabudi**

### Import Wilayah Lain (CSV)

Sediakan file CSV dengan format `code,name,level,parent_code`:

```csv
code,name,level,parent_code
31,DKI Jakarta,PROVINSI,
31.71,Kota Jakarta Pusat,KOTA_KABUPATEN,31
31.71.01,Menteng,KECAMATAN,31.71
31.71.01.1001,Menteng,KELURAHAN,31.71.01
```

Level valid: `PROVINSI | KOTA_KABUPATEN | KECAMATAN | KELURAHAN`.

Jalankan skrip import:

```bash
php scripts/import-regions.php path/to/wilayah.csv
php scripts/import-regions.php path/to/wilayah.csv --dry-run   # uji tanpa menyimpan
```

Skrip memakai upsert berdasarkan `code` (tidak menghasilkan duplikat). Data wilayah lengkap Indonesia dapat diambil dari <https://github.com/cahyadsn/wilayah> (lisensi MIT).

## Variabel Lingkungan

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

## API

Semua respons JSON. Pesan error dalam Bahasa Indonesia dengan status HTTP yang tepat.

| Metode | Endpoint | Keterangan |
| --- | --- | --- |
| GET | `/api/wilayah?level=&parent_id=` | Daftar wilayah (publik) untuk dropdown berjenjang. |
| GET | `/api/wilayah/{id}` | Detail wilayah + rantai induk (provinsi > kota > kecamatan > kelurahan). |
| GET | `/api/fasilitas/peta` | GeoJSON `FeatureCollection` untuk peta. Query: `bbox=minLng,minLat,maxLng,maxLat`, `q`, `category`/`category_id`, `status`/`accessibility_status`, `province_id`, `city_id`, `district_id`, `region_id`. Hanya fasilitas berkoordinat; maksimum `MAP_MAX_FEATURES` fitur (ada metadata `truncated`). |
| GET | `/fasilitas` | Daftar publik. Query: `q`, `category_id`, `accessibility_status`, `province_id`, `city_id`, `district_id`, `region_id`, `lat`, `lng`, `sort=jarak`. Respons menyertakan `distanceKm` bila relevan. |
| POST | `/api/geocode` | Proxy geokoding ke Nominatim (admin). Body: `{ "q": "alamat" }`. |

## Kebijakan OpenStreetMap & Nominatim

Aplikasi ini memakai layanan gratis OpenStreetMap. Mohon patuhi kebijakannya:

- **Atribusi**: setiap peta menampilkan "© OpenStreetMap contributors".
- **Tile OSM standar** hanya untuk trafik ringan. Untuk produksi berskala besar, ganti ke penyedia tile lain (mis. MapTiler, Stadia Maps) lewat `OSM_TILE_URL` beserta atributinya.
- **Nominatim**: proxy `/api/geocode` sudah mematuhi kebijakan penggunaan — menyertakan `User-Agent` yang jelas, membatasi **1 permintaan/detik** (per pengguna dan global), serta meng-cache hasil. Ganti `GEOCODING_USER_AGENT` dengan identitas aplikasi Anda sebelum produksi. Lihat <https://operations.osmfoundation.org/policies/nominatim/>.

## Privasi & Performa

- Izin lokasi hanya diminta setelah pengguna menekan tombol, dengan penjelasan singkat.
- **Koordinat pengguna tidak disimpan di server**; `lat`/`lng` hanya dipakai sekali untuk menghitung jarak/urutan.
- Leaflet dan tile dimuat **dinamis (lazy)** hanya saat tampilan peta dibuka.
- Permintaan bounding box saat peta digeser di-*debounce* dan permintaan sebelumnya dibatalkan (*AbortController*).
- Respons wilayah dan hasil geokoding di-cache.

## Aksesibilitas (WCAG 2.1 AA)

- Peta bukan satu-satunya cara mengakses data — semua informasi juga tersedia dalam daftar.
- Peta memiliki `aria-label` dan tautan "Lewati peta".
- Penanda/klaster dapat difokuskan dengan keyboard; popup ditutup dengan `Esc` dan fokus kembali ke penanda.
- Pemilih titik di admin punya alternatif tanpa mouse (input angka + tombol geser arah dengan langkah yang dapat diatur).
- Perubahan hasil diumumkan lewat `aria-live`.
- Kontrol peta berukuran minimal **44×44px** dan menghormati `prefers-reduced-motion`.

## Pengujian

```bash
php artisan test
```

Pengujian mencakup: validasi lokasi (lat/lng di luar rentang, hanya salah satu terisi, kode pos salah, hierarki wilayah tidak konsisten, peringatan koordinat di luar Indonesia), fungsi Haversine & pengurutan jarak, filter bounding box `/api/fasilitas/peta` termasuk pembatasan jumlah fitur, endpoint wilayah berjenjang, dan jaminan bahwa fasilitas lama tanpa koordinat tetap valid serta tampil.

## Catatan Data Contoh

- **Koordinat data contoh perlu diverifikasi** sebelum dipakai untuk keperluan resmi.
- Fasilitas lama yang belum memiliki koordinat tetap valid dan ditandai **"Lokasi belum ditentukan"** pada daftar maupun halaman detail.

## Beralih ke PostgreSQL + PostGIS

Saat ini koordinat disimpan sebagai kolom biasa (`latitude`, `longitude`) dan kueri peta memakai **bounding box** + rumus **Haversine** di PHP. Pendekatan ini kompatibel dengan SQLite/MySQL dan cukup untuk skala data sedang. Bila jumlah fasilitas bertambah besar atau butuh kueri spasial lanjutan (radius, jarak, polygon), pertimbangkan PostgreSQL + PostGIS:

1. Ganti driver:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=aksesibel
   DB_USERNAME=postgres
   DB_PASSWORD=secret
   ```
2. Aktifkan ekstensi: `CREATE EXTENSION postgis;`
3. Tambahkan kolom geometri dan isi dari lat/lng:
   ```sql
   ALTER TABLE facilities ADD COLUMN geom geometry(Point, 4326);
   UPDATE facilities
      SET geom = ST_SetSRID(ST_MakePoint(longitude, latitude), 4326)
    WHERE latitude IS NOT NULL AND longitude IS NOT NULL;
   CREATE INDEX facilities_geom_idx ON facilities USING GIST (geom);
   ```
4. Ganti filter bounding box dengan `ST_Within`/`ST_MakeEnvelope` dan pengurutan jarak dengan `ST_Distance`/`ST_DWithin` (operator `<->` didukung indeks GIST).

Langkah ini bersifat opsional dan tidak diperlukan selama aplikasi berjalan pada MySQL/SQLite.
