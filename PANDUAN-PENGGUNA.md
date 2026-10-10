# Panduan Penggunaan SIMS

Sistem Informasi Manajemen Sekolah — panduan lengkap untuk admin sekolah, guru, dan staf.
Ditulis dalam Bahasa Indonesia, langkah demi langkah.

---

## Daftar Isi

1. [Masuk ke Aplikasi](#1-masuk-ke-aplikasi)
2. [Berpindah Sekolah (bila Anda terdaftar di lebih dari satu)](#2-berpindah-sekolah)
3. [Mengamankan Akun dengan Verifikasi 2 Langkah](#3-mengamankan-akun-dengan-verifikasi-2-langkah)
4. [Dashboard](#4-dashboard)
5. [Data Siswa](#5-data-siswa)
6. [Data Guru & Pegawai](#6-data-guru--pegawai)
7. [Data Kelas](#7-data-kelas)
8. [Jadwal Pelajaran](#8-jadwal-pelajaran)
9. [Absensi Siswa](#9-absensi-siswa)
10. [Data Nilai](#10-data-nilai)
11. [PPDB Online](#11-ppdb-online)
12. [Keuangan SPP](#12-keuangan-spp)
13. [Peran & Hak Akses](#13-peran--hak-akses)
14. [Pertanyaan Umum](#14-pertanyaan-umum)

---

## 1. Masuk ke Aplikasi

SIMS memakai **login via WhatsApp** — tanpa password yang perlu dihafal.

1. Buka alamat aplikasi sekolah Anda (mis. `https://sma-anda.sims.id`), lalu klik **Masuk**.
2. Masukkan **nomor WhatsApp** Anda (format: 62812xxxxxxx), klik **Kirim Kode**.
3. Anda menerima kode 6 digit via WhatsApp. Masukkan kode tersebut dalam **5 menit**.
4. Jika kode salah 5 kali, akun dikunci sementara — tunggu dan minta kode baru (ada jeda 60 detik antar permintaan).

> **Tips:** Pastikan nomor WhatsApp yang dipakai sama dengan yang terdaftar di sekolah. Jika belum terdaftar, hubungi admin sekolah.

Jika akun Anda **wajib verifikasi 2 langkah** (peran operator), setelah kode WhatsApp Anda diminta memasukkan kode dari aplikasi autentikator (Google Authenticator / Authy). Lihat [bagian 3](#3-mengamankan-akun-dengan-verifikasi-2-langkah).

---

## 2. Berpindah Sekolah

Jika Anda terdaftar di lebih dari satu sekolah (mis. guru di dua sekolah):

1. Setelah masuk, Anda diarahkan ke halaman **Pilih Sekolah**.
2. Klik sekolah yang ingin dikelola.
3. Untuk berpindah di tengah sesi, klik nama sekolah di pojok kanan atas → **Ganti Sekolah**.

Data yang Anda lihat selalu milik **satu sekolah aktif** — tidak akan tercampur.

---

## 3. Mengamankan Akun dengan Verifikasi 2 Langkah

1. Buka **Pengaturan → Keamanan → Aktifkan 2FA**.
2. Pindai kode QR dengan aplikasi autentikator di HP Anda.
3. Masukkan kode 6 digit dari aplikasi untuk konfirmasi.
4. **Simpan 8 kode cadangan** di tempat aman (dicetak / password manager). Kode ini dipakai jika HP hilang — tiap kode hanya bisa dipakai sekali.

---

## 4. Dashboard

Halaman utama menampilkan ringkasan: jumlah siswa, guru, kelas, dan kehadiran hari ini. Menu di sisi kiri menyesuaikan **peran** Anda — menu yang tidak menjadi hak Anda tidak akan muncul.

---

## 5. Data Siswa

Menu **Data Siswa** (ikon orang).

### Melihat & mencari siswa
- Ketik nama atau NIS di kolom pencarian.
- Saring berdasarkan status (Aktif / Lulus / Pindah / Keluar).

### Menambah siswa baru
1. Klik **Tambah Siswa**.
2. Isi **NIS** (wajib, unik di sekolah Anda), **nama lengkap** (wajib), dan jenis kelamin.
3. Lengkapi data lain bila ada: NISN, tanggal lahir, alamat, status.
4. Klik **Simpan**.

> NIS boleh sama dengan NIS sekolah lain — yang penting unik di sekolah Anda sendiri.

### Mengubah & menonaktifkan
- Klik ikon pensil untuk mengubah data.
- Klik ikon tempat sampah untuk menonaktifkan (data tidak hilang permanen — masuk arsip).

### Profil 360°
Klik nama siswa untuk melihat profil lengkap: biodata, orang tua/wali, riwayat kelas, rekap kehadiran, dan nilai.

---

## 6. Data Guru & Pegawai

Menu **Data Guru** (ikon toga).

- **Saring** berdasarkan jenis: Guru Mapel, Guru Kelas, atau Tendik.
- **Tambah**: isi nama (wajib), NIP/NUPTK, jenis, status kepegawaian (PNS/PPPK/Honorer/Kontrak), dan jabatan.
- Nonaktifkan pegawai yang sudah tidak aktif via tombol arsip.

---

## 7. Data Kelas

Menu **Data Kelas** (ikon sekolah).

### Membuat kelas (rombel)
1. Klik **Tambah Kelas**.
2. Pilih tahun ajaran, isi tingkat (mis. `7`) dan nama kelas (mis. `VII A`).
3. Tentukan kapasitas dan wali kelas (opsional).
4. Klik **Simpan**. Nama kelas tidak boleh kembar dalam satu tahun ajaran.

### Menempatkan siswa ke kelas
1. Buka **Lihat Siswa** pada kartu kelas.
2. Di bagian **Tempatkan Siswa**, cari nama siswa lalu klik **Tempatkan**.
3. Jika siswa pindah kelas di tengah tahun, tempatkan ke kelas baru — riwayat kelas lamanya **tetap tersimpan**.
4. Untuk mengeluarkan siswa dari kelas, klik ikon **kurang** di samping namanya.

---

## 8. Jadwal Pelajaran

Menu **Jadwal Pelajaran** (ikon kalender).

1. Pilih kelas untuk melihat **grid jadwal mingguan** (Senin–Sabtu × jam ke-1 dst).
2. Klik **Tambah Jadwal**: pilih hari, jam ke, mata pelajaran, guru, dan ruang (opsional).
3. **Sistem menolak otomatis** bila:
   - guru yang sama dijadwalkan mengajar dua kelas di jam yang sama, atau
   - ruangan yang sama dipakai dua kelas di jam yang sama.
4. Untuk menghapus satu slot jadwal, klik **Hapus** pada kartunya.

---

## 9. Absensi Siswa

Menu **Absensi Siswa** (ikon papan klip).

1. Pilih **kelas** dan **tanggal**.
2. Tandai tiap siswa: **H** (Hadir), **I** (Izin), **S** (Sakit), **A** (Alpa). Isi keterangan bila perlu (mis. "izin dokter").
3. Pakai tombol **Semua Hadir / Izin / Sakit / Alpa** untuk menandai sekaligus.
4. Klik **Simpan Absensi**.
5. Jika absensi sudah final, klik **Kunci** — data yang terkunci **tidak bisa diubah lagi**.

> Rekap kehadiran per siswa (jumlah H/I/S/A dalam rentang tanggal) tersedia untuk wali kelas via laporan.

---

## 10. Data Nilai

Menu **Data Nilai** (ikon piala). Alurnya tiga langkah: **input → leger → rilis**.

### Input nilai
1. Buka **Input Nilai**, pilih kelas, mata pelajaran, komponen (Tugas/UH/PTS/PAS), dan semester.
2. Isi nilai tiap siswa (0–100). Nilai di luar rentang ditolak.
3. Klik **Simpan Nilai**.

### Leger & rilis
1. Buka **Leger Nilai** untuk melihat **nilai akhir berbobot** tiap siswa (otomatis dihitung dari bobot komponen).
2. Siswa di bawah KKM ditandai merah.
3. Klik **Rilis Nilai** — nilai baru **tampil di aplikasi siswa/orang tua** setelah dirilis. Sebelum dirilis, siswa tidak bisa melihat nilainya.

---

## 11. PPDB Online

Menu **PPDB** (ikon tambah orang). Alur: **daftar → verifikasi → seleksi → daftar ulang**.

1. **Buka periode**: buat Gelombang (nama, tanggal buka/tutup, kuota).
2. **Daftarkan** calon via formulir (nama, NISN, asal sekolah, kontak orang tua). Nomor pendaftaran dibuat otomatis (`PPDB/2026/0001`).
3. **Kirim formulir** (draft → terkirim), lalu **verifikasi berkas** (terkirim → terverifikasi).
4. **Seleksi**: isi nilai tes & wawancara → nilai akhir dihitung otomatis. Klik **Terima** / **Tolak**.
5. **Daftar ulang**: pendaftar yang diterima otomatis menjadi **data siswa** (satu klik, NIS dibuat otomatis).

> Status tidak bisa dilompati — mis. pendaftar yang belum diverifikasi tidak bisa langsung diterima. Sistem menolaknya.

---

## 12. Keuangan SPP

Menu **Keuangan SPP** (ikon dompet). Alur: **buat jenis tagihan → generate → catat pembayaran**.

### Jenis tagihan
Buat dulu jenisnya (mis. SPP bulanan Rp 500.000, Uang Gedung sekali bayar) via API atau minta admin.

### Generate tagihan massal
1. Di halaman Keuangan, pilih **jenis tagihan** dan isi **periode** (mis. `2026-07`).
2. Klik **Generate** — tagihan dibuat otomatis untuk **semua siswa aktif**. Tagihan yang sudah ada tidak diduplikat.

### Mencatat pembayaran
1. Cari tagihan siswa, klik **Bayar**.
2. Isi nominal (tidak boleh melebihi sisa), tanggal, dan metode (Tunai/Transfer/QRIS/VA/E-Wallet).
3. Klik **Simpan Pembayaran** — **nomor kuitansi dibuat otomatis** (`KWT/2026/00001`).
4. Status tagihan berubah otomatis: Belum Bayar → Sebagian → Lunas.

### Memantau tunggakan
Kartu ringkasan di atas halaman menampilkan total tagihan, terkumpul, tunggakan (Rp), dan jumlah tagihan belum lunas. Saring status **Jatuh Tempo** untuk daftar penagihan.

---

## 13. Peran & Hak Akses

| Peran | Bisa melakukan |
|---|---|
| **Admin Sekolah** | Semua: kelola siswa, guru, kelas, jadwal, absensi, nilai, PPDB, keuangan, pengguna, pengaturan |
| **Kepala Sekolah** | Melihat semua + rilis nilai + setujui PPDB |
| **Tata Usaha** | Input siswa & pendaftar PPDB, verifikasi berkas, lihat jadwal/absensi/nilai |
| **Wali Kelas** | Absensi + kunci absensi kelas binaannya, rilis nilai, lihat siswa binaannya |
| **Guru Mapel** | Lihat jadwal, input absensi & nilai untuk mapel ampuannya |
| **Guru BK** | Lihat data siswa & nilai |
| **Siswa / Orang Tua** | (Aplikasi terpisah) Lihat nilai & absensi **yang sudah dirilis** |

Jika ada menu yang tidak muncul, berarti peran Anda tidak memiliki hak untuk itu — hubungi admin sekolah.

---

## 14. Pertanyaan Umum

**Saya lupa tidak menerima kode WhatsApp.**
Tunggu 60 detik lalu klik kirim ulang. Maksimal 3 kali per 15 menit. Pastikan nomor terdaftar & WhatsApp aktif.

**Data saya tidak muncul / kosong.**
Periksa: (1) apakah sekolah yang aktif sudah benar (pojok kanan atas), (2) apakah filter pencarian/status sedang aktif.

**Saya salah input nilai/absensi.**
Selama belum dikunci, ubah dan simpan ulang. Setelah dikunci, hubungi admin sekolah.

**Apakah data sekolah kami tercampur dengan sekolah lain?**
Tidak. Setiap sekolah adalah tenant terpisah — data, NIS, dan pengguna terisolasi penuh.

**Bagaimana cara menambah pengguna baru (guru/TU)?**
Hubungi admin sekolah. Admin membuat akun via menu Pengguna dan menetapkan peran.

---

*Dokumen ini untuk pengguna akhir. Dokumentasi teknis (instalasi, API, arsitektur) ada di `README.md` dan wiki.*
