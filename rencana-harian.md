# Rencana harian PBI-07–09

Mulai: 29 Oktober 2026. Target serah terima sementara: 29 November 2026.
Rentang tanggal inklusif: 32 hari. Implementasi inti ditargetkan selesai
21 November; 22–28 November dipakai untuk mutu, integrasi, dan perbaikan.

Kalender ini adalah **rencana**, bukan riwayat pekerjaan yang sudah selesai.
Semua hasil setelah hari pertama masih menunggu implementasi dan pemeriksaan.
Setiap hari: hasil kerja + pemeriksaan yang relevan + jurnal + commit + push.
Sesuaikan urutan bila dependensi kelompok belum tersedia dan catat alasannya.

| Hari | Tanggal | Hasil kerja yang ditargetkan | Pemeriksaan atau bukti | Usulan pesan commit setelah selesai |
|---|---|---|---|---|
| 01 | 29 Okt 2026 | Peta PBI, rencana harian, audit skema, dan jurnal awal | Pemeriksaan dokumen dan diff | `docs(laporan): petakan PBI dan rencana pengerjaan harian` |
| 02 | 30 Okt 2026 | Layout Blade modul, navigasi, dan halaman awal laporan; catat kontrak login dengan tim | Tampilan desktop/ponsel dan build; daftar keputusan integrasi | `feat(laporan): tambahkan layout dan navigasi modul` |
| 03 | 31 Okt 2026 | Daftar laporan milik pengguna melalui autentikasi dan pembatasan pemilik | Pengguna A tidak melihat laporan B; tamu ditangani melalui alur login tim | `feat(laporan): tampilkan daftar laporan milik pengguna` |
| 04 | 01 Nov 2026 | Form laporan mengikuti mockup, pilihan fasilitas, kategori aktif, dan periode yang valid | Form dengan data tersedia serta kondisi fasilitas/periode kosong | `feat(laporan): tambahkan formulir pengajuan laporan` |
| 05 | 02 Nov 2026 | Validasi pengajuan, pesan error per kolom, dan pengisian ulang input yang aman | Deskripsi kosong, ID tidak valid, kategori nonaktif, periode tidak sah | `feat(laporan): validasi data pengajuan dan tampilkan kesalahan` |
| 06 | 03 Nov 2026 | Simpan laporan dengan pelapor dari sesi, status awal, periode, dan nomor unik | Record tersimpan lengkap; dua pengajuan tidak memperoleh nomor sama | `feat(laporan): simpan laporan dengan nomor unik dan status awal` |
| 07 | 04 Nov 2026 | Halaman detail laporan dengan fasilitas, kategori, waktu, dan status | Pemilik dapat membuka; pengguna lain ditolak melalui URL langsung | `feat(laporan): tampilkan detail laporan dengan akses pemilik` |
| 08 | 05 Nov 2026 | Edit laporan hanya saat Menunggu Verifikasi | Edit sendiri saat pending berhasil; status lain dan pemilik lain ditolak | `feat(laporan): batasi perubahan laporan pada status menunggu` |
| 09 | 06 Nov 2026 | Pembatalan laporan pending dengan konfirmasi dan status Dibatalkan | Batal pending berhasil; ulang batal/status lain ditangani; record tetap ada | `feat(laporan): tambahkan pembatalan laporan menunggu verifikasi` |
| 10 | 07 Nov 2026 | Tes regresi inti PBI-07 untuk akses, status, dan penyimpanan | Simpan hasil tes; perbaiki temuan sebelum menandai lulus | `test(laporan): uji kepemilikan dan perubahan status laporan` |
| 11 | 08 Nov 2026 | Bukti demo inti PBI-07, batas yang belum selesai, dan draft PR untuk review kelompok | PR berisi cara mencoba dan hasil tes; supplemental masih dilacak | `docs(laporan): dokumentasikan demo inti PBI-07 dan hasil uji` |
| 12 | 09 Nov 2026 | Daftar kategori kendala untuk role pengelola yang disepakati | Role Masyarakat ditolak melalui tampilan dan endpoint langsung | `feat(kategori): tampilkan pengelolaan kategori bagi admin` |
| 13 | 10 Nov 2026 | Tambah kategori dengan validasi nama dan pencegahan duplikat | Nama kosong dan nama setara setelah normalisasi ditolak | `feat(kategori): tambahkan kategori kendala dengan validasi nama` |
| 14 | 11 Nov 2026 | Edit nama/deskripsi kategori melalui pengelola berwenang | Perubahan valid tersimpan; pengguna tidak berwenang ditolak | `feat(kategori): tambahkan perubahan nama dan deskripsi kategori` |
| 15 | 12 Nov 2026 | Aktif/nonaktif kategori dan aturan penghapusan yang menjaga laporan lama | Kategori terpakai tidak menghilangkan relasi laporan | `feat(kategori): kelola status aktif tanpa merusak riwayat laporan` |
| 16 | 13 Nov 2026 | Pilihan kategori aktif di form serta perlindungan pada endpoint penyimpanan | ID kategori nonaktif yang dikirim manual ditolak; riwayat tetap terbaca | `feat(kategori): terapkan kategori aktif pada pengajuan laporan` |
| 17 | 14 Nov 2026 | Tes PBI-08 dan bukti review kategori; finalisasi rancangan informasi tambahan | Tes role/duplikat/nonaktif; dokumen kontrak supplemental dan status | `test(kategori): uji otorisasi dan aturan kategori kendala` |
| 18 | 15 Nov 2026 | Validasi foto/video dan pesan batas upload sesuai keputusan tim | MIME, format, ukuran, jumlah file; dokumentasi batas PHP yang dibutuhkan | `feat(bukti): validasi format dan ukuran lampiran laporan` |
| 19 | 16 Nov 2026 | Simpan bukti pada storage privat dan metadata sesuai skema | File dummy dan metadata konsisten; kegagalan penyimpanan dibersihkan | `feat(bukti): simpan lampiran privat dan metadata bukti` |
| 20 | 17 Nov 2026 | Preview/unduh bukti melalui endpoint dengan pemeriksaan akses | Pemilik/role berwenang berhasil; pengguna lain dan URL langsung ditolak | `feat(bukti): lindungi akses pratinjau dan unduhan lampiran` |
| 21 | 18 Nov 2026 | Ganti/hapus lampiran saat laporan masih boleh diubah | Aturan status berlaku; file dan metadata tidak tertinggal | `feat(bukti): kelola lampiran selama laporan menunggu verifikasi` |
| 22 | 19 Nov 2026 | Tes PBI-09 untuk file tidak valid, akses, dan cleanup | Hasil tes upload dan akses file; skenario kegagalan tersimpan | `test(bukti): uji upload akses privat dan pembersihan file` |
| 23 | 20 Nov 2026 | Informasi tambahan saat diminta verifikator, disimpan tanpa menimpa laporan awal | Deskripsi awal tetap; tambahan tercatat dengan aktor/waktu; akses terjaga | `feat(laporan): catat informasi tambahan tanpa menimpa laporan awal` |
| 24 | 21 Nov 2026 | Timeline dari data verifikasi/tindak lanjut yang tersedia; lengkapi uji supplemental dan review semua PBI | Uji status permintaan informasi, histori, dan batas antarrole | `feat(laporan): tampilkan riwayat proses dan informasi tambahan` |
| 25 | 22 Nov 2026 | Penyelarasan mockup dan aksesibilitas form/daftar/detail | Keyboard, label, fokus, pesan error, desktop dan ponsel | `style(laporan): rapikan tampilan responsif dan aksesibilitas form` |
| 26 | 23 Nov 2026 | Paginasi dan filter laporan milik sendiri, serta relasi tanpa query berulang | Filter tidak membocorkan data; cek query pada data contoh yang cukup | `perf(laporan): optimalkan daftar filter dan paginasi laporan` |
| 27 | 24 Nov 2026 | Uji alur lengkap dari kategori aktif hingga laporan, bukti, dan status | Matriks PBI diperbarui; tes relevan serta build selesai | `test(laporan): tambahkan pengujian alur lengkap PBI-07 sampai PBI-09` |
| 28 | 25 Nov 2026 | Uji kondisi batas dari hasil review dan perbaikan yang diperlukan | Skenario nomor bersamaan, status berubah, dan upload gagal | `test(laporan): lengkapi pengujian kondisi batas pelaporan` |
| 29 | 26 Nov 2026 | Integrasi dengan main terbaru dan pemeriksaan lintas modul | Hasil uji login, fasilitas, periode, verifikasi, dan tindak lanjut | `docs(integrasi): catat hasil uji modul laporan bersama tim` |
| 30 | 27 Nov 2026 | Panduan pengguna, skrip demo, dan bukti fitur dengan data fiktif | Setiap PBI mempunyai langkah demo dan bukti | `docs(laporan): tambahkan panduan pengguna dan skenario demo` |
| 31 | 28 Nov 2026 | Verifikasi akhir, penutupan temuan, dan catatan kandidat rilis | Catat hasil tes/build aktual, kekurangan tersisa, dan tautan PR | `docs(laporan): catat verifikasi akhir dan kesiapan rilis modul` |
| 32 | 29 Nov 2026 | Serah terima modul, daftar perubahan, dan rekap kontribusi | Tautan commit/PR/bukti lengkap; status merge dicatat sesuai kenyataan | `docs(laporan): lengkapi serah terima dan rekap kontribusi modul` |

