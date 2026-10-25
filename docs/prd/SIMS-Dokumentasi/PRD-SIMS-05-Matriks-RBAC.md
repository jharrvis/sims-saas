# Dok 05 — Matriks RBAC & Permission

| Atribut | Keterangan |
|---|---|
| Dokumen | Dok 05 — Matriks RBAC & Permission |
| Merujuk | PRD SIMS Multi-Sekolah v1.0 (§5 Pengguna & Peran, §7 Kebutuhan Fungsional) |
| Versi Dokumen | 1.1 |
| Tanggal | 9 Oktober 2026 |
| Catatan revisi | v1.1 — pivot SaaS: tiap sekolah = tenant pelanggan; modul add-on per tenant; peran & permission level platform terpisah (guard `platform`) |
| Teknologi | Laravel 13 + Spatie Laravel Permission |

> Dokumen ini adalah turunan resmi PRD. Setiap aksi penting di aplikasi **wajib** dicek terhadap permission di dokumen ini (via middleware/policy), dan setiap perubahan matriks mengikuti prosedur di §7.

---

## 1. Daftar Peran

### 1.1 Peran level tenant (guard `web` / `sanctum`)

| # | Peran | Deskripsi Singkat | Lingkup Data |
|---|---|---|---|
| 1 | **Superadmin** | Pengelola pusat/yayasan; satu-satunya peran lintas sekolah | Semua sekolah |
| 2 | **Admin Sekolah** | Operator utama tiap sekolah; mengelola seluruh data operasional | 1 sekolah |
| 3 | **Kepala Sekolah** | Pimpinan sekolah; fokus monitoring, laporan, dan persetujuan | 1 sekolah |
| 4 | **Tata Usaha (TU)** | Staf administrasi; mengelola data master siswa, wali, guru, kelas, tahun ajaran | 1 sekolah |
| 5 | **Wali Kelas** | Guru yang membina satu rombel; absensi, nilai (rilis), dan pemantauan kelas binaannya | Rombel binaannya |
| 6 | **Guru Mapel** | Guru mata pelajaran; jadwal mengajar, absensi per mapel, input nilai | Kelas & mapel ampuannya |
| 7 | **Guru BK** | Konselor sekolah; pemantauan siswa lintas kelas untuk keperluan bimbingan | 1 sekolah |
| 8 | **Siswa** | Peserta didik; akses baca data diri sendiri via PWA | Datanya sendiri |
| 9 | **Orang Tua/Wali** | Wali murid; memantau anaknya via PWA (bisa lintas sekolah dalam jejaring) | Anak yang terelasi |

**Catatan:** peran **Bendahara** (PRD §5) belum masuk matriks ini karena modul Keuangan/SPP dijadwalkan Fase 2. Saat modul keuangan dibangun, peran tersebut ditambahkan via prosedur §7 dengan permission `keuangan.*`.

**Catatan pivot SaaS:** pada model SaaS multi-tenant publik, fungsi lintas sekolah milik Superadmin dipecah: (1) provisioning tenant & operasional SaaS → peran level platform (§1.2); (2) pengelolaan & konsolidasi sekolah-sekolah dalam satu grup → **Admin Organisasi** (hanya untuk tenant yang tergabung ke organisasi). Kolom SA pada matriks §4 dipertahankan sebagai peran kompatibilitas untuk konteks yayasan tunggal.

Satu user dapat memegang **lebih dari satu peran dalam level yang sama** (mis. guru mapel + wali kelas); permission efektif = gabungan (union) seluruh perannya. **Satu akun dilarang memegang peran level platform dan level tenant sekaligus** — pengecualian hanya via impersonasi tercatat (lihat Aturan main dua level di §5).

### 1.2 Peran level platform (guard `platform`)

Peran ini dipegang oleh tim operator SaaS (bukan staf sekolah) dan login melalui panel platform yang terpisah. **2FA wajib** untuk keempat peran ini.

| # | Peran | Deskripsi Singkat | Lingkup |
|---|---|---|---|
| P1 | **Platform Admin** | Operator penuh SaaS: provisioning & suspensi tenant, persetujuan add-on, toggle modul, webhook, metrik | Seluruh platform; **dilarang** mengubah data akademik tenant kecuali via impersonasi tercatat |
| P2 | **Platform Support / CS** | Dukungan pelanggan: melihat tenant & impersonasi terbatas tercatat untuk troubleshooting; **tanpa** mengubah billing | Tenant yang ditangani; read-only untuk data billing |
| P3 | **Platform Finance** | Mengelola paket, langganan, invoice, dan refund | Data billing seluruh tenant; tanpa akses data akademik |
| P4 | **Admin Organisasi** | Mengelola grup/organisasi: keanggotaan tenant, pengajuan add-on, tagihan, dan laporan konsolidasi untuk sekolah-sekolah organisasinya | **Hanya** tenant dalam organisasinya |

> Admin Organisasi adalah peran lintas-tenant yang terikat ke satu organisasi (mis. yayasan yang mendaftarkan beberapa sekolah). Tenant yang berdiri sendiri (tidak tergabung ke organisasi) tidak memiliki Admin Organisasi.

---

## 2. Konvensi Penamaan Permission

Format: **`{resource}.{aksi}`** — huruf kecil, resource tunggal (tanpa spasi), aksi dari daftar baku berikut:

| Aksi | Arti |
|---|---|
| `view` | Melihat daftar/detail data |
| `create` | Menambah data baru |
| `update` | Mengubah data yang ada |
| `delete` | Menghapus/menonaktifkan data (soft delete bila berlaku) |
| `import` | Import massal (Excel/CSV) |
| `export` | Export/cetak (Excel/PDF) |
| `approve` | Menyetujui aksi yang butuh persetujuan |
| `lock` / `unlock` | Mengunci / membuka kunci periode data |
| `release` | Merilis data agar tampil di PWA |
| `input` | Menginput data transaksional (absensi, nilai) |
| `rekap` | Melihat rekap/ringkasan agregat |
| `assign` | Menetapkan relasi/penugasan |
| `manage` | Mengelola konfigurasi/definisi |
| `publish` | Menayangkan ke publik/PWA |
| `broadcast` | Mengirim notifikasi massal tersegmentasi |
| `revoke` | Mencabut akses/sesi |
| `switch` | Berpindah konteks tenant |
| `impersonate` | Login sebagai user lain |
| `promote` | Eksekusi kenaikan kelas massal |
| `link` | Menautkan relasi antar entitas |
| `template` | Mengelola template (notifikasi/laporan) |
| `builder` | Membuat template laporan kustom |
| `konsolidasi` | Laporan lintas sekolah (yayasan) |
| `reset-password` | Mereset password user lain |
| `toggle` | Mengaktifkan/menonaktifkan modul add-on untuk tenant (khusus level platform) |
| `request` | Mengajukan permintaan (aktivasi modul add-on oleh tenant) |
| `suspend` | Menangguhkan tenant — nonaktif sementara, mis. tunggakan pembayaran |

