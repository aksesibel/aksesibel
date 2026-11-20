# Jurnal pengerjaan Modul Laporan Masyarakat

Jurnal diisi pada tanggal pengerjaan sebenarnya. Rencana masa depan berada
di kalender, sedangkan bagian ini hanya berisi hasil yang sudah dikerjakan.
Ringkasan berikut belum menyatakan fitur aplikasi sudah selesai.

## 29 Oktober 2026 — analisis kebutuhan dan persiapan kontribusi

**Lingkup:** PBI-07, PBI-08, dan PBI-09. **Hasil:** dokumentasi hari pertama.

### Pekerjaan aktual

- Memeriksa snapshot main `03173812ec8d54584f26e976c3b151b00701d887`.
- Memetakan tabel `reports`, `issue_categories`, dan `report_evidences`
  beserta primary key, timestamps, dan relasi model.
- Membandingkan kebutuhan modul dengan standar database kelompok dan SQL
  lampiran, terutama perbedaan status dan kebutuhan informasi tambahan.
- Menyusun 32 target tanggal sampai asumsi tenggat 29 November 2026.
- Menulis kriteria penerimaan serta daftar pemeriksaan per PBI.
- Mencatat kebutuhan login, fasilitas, periode, status, dan histori dari tim.
- Menyusun cara commit/push melalui GitHub Desktop pada `Riyana_branch`.

### Berkas hasil

- `docs/laporan-masyarakat/README.md`
- `docs/laporan-masyarakat/rencana-harian.md`
- `docs/laporan-masyarakat/kriteria-penerimaan.md`
- `docs/laporan-masyarakat/integrasi.md`
- `docs/laporan-masyarakat/jurnal-harian.md`

### Validasi

Pemeriksaan dokumentasi selesai: lima berkas tersedia; 32 tanggal berurutan
dari 29 Oktober sampai 29 November 2026; seluruh tautan relatif valid;
26 ID kasus penerimaan unik; dan `git diff --cached --check` lulus tanpa
masalah whitespace. Pemeriksaan kalender dan tautan dilakukan dengan skrip
Python sementara, tanpa menambahkan tes aplikasi untuk perubahan dokumentasi.

Tes fitur Laravel belum dijalankan pada hari ini karena hasil commit hanya
dokumentasi. Seluruh kasus penerimaan berstatus Belum diuji. Setup Windows
dan aplikasi dilaporkan sudah berhasil oleh pengguna; audit ini tidak
menjalankan ulang aplikasi di laptop pengguna.

### Temuan dan tindak lanjut

- Dua status tambahan pada SQL lampiran belum berada di seeder snapshot.
  Tindak lanjut: koordinasikan dengan pemilik tabel status.
- Data fasilitas dan periode dibutuhkan untuk form.
  Tindak lanjut: gunakan data contoh yang disediakan pemilik modul.
- Autentikasi belum terlihat pada route snapshot.
  Tindak lanjut: konfirmasi kontrak login dan role dengan tim.
- Penyimpanan informasi tambahan belum tersedia.
  Tindak lanjut: sepakati rancangan yang mempertahankan deskripsi awal.

**Pesan commit hari pertama:**

```text
docs(laporan): petakan PBI dan rencana pengerjaan harian
```

**Target berikutnya:** layout Blade dan navigasi modul, sambil menuntaskan
kontrak autentikasi serta sumber data yang diperlukan.

## Template entri hari berikutnya

Salin template ini pada tanggal pengerjaan. Ringkas entri agar mudah diperiksa.
Kolom kosong adalah template, bukan hasil pengujian.

```text
Tanggal:
PBI / target:
Perubahan yang selesai:
Berkas yang berubah:
Pemeriksaan / perintah yang dijalankan:
Hasil aktual:
ID kasus penerimaan terkait:
Bukti tampilan / hasil uji:
Temuan / hambatan:
Tindak lanjut:
Pesan commit:
SHA / tautan commit sebelumnya jika perlu:
PR / status review:
Target berikutnya:
```