## Milestone review

| Tanggal target | Kondisi yang diharapkan |
|---|---|
| 30 Oktober | Pemilik modul lain mengetahui kebutuhan login, fasilitas, periode, dan status |
| 8 November | Inti PBI-07 siap direview: buat, daftar, detail, edit, dan batal |
| 14 November | PBI-08 siap direview; rancangan informasi tambahan disepakati |
| 21 November | Ketiga PBI terhubung, termasuk bukti, informasi tambahan, dan timeline |
| 28 November | Uji akhir, integrasi, dokumentasi, dan bukti demo siap |
| 29 November | Serah terima sesuai prosedur kelompok dan dosen |

## Ketika jadwal bergeser

- Jika autentikasi belum tersedia pada hari 03, lanjutkan layout/form dan
  kontrak route tanpa menambahkan bypass login atau identitas pengguna tetap.
  Akses data pribadi baru dinyatakan siap setelah autentikasi terhubung.
- Jika fasilitas/periode belum tersedia, minta data contoh dari pemilik modul.
  Fixture pengujian boleh memakai data fiktif sesuai skema; data runtime modul
  lain tetap dikelola pemiliknya.
- Jika status Dibatalkan atau permintaan informasi belum disediakan,
  dokumentasikan kebutuhan untuk pemilik tabel status. Hindari memakai ID
  status rekaan. Kerjakan bagian independen dan geser tanggal implementasinya.
- Jika review menemukan bug, buat commit perbaikan dengan pesan `fix(...)`
  yang menyebut masalah sebenarnya, lalu sesuaikan jurnal dan kalender.
- Review kelompok dan merge bergantung pada tim. Tanggal dalam tabel adalah
  target pengerjaan, bukan klaim bahwa PR sudah diajukan atau digabung.