Contoh: `siswa.view`, `nilai.input`, `absensi.lock`, `tahun-ajaran.activate`.

**Aturan baku:**
- Permission `delete` **tidak pernah** diberikan tanpa `update` pada resource yang sama.
- Peran Siswa & Orang Tua **hanya** memiliki permission baca (`view`, `rekap`) di lingkup PWA — tidak ada `create/update/delete/input` apa pun.
- Nama permission bersifat **imutable** setelah go-live (penghapusan/rename via migrasi + prosedur §7).
- **Guards terpisah:** permission level tenant memakai guard `web` (panel staf) / `sanctum` (API PWA); permission level platform memakai guard `platform`. Permission platform **tidak berlaku** di konteks tenant dan sebaliknya — middleware/policy harus menyebut guard yang tepat (`auth:platform`, `permission:...,platform`).
- Nama permission level platform boleh memakai tiga segmen `{domain}.{resource}.{aksi}` (mis. `platform.metrics.view`) agar tidak bentrok dengan resource tenant.

---

## 3. Daftar Permission per Modul

Total **106 permission**: 85 level tenant dalam 18 kelompok resource (§3.1–§3.18) + 21 level platform (§3.19). Kolom FR menautkan ke PRD §7.

### 3.1 Sekolah / Tenant

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `sekolah.view` | Melihat daftar & profil sekolah (sesuai lingkupnya) | PRD §6.2 |
| `sekolah.create` | Menambah sekolah/tenant baru ke aplikasi | PRD §6.2 |
| `sekolah.update` | Mengubah profil & konfigurasi sekolah | PRD §6.2 |
| `sekolah.switch` | Berpindah konteks antar sekolah tanpa login ulang (tenant switcher) | PRD §6.2 |

### 3.2 Tahun Ajaran

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `tahun-ajaran.view` | Melihat daftar tahun ajaran, semester & status aktif | FR-801 |
| `tahun-ajaran.create` | Membuat tahun ajaran/semester baru | FR-801 |
| `tahun-ajaran.update` | Mengubah periode tahun ajaran/semester | FR-801 |
| `tahun-ajaran.activate` | Mengaktifkan/mengganti tahun ajaran aktif (dengan konfirmasi & snapshot) | FR-802 |
| `tahun-ajaran.close` | Menutup tahun ajaran; data menjadi arsip read-only | FR-804 |
| `kalender.manage` | Mengelola kalender akademik: hari libur, jeda semester, pekan ujian | FR-803 |

### 3.3 Siswa

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `siswa.view` | Melihat data siswa & profil 360° (biodata, wali, riwayat kelas, kehadiran, nilai) | FR-101, FR-105 |
| `siswa.create` | Menambah data siswa baru | FR-101 |
| `siswa.update` | Mengubah data siswa | FR-101 |
| `siswa.delete` | Menonaktifkan siswa (pindah/keluar/lulus/DO); histori tetap tersimpan | FR-106 |
| `siswa.import` | Import massal siswa via Excel/CSV dengan laporan baris gagal | FR-103 |
| `siswa.export` | Export data siswa ke Excel/PDF | FR-1001, FR-1002 |

### 3.4 Wali Murid

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `wali-murid.view` | Melihat data wali murid/orang tua | FR-201 |
| `wali-murid.create` | Menambah data wali murid | FR-201 |
| `wali-murid.update` | Mengubah data wali murid | FR-201 |
| `wali-murid.delete` | Menghapus data wali murid | FR-201 |
| `wali-murid.link` | Menautkan/melepas relasi wali ↔ siswa | FR-202 |
| `wali-murid.export` | Export data wali murid | FR-1001 |

### 3.5 Guru & Pegawai

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `guru.view` | Melihat data guru, tendik & pegawai | FR-301 |
| `guru.create` | Menambah data guru/pegawai | FR-301 |
| `guru.update` | Mengubah data guru/pegawai | FR-301 |
| `guru.delete` | Menonaktifkan data guru/pegawai | FR-301 |
| `guru.import` | Import massal guru/pegawai via Excel/CSV | FR-305 |
| `guru.export` | Export data guru/pegawai | FR-1001 |
| `guru.assign` | Mengatur penugasan mengajar (guru↔mapel↔kelas) & penetapan wali kelas | FR-302, FR-303 |

### 3.6 Kelas (Rombel)

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `kelas.view` | Melihat daftar rombongan belajar | FR-401 |
| `kelas.create` | Membuat rombel baru | FR-401 |
| `kelas.update` | Mengubah rombel (nama, kapasitas, wali kelas) | FR-401 |
| `kelas.delete` | Menghapus rombel | FR-401 |
| `kelas.promote` | Eksekusi kenaikan kelas massal dengan pratinjau | FR-404 |

### 3.7 Mata Pelajaran

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `mapel.view` | Melihat daftar mata pelajaran & KKM | FR-402 |
| `mapel.create` | Menambah mata pelajaran | FR-402 |
| `mapel.update` | Mengubah mata pelajaran & KKM default | FR-402 |
| `mapel.delete` | Menghapus mata pelajaran | FR-402 |
| `mapel.kurikulum` | Mengelola struktur kurikulum & mapel wajib per tingkat | FR-403 |

### 3.8 Jadwal Pelajaran

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `jadwal.view` | Melihat jadwal per kelas, per guru, per hari | FR-505 |
| `jadwal.create` | Menyusun jadwal mingguan (dengan deteksi bentrok otomatis) | FR-501, FR-502 |
| `jadwal.update` | Mengubah jadwal & jadwal khusus (ujian, piket, pergantian) | FR-504 |
| `jadwal.delete` | Menghapus jadwal | FR-501 |
| `jadwal.export` | Cetak/export jadwal ke PDF | FR-505 |

### 3.9 Absensi

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `absensi.view` | Melihat data & riwayat absensi | FR-601 |
| `absensi.input` | Menginput absensi harian per rombel / per mapel (mode cepat) | FR-601–FR-603 |
| `absensi.update` | Mengoreksi absensi yang sudah diinput (riwayat perubahan tercatat) | FR-603 |
| `absensi.lock` | Mengunci absensi per tanggal | FR-604 |
| `absensi.unlock` | Membuka kunci absensi; wajib alasan tercatat di audit log | FR-604 |
| `absensi.rekap` | Melihat & mengekspor rekap kehadiran + daftar peringatan dini | FR-605 |

### 3.10 Nilai

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `nilai.view` | Melihat nilai siswa (di PWA: hanya yang sudah dirilis) | FR-707 |
| `nilai.input` | Menginput nilai per komponen penilaian | FR-702 |
| `nilai.import` | Import nilai via Excel dengan validasi rentang 0–100 | FR-703 |
| `nilai.update` | Mengoreksi nilai yang sudah diinput | FR-702 |
| `nilai.komponen` | Mengelola komponen penilaian & bobot per mapel | FR-701 |
| `nilai.lock` | Mengunci periode nilai per semester | FR-705 |
| `nilai.unlock` | Membuka kunci periode nilai; wajib persetujuan kepala sekolah + alasan | FR-705 |
| `nilai.release` | Merilis nilai agar tampil di PWA siswa/orang tua | FR-707 |
| `nilai.rekap` | Melihat leger nilai per kelas/mapel & peringkat | FR-706 |

