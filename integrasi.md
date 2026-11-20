# Audit skema dan kontrak integrasi

Audit awal: 29 Oktober 2026, berdasarkan snapshot repo main
`03173812ec8d54584f26e976c3b151b00701d887` dan dokumen kelompok
`STANDAR_DATABASE.md` yang diberikan saat diskusi. Snapshot perlu diperbarui
bila perubahan kelompok sudah masuk ke main.

## Tabel milik Modul Laporan Masyarakat

| Tabel/model | Primary key | Catatan kompatibilitas |
|---|---|---|
| `reports` / `Report` | `report_id` | Memakai timestamps; `report_number` unik; `period_id` wajib |
| `issue_categories` / `IssueCategory` | `issue_category_id` | Tanpa timestamps; mempunyai `is_active` |
| `report_evidences` / `ReportEvidence` | `evidence_id` | Tanpa timestamps; memakai `uploaded_at` |

Model sudah tersedia. Penambahan fitur menggunakan model tersebut dan
mempertahankan konfigurasi tabel, primary key, timestamps, serta relasi.
Perubahan skema dibuat sebagai migration baru pada tabel milik modul ini.

## Field penting laporan

| Field | Asal/aturan yang diperlukan |
|---|---|
| `report_number` | Dibuat server dan dijaga unik. Format usulan kelompok: `LPR-YYYYMMDD-NNNN`; belum menjadi keputusan final |
| `user_id` | Identitas pengguna yang sudah diautentikasi; mengacu ke `users.id` |
| `facility_id` | Fasilitas valid dari modul Direktori Fasilitas |
| `issue_category_id` | Kategori kendala aktif saat pengajuan baru |
| `status_id` | Relasi ke `verification_statuses.status_id`; status awal Menunggu Verifikasi |
| `period_id` | Wajib terisi dengan periode valid dari modul Data Pendukung |
| `description` | Deskripsi awal; informasi tambahan tidak menimpanya |

Relasi yang tersedia antara lain `user()`, `facility()`, `issueCategory()`,
`status()`, `period()`, `evidences()`, `verifications()`, dan
`followUpAssignments()`. Gunakan `issueCategory()` untuk kategori laporan.

## Ketergantungan dan pemilik

| Kebutuhan | Pemilik terkait | Yang perlu tersedia |
|---|---|---|
| Login, pengguna, role | Rajendra / penanggung jawab autentikasi tim | Route login, middleware, identitas sesi, serta akun dummy sesuai role |
| Pilihan fasilitas dan lokasi | Elyasa | Data fasilitas valid serta kontrak tampilan pilihan fasilitas |
| Periode pelaporan | Rajendra | Data periode, aturan penentuan periode aktif, dan penanganan periode kosong |
| Status dan permintaan informasi | Siti | Status Dibatalkan dan Memerlukan Pemeriksaan Lanjutan, serta transisi yang disepakati |
| Histori verifikasi | Siti | Relasi dan data yang dapat dibaca untuk timeline; aturan informasi tambahan |
| Progres tindak lanjut | Fachri | Data progres yang dapat dibaca dan batas informasi yang ditampilkan ke pelapor |
| Pengaturan/statistik kategori | Rajendra | Memakai kategori dari modul Riyana agar CRUD tidak dibuat dua kali |

Data master modul lain tetap diisi oleh pemiliknya. Tes dapat menggunakan
fixture fiktif, tetapi fitur runtime tidak membuat fasilitas, periode, atau
role pengganti secara diam-diam. Kebutuhan integrasi disampaikan di kanal
kelompok oleh pengguna; audit ini tidak mengirim pesan kepada siapa pun.

## Temuan nyata snapshot awal

1. Route web baru memuat halaman welcome. Alur login belum terlihat pada
   snapshot ini. Modul laporan harus terhubung ke autentikasi tim sebelum
   fitur data pribadi dinyatakan selesai.
2. `MasterDataSeeder` snapshot memuat lima status: Menunggu Verifikasi,
   Terverifikasi, Ditolak, Dalam Tindak Lanjut, dan Selesai. File SQL lampiran
   memuat dua tambahan: Memerlukan Pemeriksaan Lanjutan dan Dibatalkan.
   Tambahan status harus masuk ke seeder/migration resmi melalui pemiliknya.
3. Seeder yang diperiksa belum menyediakan data fasilitas dan periode
   pelaporan. Form tidak boleh mengasumsikan `facility_id = 1` atau
   `period_id = 1` selalu tersedia.
4. Tabel `reports` belum menyediakan tempat khusus untuk riwayat informasi
   tambahan. Rancang penyimpanannya sebelum implementasi; jangan memakai
   pembaruan `description` untuk mengganti isi awal.
5. SQL lampiran bukan pengganti riwayat migration. Database kerja `aksesibel`
   mengikuti migration dan seeder repo; SQL lampiran hanya untuk penggunaan
   terpisah sesuai petunjuk di dalam file tersebut.

## Keputusan yang masih menunggu kesepakatan

Bagian bertanda usulan di standar kelompok tetap diperlakukan sebagai usulan.
Tabel berikut merupakan daftar keputusan yang diperlukan, bukan bukti persetujuan.

| Topik | Rancangan awal untuk dibahas | Status awal |
|---|---|---|
| Format nomor | `LPR-YYYYMMDD-NNNN`; definisikan batas urutan dan penanganan pengajuan bersamaan | Menunggu keputusan |
| Perubahan status | Satu method/service domain milik `Report` yang digunakan lintas modul; otorisasi tetap diperiksa pada alur pemanggil | Menunggu keputusan |
| Hak kelola kategori | Usulan Admin; Masyarakat hanya memilih kategori saat melapor | Menunggu keputusan |
| Edit kategori historis | Kategori nonaktif tetap terlihat pada laporan lama; aturan edit laporan lama perlu disepakati | Menunggu keputusan |
| Informasi tambahan | Usulan tabel baru `report_supplements` milik modul laporan, dengan aktor/waktu dan kaitan permintaan verifikator; relasi bukti dirancang bersama | Menunggu keputusan |
| Akses bukti | Pemilik dan petugas terkait sesuai peran/penugasan; storage privat melalui endpoint berotorisasi | Menunggu keputusan |
| Batas lampiran | Pilih format foto/video, ukuran tiap file, jumlah file, serta batas PHP demo; catat nilai final sebelum hari 18 | Menunggu keputusan |
| Kategori duplikat | Tentukan normalisasi spasi/huruf, validasi, dan kebutuhan indeks melalui migration baru | Menunggu keputusan |

## Urutan integrasi

1. Konfirmasi autentikasi, sumber fasilitas, dan aturan periode.
2. Konfirmasi status untuk batal dan permintaan informasi dengan pemiliknya.
3. Implementasikan pengajuan, daftar, detail, edit, dan batal dengan otorisasi.
4. Hubungkan kategori aktif dan penyimpanan bukti privat.
5. Selesaikan rancangan informasi tambahan dan kepemilikan tabel baru bila ada.
6. Baca histori/progres dari relasi modul lain sesuai kontrak, lalu uji alur bersama.

Perubahan struktur tabel modul lain dilakukan oleh pemilik tabelnya. Jika ada
kebutuhan baru, dokumentasikan terlebih dahulu dan ajukan lewat alur kelompok.
