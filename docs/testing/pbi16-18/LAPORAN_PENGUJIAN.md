# Laporan Pengujian Otomatis PBI-16–18

Tanggal: **1 November 2026, Asia/Jakarta (WIB)**. Penanggung jawab modul: **Rajendra Aulya Marsa Ilham — 102022580015**. Branch tetap **`rajendra_branch`**.

## Hasil akhir

| Kelompok pengujian | Lulus | Gagal/error | Dilewati | Asersi PHPUnit | Bukti |
| --- | ---: | ---: | ---: | ---: | --- |
| Unit dan Feature pada MariaDB, termasuk 33 kasus collation MySQL | 208 | 0 | 0 | 917 | [JUnit fungsional](phpunit-mysql.xml) |
| Pengaman target database | 6 | 0 | 0 | 11 | [JUnit pengaman](phpunit-database-safety.xml) |
| Konkurensi dua proses PHP/koneksi InnoDB | 2 | 0 | 0 | 34 | [JUnit konkurensi](phpunit-concurrency.xml) |
| Browser Chromium melalui Playwright | 19 | 0 | 0 | — | [Hasil JSON](browser-results.json), [log](browser-run.log) |
| **Total verifikasi final** | **235** | **0** | **0** | **962, khusus PHPUnit** | [Ringkasan mesin](summary.json) |

Suite fungsional yang sudah berhasil tidak diulang saat melanjutkan pekerjaan. Enam tes pengaman dan dua tes konkurensi dijalankan terpisah, kemudian putaran akhir browser dijalankan setelah perbaikan. Durasi putaran akhir browser sekitar 23,4 detik. Tidak ada retry/flaky pada putaran tersebut. Build Vite, Laravel Pint, `composer validate --strict`, pemeriksaan route, dan `git diff --check` juga lulus.

Lingkungan aktual: PHP **8.4.2**, Laravel **13.39.0**, MariaDB **10.4.28**, Node **26.8.1**, Playwright **1.64.1**, dan Chromium hasil instalasi Playwright. Browser desktop memakai viewport 1440×1000; pengujian seluler menggunakan viewport 390×844 dalam Chromium.

## Isolasi dan keselamatan database

- Seluruh penulisan/reset pengujian mengarah ke **`aksesibel_testing`**, collation **`utf8mb4_unicode_ci`**. Database tersebut dibuat secara terpisah; 22 migration diterapkan setelah target diverifikasi.
- `phpunit.mysql.xml` memaksa `APP_ENV=testing`, `DB_CONNECTION=mysql`, `DB_DATABASE=aksesibel_testing`, serta `DB_URL` kosong. Cache konfigurasi pengujian menggunakan path berbeda dari aplikasi utama.
- `Tests\Support\TestingDatabaseSafety` memeriksa environment, URL koneksi, nama database dalam konfigurasi, dan **`SELECT DATABASE()`** sebelum trait refresh database berjalan. Guard juga dipakai skrip fixture browser dan worker konkurensi.
- Tes negatif membuktikan penolakan environment non-testing, nama database kerja, URL koneksi, SQLite berbasis file, serta target PDO efektif yang berbeda. Simulasi target efektif berbeda hanya memakai `information_schema` untuk pembacaan metadata dan langsung dikembalikan ke testing; tidak mengakses data aplikasi kerja.
- Kredensial koneksi dibaca lokal oleh Laravel tanpa dicetak. `.env`, `config/database.php`, dan `config/cache.php` tidak diubah.
- Browser memakai router khusus di **port 8876**, database testing, cookie sesi berbeda, serta cache/session database testing. Server tidak memakai atau menggunakan ulang server aplikasi kerja. Pemeriksaan setelah tes memastikan server port 8876 sudah berhenti.
- Fixture berisi akun/email fiktif `*.browser@example.test`, kategori/periode fiktif, tiga fasilitas, dan lima laporan. Password sementara diacak setiap reset, hanya tersimpan pada path testing yang diabaikan Git; password tidak masuk laporan atau screenshot.
- Tidak ada impor SQL, reset database kerja, commit, push, atau deployment. Migration/schema/kontrak aplikasi milik anggota lain tidak diubah.

## Skenario dan hasil aktual