### 3.11 Pengguna

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `pengguna.view` | Melihat daftar akun pengguna | FR-901 |
| `pengguna.create` | Membuat akun pengguna baru | FR-901 |
| `pengguna.update` | Mengubah akun pengguna (termasuk penetapan sekolah) | FR-901 |
| `pengguna.delete` | Menonaktifkan akun pengguna | FR-901 |
| `pengguna.reset-password` | Mereset password pengguna lain | FR-901 |
| `pengguna.impersonate` | Login sebagai pengguna lain untuk troubleshooting (banner + audit) | FR-906 |

### 3.12 Peran (Role)

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `peran.view` | Melihat daftar peran & permission-nya | FR-902 |
| `peran.assign` | Menetapkan/mencabut peran pada pengguna | FR-902 |
| `peran.manage` | Membuat/mengubah definisi peran & permission (khusus pusat) | FR-902 |

### 3.13 Laporan

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `laporan.view` | Melihat laporan bawaan (siswa, guru, absensi, nilai, kelas, mutasi) | FR-1001 |
| `laporan.export` | Mengekspor laporan ke Excel/PDF dengan kop & logo sekolah | FR-1002, FR-1005 |
| `laporan.konsolidasi` | Laporan perbandingan lintas sekolah tingkat yayasan | FR-1003 |
| `laporan.builder` | Membuat & menyimpan template laporan kustom | FR-1004 |

### 3.14 Dashboard

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `dashboard.view` | Mengakses dashboard & KPI sesuai perannya (staf: web, siswa/ortu: ringkasan PWA) | FR-1101, FR-1103, FR-1104 |
| `dashboard.yayasan` | Dashboard agregat & tren seluruh sekolah | FR-1102 |

### 3.15 Pengumuman

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `pengumuman.view` | Melihat pengumuman & agenda sekolah | FR-1103 |
| `pengumuman.create` | Membuat draf pengumuman | FR-1103 |
| `pengumuman.update` | Mengubah pengumuman | FR-1103 |
| `pengumuman.delete` | Menghapus pengumuman | FR-1103 |
| `pengumuman.publish` | Menayangkan & mem-broadcast pengumuman ke target (kelas/peran) | FR-1103 |

### 3.16 Notifikasi

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `notifikasi.view` | Melihat riwayat notifikasi terkirim (WA/push/email) | PRD §11 |
| `notifikasi.broadcast` | Mengirim broadcast tersegmentasi (per kelas/angkatan/peran) | FR-606 |
| `notifikasi.template` | Mengelola template pesan notifikasi per sekolah | PRD §11 |

### 3.17 Perangkat PWA

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `pwa.devices.view` | Melihat daftar perangkat terdaftar per akun PWA | FR-1306 |
| `pwa.devices.revoke` | Mencabut sesi/login pada perangkat tertentu | FR-1306 |

### 3.18 Audit Log

| Permission | Deskripsi | FR Terkait |
|---|---|---|
| `audit.view` | Melihat log audit (mutasi data, login, export) | FR-905, PRD §9 |

### 3.19 Permission level platform — SaaS (guard `platform`)

| Permission | Deskripsi |
|---|---|
| `tenant.view` | Melihat daftar & profil tenant (sekolah pelanggan) |
| `tenant.create` | Membuat tenant baru (provisioning + setup awal) |
| `tenant.suspend` | Menangguhkan tenant (nonaktif sementara, mis. tunggakan) |
| `tenant.delete` | Menghapus tenant secara permanen — destruktif, wajib persetujuan ganda + backup |
| `tenant.impersonate` | Impersonasi ke konteks tenant untuk troubleshooting, tercatat di audit log platform |
| `tenant.quota.override` | Memberikan batas kuota kustom sementara per tenant (wajib alasan + kedaluwarsa, tercatat di audit) |
| `subscription.view` | Melihat langganan/paket aktif tiap tenant |
| `subscription.manage` | Mengelola langganan: ubah paket, perpanjang, batalkan |
| `plan.view` | Melihat katalog paket & harga |
| `plan.manage` | Mengelola paket, harga & batasan (seat, modul termasuk) |
| `invoice.view` | Melihat invoice/tagihan tenant |
| `invoice.issue` | Menerbitkan invoice/tagihan ke tenant |
| `invoice.refund` | Memproses refund; di atas ambang batas wajib persetujuan Platform Admin |
| `module.view` | Melihat katalog modul add-on |
| `module.toggle` | Mengaktifkan/menonaktifkan modul add-on untuk tenant (satu-satunya cara aktivasi) |
| `tenant-module.request` | Mengajukan aktivasi modul add-on (dipakai tenant via guard `web`, dan Admin Organisasi untuk sekolah-sekolah organisasinya) |
| `tenant-module.approve` | Menyetujui/menolak pengajuan add-on dari tenant |
| `platform.metrics.view` | Melihat metrik SaaS (MRR, tenant aktif, churn, utilisasi modul) |
| `organization.manage` | Mengelola organisasi/grup: membuat organisasi, menambah/mengeluarkan tenant anggota |
| `webhook.manage` | Mengelola webhook & kredensial integrasi API per tenant |
| `platform.audit.view` | Melihat audit log level platform (aksi PA/PS/PF, perubahan billing & modul) |

### 3.20 Langganan & modul — sisi tenant (guard `web`)

Permission berikut memakai **nama yang sama** dengan §3.19 tetapi berjalan di guard `web` dengan scope otomatis ke tenant sendiri (policy `OwnTenantScope`) — tanpa permission baru:

- `tenant-module.request` — Admin Sekolah mengajukan aktivasi modul add-on ke platform.
- `subscription.view` — melihat paket & status langganan sekolah sendiri.
- `invoice.view` — melihat tagihan sekolah sendiri.
- `module.view` — melihat katalog modul add-on yang tersedia.

> **Penting:** Admin Sekolah **tidak dapat** mengaktifkan modul sendiri. Aktivasi modul hanya terjadi lewat `module.toggle` oleh Platform Admin setelah `tenant-module.approve`. Pengajuan yang disetujui otomatis mengaktifkan modul dan menagihkan sesuai paket.

---

## 4. Matriks Peran × Permission

**Legenda kolom:** SA = Superadmin · AS = Admin Sekolah · KS = Kepala Sekolah · TU = Tata Usaha · WK = Wali Kelas · GM = Guru Mapel · BK = Guru BK · SW = Siswa · OT = Orang Tua

**Legenda sel:** ✓ = memiliki · – = tidak memiliki · ◐ = terbatas (lihat catatan kaki)

