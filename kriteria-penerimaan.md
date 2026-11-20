# Kriteria penerimaan dan matriks bukti

Status awal seluruh kasus: **Belum diuji**. Pemeriksaan hari pertama hanya
mencakup dokumen dan struktur repo. Isi hasil, tanggal, dan bukti ketika
implementasi diuji. Gunakan proposal/mockup kelompok sebagai acuan tampilan;
detail yang belum disepakati dicatat di [integrasi](integrasi.md).

## PBI-07 — pengelolaan laporan masyarakat

| ID | Perilaku yang harus dibuktikan | Cara memeriksa | Status awal |
|---|---|---|---|
| LM-07-01 | Pengguna masuk dapat mengirim laporan valid | Pilih fasilitas, kategori aktif, periode sah, dan deskripsi; kirim | Belum diuji |
| LM-07-02 | Pelapor berasal dari sesi dan tidak dapat dipalsukan melalui input | Kirim `user_id` pengguna lain secara manual | Belum diuji |
| LM-07-03 | Laporan baru mendapat nomor unik dan status Menunggu Verifikasi | Periksa record dan kirim dua pengajuan berdekatan | Belum diuji |
| LM-07-04 | Input tidak valid ditolak dengan pesan yang jelas | Deskripsi kosong, ID asing, kategori nonaktif, periode tidak sah | Belum diuji |
| LM-07-05 | Daftar/filter/paginasi hanya memuat laporan milik pengguna | Buat laporan untuk A dan B; buka sebagai A | Belum diuji |
| LM-07-06 | Detail laporan pengguna lain ditolak pada server | Buka URL detail laporan B sebagai A | Belum diuji |
| LM-07-07 | Pemilik hanya dapat mengedit saat Menunggu Verifikasi | Coba edit pada status pending dan status lain | Belum diuji |
| LM-07-08 | Pemeriksaan status berlaku saat penyimpanan | Buka form, ubah status lewat alur tim, kemudian kirim form lama | Belum diuji |
| LM-07-09 | Pembatalan pending memakai status Dibatalkan dan mempertahankan record | Batalkan laporan pending; cek record dan status | Belum diuji |
| LM-07-10 | Informasi tambahan dicatat saat diminta tanpa menimpa deskripsi awal | Kirim tambahan; periksa isi awal, aktor, waktu, dan lampiran | Belum diuji |
| LM-07-11 | Tampilan detail menjelaskan proses berdasarkan histori yang tersedia | Jalankan proses lintas modul dan cocokkan timeline dengan data | Belum diuji |
| LM-07-12 | Kondisi kategori/fasilitas/periode kosong ditangani secara jelas | Jalankan form pada fixture yang tidak mempunyai pilihan sah | Belum diuji |

## PBI-08 — kategori kendala

Pengguna fitur pengelolaan kategori adalah role pengelola yang disepakati tim.
Status Riyana sebagai pembuat modul tidak membuat role Masyarakat memiliki
hak administrasi. Usulan role awal: Admin.

| ID | Perilaku yang harus dibuktikan | Cara memeriksa | Status awal |
|---|---|---|---|
| LM-08-01 | Pengelola berwenang dapat melihat, membuat, dan mengedit kategori | Masuk sebagai role yang disepakati; lakukan operasi | Belum diuji |
| LM-08-02 | Masyarakat tidak dapat mengelola kategori | Akses halaman dan kirim request langsung sebagai Masyarakat | Belum diuji |
| LM-08-03 | Nama kosong dan duplikat ditangani | Coba nama kosong, spasi, dan nama setara sesuai aturan tim | Belum diuji |
| LM-08-04 | Kategori dapat diaktifkan/nonaktifkan | Ubah status aktif dan periksa pilihan pada form laporan | Belum diuji |
| LM-08-05 | Kategori nonaktif ditolak untuk laporan baru di server | Kirim ID kategori nonaktif secara manual | Belum diuji |
| LM-08-06 | Laporan lama tetap memiliki kategori dan dapat dibaca | Nonaktifkan kategori yang dipakai laporan lama | Belum diuji |
| LM-08-07 | Penghapusan kategori terpakai menjaga referensi laporan | Coba penghapusan kategori yang mempunyai laporan | Belum diuji |

## PBI-09 — bukti foto/video

Batas format, ukuran, dan jumlah file ditetapkan bersama sebelum implementasi.
Kasus uji menggunakan file dummy; hasil akhir harus sesuai validasi aplikasi
dan batas upload PHP pada lingkungan demo.

| ID | Perilaku yang harus dibuktikan | Cara memeriksa | Status awal |
|---|---|---|---|
| LM-09-01 | Foto/video yang diperbolehkan dapat diunggah | Upload file dummy valid dan cocokkan metadata | Belum diuji |
| LM-09-02 | MIME/format, ukuran, dan jumlah yang melanggar ditolak | Uji batas tepat, melewati batas, dan file berganti ekstensi | Belum diuji |
| LM-09-03 | File tersimpan privat dan diakses melalui otorisasi | Coba URL langsung di luar endpoint berwenang | Belum diuji |
| LM-09-04 | Pengguna lain tidak dapat membaca, mengganti, atau menghapus bukti | Coba operasi terhadap lampiran laporan B sebagai A | Belum diuji |
| LM-09-05 | Pemilik hanya mengganti/menghapus bukti ketika aturan laporan mengizinkan | Uji status pending serta status setelah diproses | Belum diuji |
| LM-09-06 | Kegagalan transaksi/upload tidak meninggalkan file atau metadata yatim | Simulasikan kegagalan penyimpanan setelah file diterima | Belum diuji |
| LM-09-07 | Bukti informasi tambahan terpisah dari bukti awal sesuai kontrak | Minta informasi tambahan, upload, lalu periksa urutan dan relasinya | Belum diuji |

## Syarat suatu PBI dinyatakan selesai

- Kriteria terkait mempunyai hasil aktual, tanggal, dan tautan bukti.
- Otorisasi diuji melalui request langsung; menyembunyikan tombol saja belum
  membuktikan pembatasan akses.
- Tes yang relevan lulus dan temuan dicatat atau diperbaiki.
- Tampilan dibandingkan dengan mockup pada desktop dan ponsel.
- Migration baru, model yang relevan, dan seeder milik modul saling sesuai.
- PR menjelaskan perilaku, cara mencoba, hasil uji, dan ketergantungan tim.
- Status review/merge dicatat sesuai keadaan sebenarnya.

## Format pencatatan hasil uji

```text
Tanggal:
Commit yang diuji:
ID kasus:
Prasyarat/data dummy:
Langkah:
Hasil yang diharapkan:
Hasil aktual:
Status: Lulus / Gagal / Terhambat
Bukti:
Tindak lanjut:
```