| Skenario | Hasil aktual | Lapisan |
| --- | --- | --- |
| Guest membuka URL internal langsung | Dialihkan ke login; sembilan URL diuji di browser | PHPUnit + browser |
| Admin, Masyarakat, role tidak dikenal, dan role kosong | URL pengelolaan menghasilkan 403; navigasi pengelola tidak muncul | PHPUnit + browser |
| Login Pengelola dan logout | Navigasi lima modul tersedia; setelah logout URL internal kembali meminta login | PHPUnit + browser |
| Akun `inactive`/`suspended` | Login ditolak; sesi browser yang sebelumnya aktif dicabut pada request selanjutnya | PHPUnit + browser |
| Pembatasan percobaan login | Batas percobaan login teruji melalui feature test dengan cache testing | PHPUnit |
| Registrasi/profil antieskalasi | Penyisipan `role_id` dan `account_status` ditolak; registrasi sah menjadi Masyarakat | PHPUnit + browser |
| Pengguna: tambah/detail/edit/pencarian/peran/status/pagination | Tersimpan dan ditampilkan sesuai filter; perubahan hak akses diterapkan | PHPUnit + browser |
| Email duplikat dan password | Duplikasi ditolak; password di-hash; validasi panjang/konfirmasi berlaku; edit password kosong mempertahankan hash/password lama | PHPUnit + browser |
| Pengelola sendiri dan terakhir | Penonaktifan sendiri dan demosi pengelola aktif terakhir ditolak | PHPUnit + browser |
| Dua kategori: CRUD, trim, deskripsi, status, pencarian/pagination | Alur lengkap lulus; kategori dapat dinonaktifkan/diaktifkan tanpa mengubah histori | PHPUnit + browser |
| Kategori duplikat pada collation nyata | Variasi kapital, aksen, dan spasi yang ekuivalen ditolak oleh validasi dan unique index | PHPUnit + browser |
| Penghapusan kategori terpakai | Ditolak server dengan saran penonaktifan; termasuk request DELETE langsung dengan sesi/CSRF browser | PHPUnit + browser |
| Periode: CRUD/tanggal/overlap/pagination | Tanggal akhir sebelum awal ditolak; overlap diterima; edit tanggal tidak memindahkan FK laporan | PHPUnit + browser |
| Penghapusan periode terpakai | Ditolak server; hubungan laporan dipertahankan | PHPUnit + browser |
| Statistik dan tujuh kombinasi filter | Lima laporan dihitung sesuai `period_id`, kategori fasilitas, kategori kendala; kartu/grafik/rekap konsisten | PHPUnit + browser |
| Banyak bukti/riwayat/progres pada satu laporan | Tetap dihitung sekali. Fixture laporan pertama memiliki dua bukti, dua riwayat, dan dua progres | PHPUnit + browser |
| Rekap status | Total status A=2 dan B=3 pada fixture lima laporan | PHPUnit + browser |
| Kondisi kosong/reset dan data historis nonaktif | Kondisi kosong jelas, reset mengembalikan lima laporan; kategori historis nonaktif tetap terbaca | PHPUnit + browser |
| Seeder idempotent | Pengulangan tidak menggandakan data, mengubah peran pengguna, atau mereset password lama; tidak membuat akun tetap | PHPUnit |
| Formulir lintas origin tanpa token CSRF | Browser nyata mengirim `Sec-Fetch-Site: cross-site`; server menjawab **419**, tetap guest | Browser |
| Keyboard dan tampilan seluler | Skip link dapat difokuskan/digunakan; kelima halaman memiliki document width=viewport=390 setelah perbaikan | Browser |

Dashboard aktual menyediakan tiga filter: periode, kategori fasilitas, kategori kendala. **Status adalah dimensi agregasi, bukan dropdown filter tersendiri**; tidak ditambahkan fitur status filter baru saat pengujian ini.

## Konkurensi MySQL/MariaDB

Konkurensi diuji pada service `UserManagement`: dua worker PHP dijalankan sebagai proses independen dengan koneksi database berbeda. Ini bukan pengujian dua request HTTP ke server web. Koordinator mengunci baris role; koneksi observer terpisah memeriksa `information_schema.INNODB_TRX`. Kedua worker harus benar-benar berada pada **`LOCK WAIT`** sebelum koordinator melepas lock.

1. **Dua pengelola menurunkan peran sendiri bersamaan:** tepat satu berhasil, satu ditolak validasi, dan satu pengelola aktif tetap ada.
2. **Dua pengelola saling menangguhkan bersamaan:** tepat satu berhasil; pelaku yang sudah kehilangan hak ditolak otorisasi setelah pembacaan ulang; satu pengelola aktif tetap ada.

Kedua tes lulus. Cleanup hanya menghapus ID fixture milik tes. Ini pengujian dua transaksi yang bertabrakan, bukan pengujian beban besar.

## Temuan dan perbaikan

| Temuan | Perbaikan | Verifikasi |
| --- | --- | --- |
| Label `sr-only` absolut pada tabel pengguna menambah lebar dokumen menjadi 596 px pada viewport 390 px | Tambahkan `relative` pada `.table-wrap` agar elemen absolut tetap dibatasi wrapper tabel yang dapat digulir | Lima halaman kini document width 390 px; putaran akhir 19 browser tests lulus |
| Tes migration awal mengandalkan rollback DDL SQLite | Gunakan koneksi dan dua tabel kategori berprefix acak; cleanup terbatas pada fixture tersebut; tes dependensi statistik memakai alias tanpa tabel tanpa rename DDL | Seluruh suite MariaDB lulus |
| Guard baru awalnya memeriksa URL hanya dari konfigurasi koneksi yang sudah diparse Laravel | Periksa juga URL pada konfigurasi asal, karena parser dapat menghapus field URL | Enam tes pengaman database lulus |
| Pengamatan lock kedua sempat timeout dalam harness | Gunakan koneksi observer terpisah di luar transaksi koordinator | Dua skenario konkurensi lulus dengan observasi kedua worker `LOCK WAIT` |
| Tes CSRF awal mengasumsikan token wajib meskipun origin valid | Sesuaikan tes dengan Laravel 13; gunakan halaman origin lokal asing yang mengirim form ke server testing | Request nyata `cross-site` menghasilkan 419; middleware keamanan aplikasi tidak diubah |