**Catatan kaki:**
- ¹ Hanya untuk rombel/kelas binaannya.
- ² Hanya untuk kelas & mapel sesuai penugasan mengajar aktif tahun ajaran berjalan.
- ³ Siswa: hanya data diri sendiri. Orang tua: hanya anak yang terelasi dengannya; nilai & absensi tampil di PWA hanya setelah dirilis.
- ⁴ Pembukaan kunci wajib mencantumkan alasan dan tercatat di audit log; `nilai.unlock` wajib ada persetujuan kepala sekolah.
- ⁵ Data tahun ajaran yang sudah ditutup bersifat read-only; perubahan hanya oleh superadmin via prosedur khusus.
- ⁶ Khusus superadmin; impersonasi menampilkan banner jelas dan tercatat di audit log.
- ⁷ Khusus superadmin (level yayasan); data lintas sekolah.
- ⁸ Admin sekolah mengelola profil & konfigurasi sekolahnya sendiri, kecuali identitas legal (NPSN, status) yang dikelola superadmin.
- ⁹ Kepala sekolah sebagai pemberi persetujuan; eksekusi teknis oleh admin sekolah.

### 4.1 Sekolah & Tahun Ajaran

| Permission | SA | AS | KS | TU | WK | GM | BK | SW | OT |
|---|---|---|---|---|---|---|---|---|---|
| `sekolah.view` | ✓ | ✓ | ✓ | ✓ | – | – | – | – | – |
| `sekolah.create` | ✓ | – | – | – | – | – | – | – | – |
| `sekolah.update` | ✓ | ◐⁸ | – | – | – | – | – | – | – |
| `sekolah.switch` | ✓⁶ | – | – | – | – | – | – | – | – |
| `tahun-ajaran.view` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `tahun-ajaran.create` | ✓ | ✓ | – | – | – | – | – | – | – |
| `tahun-ajaran.update` | ✓ | ✓ | – | – | – | – | – | – | – |
| `tahun-ajaran.activate` | ✓ | ✓ | ◐⁹ | – | – | – | – | – | – |
| `tahun-ajaran.close` | ✓ | ✓⁵ | – | – | – | – | – | – | – |
| `kalender.manage` | ✓ | ✓ | – | – | – | – | – | – | – |

### 4.2 Siswa & Wali Murid

| Permission | SA | AS | KS | TU | WK | GM | BK | SW | OT |
|---|---|---|---|---|---|---|---|---|---|
| `siswa.view` | ✓ | ✓ | ✓ | ✓ | ◐¹ | ◐² | ✓ | ◐³ | ◐³ |
| `siswa.create` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `siswa.update` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `siswa.delete` | ✓ | ✓ | – | – | – | – | – | – | – |
| `siswa.import` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `siswa.export` | ✓ | ✓ | ✓ | ✓ | – | – | – | – | – |
| `wali-murid.view` | ✓ | ✓ | ✓ | ✓ | ◐¹ | – | ✓ | – | ◐³ |
| `wali-murid.create` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `wali-murid.update` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `wali-murid.delete` | ✓ | ✓ | – | – | – | – | – | – | – |
| `wali-murid.link` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `wali-murid.export` | ✓ | ✓ | ✓ | ✓ | – | – | – | – | – |

### 4.3 Guru & Pegawai, Kelas, Mata Pelajaran

| Permission | SA | AS | KS | TU | WK | GM | BK | SW | OT |
|---|---|---|---|---|---|---|---|---|---|
| `guru.view` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | – | – |
| `guru.create` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `guru.update` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `guru.delete` | ✓ | ✓ | – | – | – | – | – | – | – |
| `guru.import` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `guru.export` | ✓ | ✓ | ✓ | ✓ | – | – | – | – | – |
| `guru.assign` | ✓ | ✓ | ◐⁹ | – | – | – | – | – | – |
| `kelas.view` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ◐³ | ◐³ |
| `kelas.create` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `kelas.update` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `kelas.delete` | ✓ | ✓ | – | – | – | – | – | – | – |
| `kelas.promote` | ✓ | ✓ | ◐⁹ | – | – | – | – | – | – |
| `mapel.view` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `mapel.create` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `mapel.update` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `mapel.delete` | ✓ | ✓ | – | – | – | – | – | – | – |
| `mapel.kurikulum` | ✓ | ✓ | ✓ | – | – | – | – | – | – |

### 4.4 Jadwal & Absensi

| Permission | SA | AS | KS | TU | WK | GM | BK | SW | OT |
|---|---|---|---|---|---|---|---|---|---|
| `jadwal.view` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `jadwal.create` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `jadwal.update` | ✓ | ✓ | – | ✓ | – | – | – | – | – |
| `jadwal.delete` | ✓ | ✓ | – | – | – | – | – | – | – |
| `jadwal.export` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | – | – |
| `absensi.view` | ✓ | ✓ | ✓ | ✓ | ◐¹ | ◐² | ✓ | ◐³ | ◐³ |
| `absensi.input` | ✓ | ✓ | – | – | ◐¹ | ◐² | – | – | – |
| `absensi.update` | ✓ | ✓ | – | – | ◐¹ | ◐² | – | – | – |
| `absensi.lock` | ✓ | ✓ | – | – | ◐¹ | ◐² | – | – | – |
| `absensi.unlock` | ✓ | ◐⁴ | – | – | – | – | – | – | – |
| `absensi.rekap` | ✓ | ✓ | ✓ | ✓ | ◐¹ | ◐² | ✓ | – | – |

### 4.5 Nilai

| Permission | SA | AS | KS | TU | WK | GM | BK | SW | OT |
|---|---|---|---|---|---|---|---|---|---|
| `nilai.view` | ✓ | ✓ | ✓ | ✓ | ◐¹ | ◐² | ✓ | ◐³ | ◐³ |
| `nilai.input` | ✓ | ✓ | – | – | – | ◐² | – | – | – |
| `nilai.import` | ✓ | ✓ | – | – | – | ◐² | – | – | – |
| `nilai.update` | ✓ | ✓ | – | – | – | ◐² | – | – | – |
| `nilai.komponen` | ✓ | ✓ | ✓ | – | – | – | – | – | – |
| `nilai.lock` | ✓ | ✓ | – | – | – | – | – | – | – |
| `nilai.unlock` | ✓ | – | ◐⁴ | – | – | – | – | – | – |
| `nilai.release` | ✓ | ✓ | ✓ | – | ◐¹ | – | – | – | – |
| `nilai.rekap` | ✓ | ✓ | ✓ | ✓ | ◐¹ | ◐² | – | – | – |

> Catatan konsistensi: `nilai.unlock` hanya dimiliki Superadmin (eksekusi) dengan persetujuan Kepala Sekolah (◐⁴) — Admin Sekolah **sengaja** tidak diberi agar tidak ada konflik kepentingan; pembukaan kunci selalu tercatat di audit log.

### 4.6 Pengguna, Peran & Audit Log

