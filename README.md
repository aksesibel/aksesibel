# Modul Laporan Masyarakat — panduan pengerjaan

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