Putaran browser awal menghasilkan **17 lulus/2 gagal**, disimpan dalam [JSON awal](browser-initial-results.json). Percobaan terarah kemudian memverifikasi koreksi; putaran akhir menghasilkan **19/19 lulus**. Berkas screenshot berawalan `failed-` adalah bukti historis, bukan kegagalan putaran akhir.

Percobaan awal koneksi database/browser di dalam sandbox terhalang permission; dijalankan ulang dengan akses yang diperlukan. Percobaan tambahan SQLite awal juga menangkap kekurangan pemeriksaan URL pada guard yang baru dibuat: 179 lulus/1 gagal/34 dilewati. Kasus guard tersebut telah diperbaiki dan lulus pengujian terarah; suite SQLite lengkap tidak diulang setelah instruksi lanjutan untuk memakai hanya `aksesibel_testing`. [Artefak SQLite awal](phpunit-sqlite-initial.xml) tidak termasuk total verifikasi final di atas.

Perilaku origin CSRF diverifikasi terhadap [dokumentasi Laravel 13](https://github.com/laravel/docs/blob/13.x/csrf.md) dan middleware versi yang terpasang. Playwright dipasang sesuai [panduan resminya](https://playwright.dev/docs/intro).

## Screenshot

Tersedia **26 screenshot hasil akhir** dan tiga screenshot kegagalan awal.

- [Navigasi pengelola](screenshots/02-pengelola-navigasi.png)
- [Akses langsung Admin ditolak](screenshots/03-akses-ditolak-admin.png)
- [Sesi nonaktif dicabut](screenshots/08-sesi-inactive-dicabut.png)
- [Pengelola terakhir dilindungi](screenshots/09-pengelola-terakhir-dilindungi.png)
- [Kategori fasilitas terpakai dilindungi](screenshots/11-kategori-fasilitas-terpakai-dilindungi.png)
- [Validasi tanggal periode](screenshots/12-validasi-tanggal-periode.png)
- [Statistik seluruh laporan](screenshots/15-statistik-semua-laporan.png)
- [Statistik tiga filter](screenshots/16-statistik-tiga-filter.png)
- [Statistik seluler setelah perbaikan](screenshots/18-statistik-seluler.png)
- [CSRF lintas origin ditolak](screenshots/01b-csrf-tanpa-token-ditolak.png)

Hasil akhir mencatat **0 error JavaScript, 0 resource gagal, dan 0 error HTTP asset**. Respons dokumen 403/419 yang memang diuji tidak dianggap kegagalan asset.

## Menjalankan ulang

```sh
# Hanya menyiapkan database khusus testing, dengan verifikasi target.
php tests/Support/prepare-mysql.php

# Semua suite MySQL/MariaDB, termasuk pengaman dan konkurensi.
vendor/bin/phpunit --configuration phpunit.mysql.xml

# Instalasi browser diperlukan sekali pada mesin baru.
npm ci
npx playwright install chromium

# Runner mereset HANYA aksesibel_testing, membuat fixture, menjalankan
# server khusus, menguji browser, lalu menghentikan server.
npm run test:browser
```

Untuk menjalankan subset gunakan `--testsuite Unit,Feature` atau `--testsuite MysqlConcurrency`; tes konkurensi terpisah memerlukan schema testing siap dan tidak ada fixture pengelola aktif yang tertinggal. Menjalankan semua suite secara berurutan membersihkan data pengujian melalui trait sebelum suite konkurensi. Jangan menjalankan suite backend dan browser bersamaan: keduanya memakai database testing yang sama.

Koneksi utama tidak diubah. Kredensial lokal tidak boleh disalin ke perintah, dokumentasi, atau commit. Lingkungan dengan sandbox perlu izin untuk koneksi MariaDB lokal, proses browser, dan port server testing.

## Yang belum diuji atau di luar cakupan

- Firefox, WebKit, perangkat Android/iOS fisik, dan pembaca layar manual belum diuji. Tampilan seluler memakai emulasi viewport Chromium.
- Dua request HTTP serentak ke web server, beban besar, puluhan transaksi bersamaan, dan lingkungan deployment/HTTPS produksi belum diuji. Dua operasi service bersamaan pada InnoDB telah diuji seperti dijelaskan di atas.
- UI operasional fasilitas, aksesibilitas, pelaporan, verifikasi, dan tindak lanjut anggota lain belum tersedia; pengujian memakai fixture data khusus testing, bukan mengklaim CRUD modul tersebut selesai.
- Konflik dokumen/role legacy serta dua status master milik modul verifikasi dari audit sebelumnya tidak diubah sepihak oleh pekerjaan pengujian ini.

Verifikasi fitur aktual PBI-16–18 pada MariaDB dan Chromium selesai dengan batas pengujian yang disebutkan di atas. Tidak ada kegagalan yang tersisa pada verifikasi final.