| Permission | SA | AS | KS | TU | WK | GM | BK | SW | OT |
|---|---|---|---|---|---|---|---|---|---|
| `pengguna.view` | ✓ | ✓ | ✓ | – | – | – | – | – | – |
| `pengguna.create` | ✓ | ✓ | – | – | – | – | – | – | – |
| `pengguna.update` | ✓ | ✓ | – | – | – | – | – | – | – |
| `pengguna.delete` | ✓ | ✓ | – | – | – | – | – | – | – |
| `pengguna.reset-password` | ✓ | ✓ | – | – | – | – | – | – | – |
| `pengguna.impersonate` | ✓⁶ | – | – | – | – | – | – | – | – |
| `peran.view` | ✓ | ✓ | ✓ | – | – | – | – | – | – |
| `peran.assign` | ✓ | ✓ | – | – | – | – | – | – | – |
| `peran.manage` | ✓ | – | – | – | – | – | – | – | – |
| `audit.view` | ✓ | ✓ | ◐ | – | – | – | – | – | – |

> `audit.view` untuk Kepala Sekolah bersifat baca saja dan terbatas pada log sekolahnya sendiri.

### 4.7 Laporan, Dashboard, Pengumuman, Notifikasi & Perangkat PWA

| Permission | SA | AS | KS | TU | WK | GM | BK | SW | OT |
|---|---|---|---|---|---|---|---|---|---|
| `laporan.view` | ✓ | ✓ | ✓ | ✓ | ◐¹ | ◐² | ✓ | – | – |
| `laporan.export` | ✓ | ✓ | ✓ | ✓ | ◐¹ | – | – | – | – |
| `laporan.konsolidasi` | ✓⁷ | – | – | – | – | – | – | – | – |
| `laporan.builder` | ✓ | ✓ | ✓ | – | – | – | – | – | – |
| `dashboard.view` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `dashboard.yayasan` | ✓⁷ | – | – | – | – | – | – | – | – |
| `pengumuman.view` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `pengumuman.create` | ✓ | ✓ | ✓ | – | – | – | – | – | – |
| `pengumuman.update` | ✓ | ✓ | ✓ | – | – | – | – | – | – |
| `pengumuman.delete` | ✓ | ✓ | ✓ | – | – | – | – | – | – |
| `pengumuman.publish` | ✓ | ✓ | ✓ | – | – | – | – | – | – |
| `notifikasi.view` | ✓ | ✓ | ✓ | ✓ | – | – | – | – | – |
| `notifikasi.broadcast` | ✓ | ✓ | ✓ | – | – | – | – | – | – |
| `notifikasi.template` | ✓ | ✓ | – | – | – | – | – | – | – |
| `pwa.devices.view` | ✓ | ✓ | – | – | – | – | – | – | – |
| `pwa.devices.revoke` | ✓ | ✓ | – | – | – | – | – | – | – |

> `dashboard.view` untuk Siswa/Orang Tua = ringkasan PWA (jadwal hari ini, kehadiran, nilai terbaru, pengumuman) — bukan dashboard staf.

### 4.8 Matriks level platform (guard `platform`)

**Legenda kolom:** PA = Platform Admin · PS = Platform Support/CS · PF = Platform Finance · AO = Admin Organisasi

**Catatan kaki:**
- ¹ Hanya untuk tenant dalam organisasi yang dikelolanya.
- ² Impersonasi tercatat, hanya untuk troubleshooting; dilarang mengubah data billing & data akademik.
- ³ Destruktif — wajib persetujuan tertulis dua pihak + backup sebelum eksekusi.

| Permission | PA | PS | PF | AO |
|---|---|---|---|---|
| `tenant.view` | ✓ | ✓ | ✓ | ◐¹ |
| `tenant.create` | ✓ | – | – | – |
| `tenant.suspend` | ✓ | – | – | – |
| `tenant.delete` | ✓³ | – | – | – |
| `tenant.impersonate` | ✓ | ◐² | – | – |
| `tenant.quota.override` | ✓ | – | – | – |
| `subscription.view` | ✓ | ✓ | ✓ | ◐¹ |
| `subscription.manage` | ✓ | – | ✓ | – |
| `plan.view` | ✓ | ✓ | ✓ | ✓ |
| `plan.manage` | ✓ | – | ✓ | – |
| `invoice.view` | ✓ | ✓ | ✓ | ◐¹ |
| `invoice.issue` | ✓ | – | ✓ | – |
| `invoice.refund` | ✓ | – | ✓ | – |
| `module.view` | ✓ | ✓ | ✓ | ✓ |
| `module.toggle` | ✓ | – | – | – |
| `tenant-module.request` | ✓ | – | – | ✓¹ |
| `tenant-module.approve` | ✓ | – | – | – |
| `platform.metrics.view` | ✓ | – | ✓ | – |
| `organization.manage` | ✓ | – | – | ◐¹ |
| `webhook.manage` | ✓ | – | – | – |
| `platform.audit.view` | ✓ | – | – | – |

### 4.9 Langganan & modul — sisi tenant (guard `web`)

| Permission | AS (Admin Sekolah) | KS (Kepala Sekolah) |
|---|---|---|
| `tenant-module.request` | ✓ | ◐¹ |
| `subscription.view` | ✓ | ✓ |
| `invoice.view` | ✓ | ✓ |
| `module.view` | ✓ | ✓ |

- ¹ Kepala Sekolah memberi persetujuan internal sebelum Admin Sekolah mengajukan add-on ke platform.
- Seluruh permission di tabel ini otomatis ter-scope ke tenant sendiri — tidak dapat melihat langganan/tagihan sekolah lain.
- Pengajuan add-on yang disetujui platform (`tenant-module.approve` + `module.toggle`) otomatis mengaktifkan modul di tenant dan menagihkan sesuai paket.

---

## 5. Aturan Khusus / Policy (di Luar Matriks)

Aturan-aturan ini ditegakkan di **lapisan Policy & query scope**, bukan sekadar permission — permission menjawab *"boleh melakukan apa"*, policy menjawab *"terhadap data siapa"*.

**(a) Wali kelas hanya untuk rombel binaannya.**
`WaliKelasPolicy`: setiap aksi `absensi.*`, `nilai.release`, `nilai.rekap`, `siswa.view`, `laporan.*` pada lingkup kelas wajib memeriksa `rombel.wali_kelas_id == auth()->user()->employee_id` **dan** tahun ajaran aktif. Rombel lain → 403 otomatis meski permission dimiliki.

**(b) Guru mapel hanya untuk kelas & mapel ampuannya.**
`NilaiPolicy` / `AbsensiPolicy`: `nilai.input|update|import` dan `absensi.input|update` wajib lolos cek `teaching_assignments` (employee_id + subject_id + rombel_id + academic_year_id aktif). Guru yang tidak terdaftar sebagai pengampu → 403. Berlaku juga untuk `nilai.rekap` per mapel.

**(c) Orang tua hanya melihat anaknya sendiri.**
`OrangTuaScope`: seluruh endpoint PWA orang tua difilter via relasi `guardian_student` — `whereIn('student_id', anak_terelasi)`. Melihat anak lain (bahkan satu sekolah) → 404/403. Berlaku lintas sekolah: satu akun dapat melihat anak di sekolah berbeda dalam jejaring.

