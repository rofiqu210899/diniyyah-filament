# Agent Guide & Project Architecture: Diniyyah Filament

Dokumen ini berisi panduan alur proyek, aturan bisnis, dan referensi arsitektur sistem untuk mempermudah AI Agent memahami dan melanjutkan pengembangan codebase ini.

---

## 🏫 Gambaran Umum Sistem
Sistem ini mengelola data akademik Madrasah Diniyyah, progres hafalan santri (Wajib vs Sunnah), pendataan wali kelas (Mustahiq) per tahun ajaran, dan laporan rekapitulasi.

---

## 💾 Struktur Database & Relasi Utama

1. **`bukuinduk` (Data Santri)**:
   - Menyimpan biodata santri.
   - Kolom penting: `noin` (NIS), `nm` (Nama), `jk` (Jenis Kelamin: `1` = Laki-laki/Putra, `2` = Perempuan/Putri), serta kelas berjalan `mkls` (1-6), `mbag` (Bagian/Paralel), dan `tkt` (Jenjang FK ke `madin.id`).
2. **`mustahiqs` (Wali Kelas)**:
   - Wali kelas dipetakan **setiap tahun ajaran** dan memiliki jenis kelamin kelas.
   - Kolom penting: `nama_mustahiq`, `mkls`, `mbag`, `tkt`, `jk` (Jenis Kelamin Kelas: `1` = Putra, `2` = Putri), dan `tahun_ajaran_id`.
   - **Unique Constraint**: `['mkls', 'mbag', 'tkt', 'jk', 'tahun_ajaran_id']` (Satu kelas/tingkat/gender hanya boleh memiliki 1 Mustahiq per tahun ajaran).
3. **`data_hafalans` (Daftar Hafalan)**:
   - Target hafalan yang dipetakan per kelas (`mkls`) dan jenjang (`tkt`).
   - Memiliki `kriteria` (`Wajib` atau `Sunnah`).
4. **`hafalan_santris` (Pencapaian Hafalan)**:
   - Mencatat hafalan yang sudah diselesaikan santri per tahun ajaran.
   - Menyimpan snapshot historis kelas (`mkls`, `tkt`, `kls`, `unit`) saat santri tersebut menyelesaikan setoran.

---

## ⚙️ Aturan Bisnis Penting (Business Rules)

### 1. Sinkronisasi Jenis Kelamin Mustahiq & Santri
Di halaman **Input Hafalan**, saat santri dicari:
- Sistem mengambil Jenis Kelamin (`jk`) dari data `bukuinduk` santri.
- Mustahiq yang ditampilkan disaring berdasarkan kesamaan kelas (`mkls`, `mbag`, `tkt`) **DAN** kecocokan Jenis Kelamin (`jk` kelas Mustahiq == `jk` santri) **DAN** tahun ajaran yang aktif saat ini.
- Kelas Putra hanya didampingi Mustahiq kelas Putra, begitupun Kelas Putri.

### 2. Validasi Setoran Hafalan Sunnah
- Santri **tidak boleh** menyetor hafalan berkriteria `Sunnah` di suatu kelas sebelum **semua** hafalan berkriteria `Wajib` pada kelas tersebut ditandai tuntas.

### 3. Logika Penguncian Kelas Historis (Class Locking)
Di halaman **Input Hafalan**:
- Jika santri **sudah memiliki** setoran hafalan pada suatu tahun ajaran (baik aktif maupun lama), tingkatan kelasnya di halaman input **dikunci** mengikuti snapshot kelas pada data setoran pertama di tahun ajaran tersebut.
- Hal ini mencegah tumpang tindih data jika data kelas santri di `bukuinduk` dirubah/naik kelas sebelum tahun ajaran baru diaktifkan di sistem.

### 4. Auto-Deaktivasi Periode Tahun Ajaran
- Ketika pengguna melakukan login ke sistem, event listener `App\Listeners\CheckTahunAjaranOnLogin` akan otomatis dipicu.
- Listener akan mengecek apakah tanggal hari ini sudah melewati **30 Juni** dari tahun akhir periode aktif (misal periode aktif `2026-2027` berakhir pada 30 Juni 2027). Jika ya, tahun ajaran aktif tersebut otomatis diset non-aktif (`is_aktif` = `false`).

---

## 📊 Arsitektur Rekapitulasi Excel

Fitur download laporan di halaman custom **Rekapitulasi** menggunakan library **Laravel Excel**:
- **Multi-Sheet**: Dibagi per tingkatan jenjang (ULA, Wustho, Ulya).
- **Tabel Mandiri per Tingkat Kelas**: Setiap tingkat kelas (Kelas 1, Kelas 2, dst.) ditampilkan dalam tabel terpisah secara vertikal ke bawah, masing-masing dengan header kolom sendiri.
- **Eager Loading & In-Memory**: Semua data santri dan pencapaian hafalan di-load sekaligus di awal (menghindari N+1 query database). Seluruh kalkulasi statistik (rata-rata, tuntas wajib, tuntas sunnah, prosentase) diproses di memory RAM PHP agar performa ekspor instan.

---

## 💡 Panduan Pengembangan di Hari Lain
- **Penambahan Fitur**: Jika menambahkan target hafalan atau memodifikasi alur kelas, pastikan selalu menyertakan kolom `jk` (1/2) dan `tahun_ajaran_id` jika data tersebut sensitif terhadap perbedaan gender kelas dan periode tahunan.
- **Zona Waktu**: Sistem menggunakan zona waktu `Asia/Jakarta` (WIB) dengan locale `id` (Bahasa Indonesia). Pastikan query berbasis tanggal menggunakan Carbon dengan penyesuaian zona waktu ini.
