# Changelog SIMS SaaS

Semua perubahan penting pada proyek ini dicatat di sini, dalam Bahasa Indonesia.
Format mengikuti [Keep a Changelog](https://keepachangelog.com/id/1.1.0/).

## [Unreleased]

### Ditambahkan
- Autentikasi: login WA OTP (rate limit Dok 09 §3.1), MFA TOTP wajib untuk peran platform (FR-904), tenant switcher, API v1 auth
- RBAC: 54 permission (`{resource}.{aksi}`) + 9 peran tenant sesuai matriks Dok 05 via Spatie teams; middleware `module` → 403 `MODULE_DISABLED` untuk add-on nonaktif
- Kesiswaan: CRUD siswa + soft delete, NIS unik per sekolah, profil 360° (wali, riwayat kelas, kehadiran, nilai), UI Livewire + API v1
- Kepegawaian: CRUD guru/pegawai (NIP/NUPTK, jenis, status kepegawaian), UI + API v1
- Kelas: rombel + wali kelas (FR-303), penempatan siswa dengan riwayat pindah (FR-102, partial unique index)
- Akademik: mata pelajaran + KKM, jadwal mingguan dengan deteksi bentrok guru & ruang (FR-502), absensi harian H/I/S/A + kunci (FR-604) + rekap
- Nilai: semester, komponen berbobot, input nilai (validasi 0–100), nilai akhir berbobot, leger (FR-706), rapor (FR-705), rilis nilai ke PWA (FR-707)
- PPDB Online (add-on): periode, formulir pendaftar, verifikasi berkas, seleksi (tes + wawancara), pengumuman kelulusan, daftar ulang → otomatis menjadi data siswa; di belakang feature flag `module:ppdb` → 403 `MODULE_DISABLED`
- Keuangan SPP (add-on): jenis tagihan, generate tagihan massal per periode, pembayaran bertahap + kuitansi otomatis, status lunas/sebagian/jatuh tempo, ringkasan arus kas & tunggakan; di belakang feature flag `module:keuangan`
- `PANDUAN-PENGGUNA.md`: panduan lengkap berbahasa Indonesia untuk pengguna akhir (login WA OTP → semua modul)
- Pusat Bantuan in-app (`/bantuan`): panduan end-user di-render dari `docs/bantuan/*.md` menjadi HTML dengan layout aplikasi (tanpa toolchain Node/Docusaurus); tombol "?" kontekstual di halaman modul
- Pengumuman (add-on): pengumuman tersegmentasi (semua/peran/kelas), draf → tayang, tanda penting, periode tayang; di belakang feature flag `module:pengumuman`; API v1 untuk PWA
- Laporan: 6 laporan bawaan (siswa, guru, kelas, mutasi, absensi, nilai) + filter + ekspor CSV berkode UTF-8 (FR-1001/1002); UI Livewire + API v1
- Pengaturan: profil sekolah (nama, NPSN, alamat, kontak), tahun ajaran (tambah + aktifkan), toggle modul add-on per sekolah; UI tab + API v1
- E-Rapor (add-on): rapor cetak berkop sekolah (nilai + predikat + rata-rata + rekap absensi + catatan wali kelas + tanda tangan), catatan wali per siswa/semester; di belakang feature flag `module:e_rapor`
- Tata Tertib (add-on): master jenis pelanggaran (ringan/sedang/berat + bobot poin), pencatatan pelanggaran per siswa, rekap poin tertinggi; di belakang feature flag `module:tata_tertib`
- BK & Konseling (add-on): penjadwalan sesi konseling (individu/kelompok/klasikal), catatan rahasia + tindak lanjut, status terjadwal/selesai/batal; di belakang feature flag `module:bk_konseling`
- Perpustakaan Digital (add-on): katalog buku + stok tersedia, peminjaman dengan batas stok, pengembalian otomatis hitung denda keterlambatan; di belakang feature flag `module:perpustakaan`
- Ekstrakurikuler & Prestasi (add-on): daftar ekskul + kelola anggota, pencatatan prestasi berjenjang (sekolah → internasional); di belakang feature flag `module:ekstrakurikuler`
- LMS Mini / Tugas Online (add-on): pembuatan tugas per mapel/kelas, pengumpulan jawaban, penilaian + feedback; di belakang feature flag `module:lms_mini`

### Keamanan
- Isolasi tenant fail-closed: query model tenant tanpa konteks melempar exception (diuji)
- Identitas global + membership (ADR-0004); token satu tenant aktif
- NIK wali terenkripsi di database

### Diperbaiki
- Unique constraint `class_student`: diubah menjadi partial unique index (`status='aktif'`) agar riwayat pindah kelas tersimpan — Dok 02 §3.11 diperbarui

## [0.1.0] - 2026-10-10
### Ditambahkan
- Scaffolding Laravel 13 + PHP 8.3 + Pest 4 + Livewire + Sanctum + Spatie Permission (teams)
- Fondasi multi-tenant: `TenantContext`, `SchoolScope`, `BelongsToSchool`, `ResolveTenant`
- Migrasi awal 000001–000027 (identitas, sekolah, membership, audit, OTP, MFA)
- Layout Blade + Tailwind v4 mengikuti design system
- CI: Pint + Larastan level 6