**(d) Siswa hanya datanya sendiri.**
`SiswaScope`: endpoint PWA siswa selalu `where('student_id', auth()->user()->student_id)`. Nilai & absensi hanya tampil bila `nilai.release` sudah dieksekusi untuk semester/rombel tersebut.

**(e) Semua query otomatis ter-scope `school_id`.**
`BelongsToSchool` global scope di setiap model operasional. Superadmin yang memakai `sekolah.switch` mengeset `session('active_school_id')`; semua query & audit log memakai nilai ini. Test otomatis wajib: *data sekolah A tidak pernah bocor ke konteks sekolah B* (PRD §14 kriteria #3).

**(f) Impersonasi superadmin tercatat.**
`pengguna.impersonate` hanya untuk troubleshooting: menampilkan banner persisten "Anda sedang impersonasi sebagai X", menonaktifkan aksi destruktif tertentu selama impersonasi (opsional via config), dan setiap aksi selama sesi impersonasi dicatat di audit log dengan `impersonated_by`.

**(g) Data tahun ajaran tertutup bersifat read-only.**
Setelah `tahun-ajaran.close`, seluruh tabel operasional pada tahun ajaran tersebut dikunci di level policy (`TahunAjaranPolicy::isClosed()` → tolak create/update/delete). Perubahan hanya oleh superadmin melalui prosedur khusus §7 dengan justifikasi tertulis + audit ganda.

**Aturan tambahan:**
- **Prinsip least privilege:** peran baru/default hanya mendapat `view`; akses tulis selalu via pengajuan §7.
- **Akses sementara:** permission sensitif (`*.unlock`, `peran.assign`) dapat diberi batas waktu (expired_at pada model_has_roles); job harian mencabut yang kedaluwarsa.
- **Penolakan default (deny-by-default):** tidak ada permission di database = tidak boleh. Setiap route panel admin/API diproteksi middleware `permission:` atau policy.

### Aturan main dua level (SaaS)

**(a) Guard terpisah, tidak saling berlaku.**
Permission guard `platform` tidak berlaku di konteks tenant dan sebaliknya. Panel platform (`auth:platform`) dan aplikasi tenant (`auth:web`/`sanctum`) adalah aplikasi/auth context terpisah. Satu akun tidak boleh memegang peran platform dan peran tenant sekaligus — pengecualian hanya via impersonasi tercatat.

**(b) Platform Admin dilarang mengubah data akademik tenant.**
PA/PS/PF tidak memiliki akses tulis ke data akademik (siswa, nilai, absensi). Akses baca/tulis ke data tenant hanya via `tenant.impersonate` yang tercatat di audit log platform (siapa, kapan, tenant apa, durasi), dengan banner persisten selama sesi impersonasi.

**(c) Modul nonaktif = permission disembunyikan & ditolak.**
Modul add-on yang tidak aktif untuk suatu tenant: permission `{modul}.*`-nya disembunyikan dari UI (menu, tombol) dan setiap request ke API-nya ditolak dengan **403 `MODULE_DISABLED`**. Pengecekan status modul dilakukan via middleware `module:{nama-modul}` sebelum pengecekan permission.

**(d) Tenant suspended = akses staf ditolak, PWA read-only.**
Tenant yang di-suspend (`tenant.suspend`): login staf (guard `web`) ditolak dengan pesan billing ("Akun sekolah ditangguhkan — hubungi admin/langganan"), job & notifikasi keluar tenant dihentikan, sedangkan PWA siswa/orang tua menjadi **read-only** (data terakhir yang ter-cache tetap bisa dibaca, tanpa aksi tulis). Reaktivasi mengembalikan akses penuh tanpa kehilangan data.

**(e) Admin Organisasi terikat organisasinya.**
AO hanya melihat/mengelola tenant yang menjadi anggota organisasinya (`organization.manage` + scope `organization_id`). Melihat tenant organisasi lain → 403. Tenant mandiri (tanpa organisasi) tidak memiliki AO.

**(f) Perubahan modul & billing diaudit di level platform.**
Setiap `module.toggle`, `tenant.suspend`, `subscription.manage`, `invoice.refund` tercatat di audit log platform (`platform.audit.view`) dengan before/after state — terpisah dari audit log tenant.

---

## 6. Contoh `RolePermissionSeeder`

Seeder ringkas memakai Spatie Laravel Permission. Dijalankan via `php artisan db:seed --class=RolePermissionSeeder`.

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Daftarkan seluruh permission (idempotent)
        $resources = [
            'sekolah'       => ['view', 'create', 'update', 'switch'],
            'tahun-ajaran'  => ['view', 'create', 'update', 'activate', 'close'],
            'kalender'      => ['manage'],
            'siswa'         => ['view', 'create', 'update', 'delete', 'import', 'export'],
            'wali-murid'    => ['view', 'create', 'update', 'delete', 'link', 'export'],
            'guru'          => ['view', 'create', 'update', 'delete', 'import', 'export', 'assign'],
            'kelas'         => ['view', 'create', 'update', 'delete', 'promote'],
            'mapel'         => ['view', 'create', 'update', 'delete', 'kurikulum'],
            'jadwal'        => ['view', 'create', 'update', 'delete', 'export'],
            'absensi'       => ['view', 'input', 'update', 'lock', 'unlock', 'rekap'],
            'nilai'         => ['view', 'input', 'import', 'update', 'komponen', 'lock', 'unlock', 'release', 'rekap'],
            'pengguna'      => ['view', 'create', 'update', 'delete', 'reset-password', 'impersonate'],
            'peran'         => ['view', 'assign', 'manage'],
            'laporan'       => ['view', 'export', 'konsolidasi', 'builder'],
            'dashboard'     => ['view', 'yayasan'],
            'pengumuman'    => ['view', 'create', 'update', 'delete', 'publish'],
            'notifikasi'    => ['view', 'broadcast', 'template'],
            'pwa.devices'   => ['view', 'revoke'],
            'audit'         => ['view'],
        ];

        foreach ($resources as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name'       => "{$resource}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // 2. Petakan permission inti per peran (◐/terbatas ditegakkan di Policy, bukan di sini)
        $matrix = [
            'platform_admin' => ['*'], // seluruh permission (guard platform, ADR-0004)
            'admin_sekolah' => [
                'sekolah.view', 'sekolah.update',
                'tahun_ajaran.view', 'tahun_ajaran.create', 'tahun_ajaran.update',
                'tahun_ajaran.activate', 'tahun_ajaran.close', 'kalender.manage',
                'siswa.view', 'siswa.create', 'siswa.update', 'siswa.delete', 'siswa.import', 'siswa.export',
                'wali_murid.view', 'wali_murid.create', 'wali_murid.update', 'wali_murid.delete',
                'wali_murid.link', 'wali_murid.export',
                'guru.view', 'guru.create', 'guru.update', 'guru.delete', 'guru.import', 'guru.export', 'guru.assign',
                'kelas.view', 'kelas.create', 'kelas.update', 'kelas.delete', 'kelas.promote',
                'mapel.view', 'mapel.create', 'mapel.update', 'mapel.delete', 'mapel.kurikulum',
                'jadwal.view', 'jadwal.create', 'jadwal.update', 'jadwal.delete', 'jadwal.export',
                'absensi.view', 'absensi.input', 'absensi.update', 'absensi.lock', 'absensi.unlock', 'absensi.rekap',
                'nilai.view', 'nilai.input', 'nilai.import', 'nilai.update', 'nilai.komponen',
                'nilai.lock', 'nilai.release', 'nilai.rekap',
                'pengguna.view', 'pengguna.create', 'pengguna.update', 'pengguna.delete', 'pengguna.reset-password',
                'peran.view', 'peran.assign',
                'laporan.view', 'laporan.export', 'laporan.builder',
                'dashboard.view',
                'pengumuman.view', 'pengumuman.create', 'pengumuman.update', 'pengumuman.delete', 'pengumuman.publish',
                'notifikasi.view', 'notifikasi.broadcast', 'notifikasi.template',
                'pwa.devices.view', 'pwa.devices.revoke',
                'audit.view',
            ],
            'kepala_sekolah' => [
                'sekolah.view', 'tahun_ajaran.view',
                'siswa.view', 'siswa.export', 'wali_murid.view', 'wali_murid.export',
                'guru.view', 'guru.export', 'guru.assign',
                'kelas.view', 'kelas.promote', 'mapel.view', 'mapel.kurikulum',
                'jadwal.view', 'jadwal.export',
                'absensi.view', 'absensi.rekap',
                'nilai.view', 'nilai.komponen', 'nilai.unlock', 'nilai.release', 'nilai.rekap',
                'pengguna.view', 'peran.view',
                'laporan.view', 'laporan.export', 'laporan.builder',
                'dashboard.view',
                'pengumuman.view', 'pengumuman.create', 'pengumuman.update', 'pengumuman.delete', 'pengumuman.publish',
                'notifikasi.view', 'notifikasi.broadcast',
                'audit.view',
            ],
            'tata_usaha' => [
                'sekolah.view', 'tahun_ajaran.view',
                'siswa.view', 'siswa.create', 'siswa.update', 'siswa.import', 'siswa.export',
                'wali_murid.view', 'wali_murid.create', 'wali_murid.update', 'wali_murid.link', 'wali_murid.export',
                'guru.view', 'guru.create', 'guru.update', 'guru.import', 'guru.export',
                'kelas.view', 'kelas.create', 'kelas.update',
                'mapel.view', 'mapel.create', 'mapel.update',
                'jadwal.view', 'jadwal.create', 'jadwal.update', 'jadwal.export',
                'absensi.view', 'absensi.rekap',
                'nilai.view', 'nilai.rekap',
                'laporan.view', 'laporan.export',
                'dashboard.view', 'pengumuman.view', 'notifikasi.view',
            ],
            'wali_kelas' => [
                'tahun_ajaran.view', 'siswa.view', 'wali_murid.view',
                'guru.view', 'kelas.view', 'mapel.view', 'jadwal.view', 'jadwal.export',
                'absensi.view', 'absensi.input', 'absensi.update', 'absensi.lock', 'absensi.rekap',
                'nilai.view', 'nilai.release', 'nilai.rekap',
                'laporan.view', 'laporan.export',
                'dashboard.view', 'pengumuman.view',
            ],
            'guru_mapel' => [
                'tahun_ajaran.view', 'siswa.view',
                'guru.view', 'kelas.view', 'mapel.view', 'jadwal.view', 'jadwal.export',
                'absensi.view', 'absensi.input', 'absensi.update', 'absensi.lock', 'absensi.rekap',
                'nilai.view', 'nilai.input', 'nilai.import', 'nilai.update', 'nilai.rekap',
                'laporan.view',
                'dashboard.view', 'pengumuman.view',
            ],
            'guru_bk' => [
                'tahun_ajaran.view', 'siswa.view', 'wali_murid.view',
                'guru.view', 'kelas.view', 'mapel.view', 'jadwal.view', 'jadwal.export',
                'absensi.view', 'absensi.rekap', 'nilai.view',
                'laporan.view',
                'dashboard.view', 'pengumuman.view',
            ],
            // Siswa & orang tua: hanya baca via PWA (lihat §4.7 & catatan kaki ³)
            'siswa'      => ['tahun_ajaran.view', 'siswa.view', 'kelas.view', 'mapel.view',
                             'jadwal.view', 'absensi.view', 'nilai.view',
                             'dashboard.view', 'pengumuman.view'],
            'orang_tua'   => ['tahun_ajaran.view', 'siswa.view', 'wali_murid.view', 'kelas.view', 'mapel.view',
                             'jadwal.view', 'absensi.view', 'nilai.view',
                             'dashboard.view', 'pengumuman.view'],
        ];

        foreach ($matrix as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($perms === ['*']) {
                // Hanya permission guard web — permission guard platform tidak ikut
                $role->syncPermissions(Permission::where('guard_name', 'web')->get());
            } else {
                $role->syncPermissions(Permission::whereIn('name', $perms)->where('guard_name', 'web')->get());
            }
        }
    }
}
```

### Seeder peran platform (guard `platform`)

Dijalankan via `php artisan db:seed --class=PlatformRoleSeeder`. Permission platform dibuat dengan `guard_name => 'platform'` agar tidak tercampur dengan permission tenant.

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PlatformRoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Daftarkan permission platform (idempotent, guard terpisah)
        $platformResources = [
            'tenant'          => ['view', 'create', 'suspend', 'delete', 'impersonate'],
            'subscription'    => ['view', 'manage'],
            'plan'            => ['view', 'manage'],
            'invoice'         => ['view', 'issue', 'refund'],
            'module'          => ['view', 'toggle'],
            'tenant-module'   => ['request', 'approve'],
            'platform.metrics'=> ['view'],
            'organization'    => ['manage'],
            'webhook'         => ['manage'],
            'platform.audit'  => ['view'],
        ];

        foreach ($platformResources as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name'       => "{$resource}.{$action}",
                    'guard_name' => 'platform',
                ]);
            }
        }

        // 2. Petakan permission per peran platform (◐/terbatas ditegakkan di Policy)
        $matrix = [
            'platform-admin'   => ['*'], // seluruh permission guard platform
            'platform_support' => [
                'tenant.view', 'tenant.impersonate',
                'subscription.view', 'plan.view', 'invoice.view', 'module.view',
            ],
            'platform_finance' => [
                'tenant.view',
                'subscription.view', 'subscription.manage',
                'plan.view', 'plan.manage',
                'invoice.view', 'invoice.issue', 'invoice.refund',
                'module.view', 'platform.metrics.view',
            ],
            'admin_organisasi' => [
                'tenant.view',
                'subscription.view', 'plan.view', 'invoice.view', 'module.view',
                'tenant_module.request', 'organization.manage',
            ],
        ];

        foreach ($matrix as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'platform']);

            if ($perms === ['*']) {
                $role->syncPermissions(
                    Permission::where('guard_name', 'platform')->get()
                );
            } else {
                $role->syncPermissions(
                    Permission::whereIn('name', $perms)->where('guard_name', 'platform')->get()
                );
            }
        }
    }
}
```

> **Catatan implementasi:**
> - Guard `web` dipakai untuk panel staf (Livewire/Blade); untuk API PWA gunakan guard `sanctum` dengan permission yang sama — Spatie mendukung multi-guard, cukup duplikasi seeder dengan `guard_name => 'sanctum'` untuk peran `siswa` & `orang_tua`.
> - Guard `platform` dipakai untuk panel operator SaaS (aplikasi/auth context terpisah). Permission `platform` tidak bisa di-assign ke role guard `web` dan sebaliknya — Spatie menolak assignment lintas guard.
> - Tanda ◐ (terbatas) **tidak** direpresentasikan sebagai permission terpisah; pembatasan ditegakkan di Policy (lihat §5). Ini menjaga jumlah permission tetap terkendali.
> - Jalankan `php artisan permission:cache-reset` setelah seed di production.

---

## 7. Prosedur Permintaan Perubahan Akses

Setiap penambahan, pengurangan, atau perubahan permission/peran **di luar matriks ini** wajib melalui alur berikut. Alur dibedakan menjadi **level tenant** (diagram di bawah) dan **level platform** (subbagian khusus setelah tabel SLA). Perubahan langsung di database production tanpa tiket **dilarang**.

```
┌───────────┐    ┌──────────────┐    ┌──────────────────┐    ┌──────────────┐    ┌─────────────┐
│ PENGAJUAN │───▶│ VERIFIKASI   │───▶│ PERSETUJUAN      │───▶│ EKSEKUSI     │───▶│ REVIEW      │
│ (pemohon) │    │ (admin       │    │ (kepala sekolah/ │    │ (admin/      │    │ BERKALA    │
│           │    │  sekolah)    │    │  superadmin)     │    │  superadmin) │    │ (semester)  │
└───────────┘    └──────────────┘    └──────────────────┘    └──────────────┘    └─────────────┘
```

| Tahap | Pelaku | Aktivitas | SLA |
|---|---|---|---|
| **1. Pengajuan** | Pemohon (guru/staf) | Mengisi formulir/tiket: nama, peran saat ini, permission/peran yang diminta, alasan bisnis, masa berlaku (tetap/sementara + tanggal kedaluwarsa) | — |
| **2. Verifikasi** | Admin Sekolah | Cek kelengkapan, terapkan prinsip *least privilege* (usulkan alternatif permission terkecil yang mencukupi); tolak langsung bila tidak jelas alasannya | 1×24 jam |
| **3. Persetujuan** | Kepala Sekolah | Menyetujui/menolak. Untuk permintaan **lintas sekolah**, peran baru, atau permission sensitif (`*.unlock`, `peran.manage`, `pengguna.impersonate`, `laporan.konsolidasi`): persetujuan oleh **Superadmin** | 2×24 jam |
| **4. Eksekusi** | Admin Sekolah / Superadmin | Pemberian peran/permission via panel (tercatat otomatis di audit log + nomor tiket dicantumkan di keterangan); untuk akses sementara, set `expired_at` | 1×24 jam |
| **5. Notifikasi** | Sistem | Pemohon menerima notifikasi (PWA/WA) bahwa akses aktif beserta masa berlakunya | Otomatis |
| **6. Review berkala** | Admin Sekolah + Kepala Sekolah | **Resertifikasi akses tiap semester**: daftar seluruh akun & perannya ditinjau; akses yang tidak lagi relevan dicabut; akses sementara yang kedaluwarsa dicabut otomatis oleh job harian | Tiap semester |

### Alur level platform vs level tenant

Alur diagram di atas berlaku untuk **level tenant**. Perubahan akses **level platform** mengikuti alur terpisah yang lebih ketat:

| Aspek | Level tenant | Level platform |
|---|---|---|
| Contoh kasus | Guru butuh `nilai.unlock`; TU butuh `siswa.import` | Pengangkatan Platform Support baru; pemberian `tenant.suspend`/`invoice.refund`; perubahan definisi peran platform |
| Pengaju | Staf sekolah | Tim operator SaaS |
| Verifikasi | Admin Sekolah (least privilege) | Platform Admin |
| Persetujuan | Kepala Sekolah (lintas sekolah/peran baru/sensitif: Superadmin) | **Manajemen SaaS** (tertulis); untuk `tenant.delete`: persetujuan ganda |
| Eksekusi | Admin Sekolah / Superadmin via panel tenant | Platform Admin via panel platform |
| Audit | Audit log tenant | Audit log platform (`platform.audit.view`) |
| SLA persetujuan | 2×24 jam | 3×24 jam kerja |

```
┌───────────┐    ┌──────────────┐    ┌──────────────────┐    ┌──────────────┐
│ PENGAJUAN │───▶│ VERIFIKASI   │───▶│ PERSETUJUAN      │───▶│ EKSEKUSI +   │
│ (tim SaaS)│    │ (Platform    │    │ (manajemen SaaS, │    │ AUDIT        │
│           │    │  Admin)      │    │  tertulis)       │    │ (panel       │
└───────────┘    └──────────────┘    └──────────────────┘    │ platform)    │
                                                            └──────────────┘
```

**Pencabutan darurat level platform:** bila terindikasi penyalahgunaan oleh operator, Platform Admin (atau manajemen SaaS) mencabut akses **seketika**, lalu membuat tiket insiden maksimal 1×24 jam. Untuk tenant: suspensi darurat tenant (`tenant.suspend`) hanya oleh Platform Admin dengan alasan tertulis.

**Pencabutan darurat:** bila terindikasi penyalahgunaan, Admin Sekolah/Superadmin dapat mencabut akses **seketika** tanpa menunggu persetujuan, lalu melaporkannya ke Kepala Sekolah maksimal 1×24 jam dengan tiket insiden. Pencabutan darurat tetap tercatat di audit log.

**Perubahan matriks dokumen ini:** revisi §3/§4 (mis. menambah permission modul baru Fase 2) memerlukan persetujuan Product Owner + Tech Lead, penomoran versi dokumen baru, dan migrasi seeder (`RolePermissionSeeder` dijalankan ulang — idempotent via `firstOrCreate`/`syncPermissions`).

---

### Riwayat Perubahan

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.1 | 9 Okt 2026 | Pivot SaaS: 4 peran level platform (§1.2), 20 permission platform (§3.19) + sisi tenant (§3.20), matriks level platform (§4.8) & langganan tenant (§4.9), Aturan main dua level (§5), `PlatformRoleSeeder` + guard terpisah (§6), alur perubahan akses platform vs tenant (§7). Total 105 permission. |
| 1.2 | 9 Okt 2026 | Kuota & trial: permission `tenant.quota.override` (§3.19, §4.8), matriks enforcement kuota & batas trial formal di PRD §9.3/§9.5. Total 106 permission. |
| 1.0 | 9 Okt 2026 | Rilis awal: 85 permission, 9 peran, mengacu PRD SIMS v1.0 |

*— Akhir Dok 05 —*
