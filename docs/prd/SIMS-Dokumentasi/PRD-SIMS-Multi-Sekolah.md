# PRD — SIMS: Platform SaaS Manajemen Sekolah Multi-Tenant

| Atribut | Keterangan |
|---|---|
| Nama Produk | SIMS (nama kerja, dapat diganti) |
| Model Bisnis | **SaaS multi-tenant publik** — setiap sekolah adalah pelanggan yang berlangganan |
| Versi Dokumen | 1.1 (pivot SaaS, 9 Oktober 2026) |
| Tanggal | 9 Oktober 2026 |
| Tech Stack Utama | Laravel 13, PHP 8.3+, PostgreSQL/MySQL, Redis |
| Status | Draft untuk review |

---

## 1. Ringkasan Eksekutif

SIMS adalah **platform SaaS (Software as a Service) multi-tenant untuk manajemen sekolah** — satu aplikasi yang melayani banyak sekolah sebagai pelanggan mandiri yang berlangganan. Setiap sekolah (SD/SMP/SMA/SMK, berbagai skala) mendapatkan *workspace* terisolasi berisi operasional akademik dan administrasi: data siswa, wali murid, guru/pegawai, kelas & mata pelajaran, jadwal, absensi, nilai, tahun ajaran, pengguna, laporan, dan dashboard — ditambah **PWA (Progressive Web App)** khusus siswa dan orang tua yang berfungsi seperti aplikasi native tanpa instalasi dari app store.

Sekolah dapat **registrasi mandiri (self-service)**, memilih preset sesuai jenjang, dan langsung memakai sistem dalam hitungan menit dengan masa trial gratis. Fitur di luar modul inti ditawarkan sebagai **add-on modular** yang diaktifkan per sekolah via *feature flag* — sekolah hanya membayar yang dipakai. Opsional: beberapa sekolah dapat dikelompokkan dalam **Organisasi** (mis. yayasan) dengan laporan konsolidasi sebagai add-on tersendiri.

Dokumen ini menjadi acuan tunggal (*single source of truth*) bagi product owner, developer, QA, dan stakeholder untuk lingkup, kebutuhan fungsional/non-fungsional, arsitektur, model bisnis, roadmap, dan kriteria penerimaan.

## 2. Latar Belakang & Masalah

- Sekolah (SD/SMP/SMA/SMK, negeri maupun swasta, berbagai skala) membutuhkan sistem informasi siap pakai, tetapi membangun/mengelola sistem sendiri terlalu mahal dan butuh tim IT yang umumnya tidak dimiliki sekolah.
- Solusi generik yang ada sering kaku: tidak mengikuti jenjang & kurikulum Indonesia, mahal untuk sekolah kecil, dan butuh instalasi/server sendiri.
- Administrasi masih banyak yang manual/Excel: absensi kertas, rekap nilai berhari-hari, laporan ke dinas disusun ulang tiap semester.
- Orang tua kesulitan memantau kehadiran, nilai, dan informasi sekolah anak secara real-time.

**Solusi:** platform SaaS multi-tenant — sekolah **berlangganan**, registrasi mandiri dalam hitungan menit, data terisolasi per pelanggan, preset siap pakai per jenjang (SD/SMP/SMA/SMK), modul tambahan bersifat opsional (bayar sesuai pakai), dan akses mobile untuk siswa/orang tua via PWA.

## 3. Tujuan Produk & Indikator Keberhasilan

### Tujuan
1. Menyediakan layanan SaaS manajemen sekolah yang bisa dipakai sekolah mana pun via registrasi mandiri, dengan data terisolasi per pelanggan.
2. Mendigitalkan proses akademik inti (absensi, nilai, jadwal, laporan) dengan preset siap pakai per jenjang.
3. Memberi transparansi real-time kepada siswa & orang tua via PWA.
4. Mencapai *unit economics* SaaS yang sehat: retensi tinggi, ekspansi modul per pelanggan (NRR > 100%), dan akuisisi yang efisien.

### Indikator Keberhasilan (KPI)

**KPI operasional (per pelanggan):**
- ≥ 90% data siswa & guru termigrasi dan valid dalam 1 bulan go-live per sekolah.
- Waktu input absensi harian per kelas ≤ 2 menit.
- Rekap nilai per semester dapat dihasilkan ≤ 5 menit (dari sebelumnya hitungan hari).
- ≥ 70% orang tua aktif membuka PWA minimal 1x/minggu.
- Uptime aplikasi ≥ 99,5% per bulan.

**KPI bisnis SaaS:**
- Pertumbuhan MRR ≥ 10%/bulan (tahun pertama).
- *Logo churn* ≤ 3%/bulan; *revenue churn* ≤ 2%/bulan.
- Konversi trial → berbayar ≥ 25%.
- NRR (*net revenue retention*) ≥ 105% (ekspansi add-on modul menutup churn).
- *CAC payback period* ≤ 12 bulan.
- Waktu aktivasi tenant baru (registrasi → siap pakai) ≤ 15 menit.

## 4. Lingkup Produk

### In Scope (Fase MVP → lihat Roadmap §14)
Seluruh fitur pada §7 (13 modul utama) + fondasi SaaS Fase 0 (registrasi tenant mandiri, paket & langganan, *feature flag* modul, preset per jenjang, resolusi tenant via subdomain), fondasi multi-tenant, RBAC, audit log, dan PWA siswa/orang tua.

### Out of Scope (tidak dikerjakan di versi ini, bisa jadi fase lanjutan)
- Aplikasi native iOS/Android (digantikan PWA).
- Modul keuangan/SPP lengkap (katalog add-on §8, Fase 2).
- Billing penuh: invoice otomatis, *dunning*, *suspend* otomatis (Fase 2 — versi MVP memakai penagihan manual/terbatas).
- Laporan konsolidasi multi-sekolah tingkat organisasi/yayasan (modul add-on **Organisasi**, Fase 3).
- Integrasi Dapodik/EMIS otomatis (disiapkan API-nya, integrasi penuh di fase 2).
- Video conference / LMS penuh (versi mini diusulkan §8).

## 5. Pengguna & Peran (RBAC)

Terdapat dua lapis peran yang **dipisahkan tegas**: peran **level platform** (operator SaaS) dan peran **level tenant/sekolah** (pelanggan). Akun platform tidak dapat mengakses data operasional sekolah kecuali melalui mekanisme tercatat (impersonasi Support/CS atau konteks tenant eksplisit oleh Platform Admin).

### 5.1 Peran Level Platform (operator SaaS)

| Peran | Lingkup Data | Akses Utama |
|---|---|---|
| Platform Admin | Seluruh platform | Kelola tenant (sekolah), paket & harga, registry modul, *feature flag* global, monitoring, konfigurasi sistem |
| Support/CS | Terbatas per tiket | Bantuan pelanggan, impersonasi terbatas ke tenant (tercatat + banner jelas), lihat status langganan |
| Finance | Data billing | Invoice, pembayaran, *dunning*, laporan MRR/churn/NRR |
| Admin Organisasi | Grup sekolahnya | Kelola sekolah-sekolah dalam organisasinya; laporan konsolidasi (membutuhkan add-on **Organisasi**) |

### 5.2 Peran Level Tenant/Sekolah (pelanggan)

| Peran | Lingkup Data | Akses Utama |
|---|---|---|
| Admin Sekolah | 1 sekolah | Seluruh data operasional sekolahnya; kelola langganan & add-on modul sekolahnya |
| Kepala Sekolah | 1 sekolah | Dashboard, laporan, persetujuan (approve) |
| Tata Usaha | 1 sekolah | Data siswa, wali, guru, kelas, tahun ajaran |
| Bendahara | 1 sekolah | Modul Keuangan/SPP (add-on) |
| Wali Kelas | Kelas binaannya | Absensi, nilai, catatan siswa di kelasnya |
| Guru Mapel | Kelas yang diajar | Jadwal mengajar, absensi mapel, input nilai |
| Guru BK | 1 sekolah | Catatan konseling, poin pelanggaran (modul tambahan) |
| Siswa | Data dirinya | PWA: jadwal, absensi, nilai, pengumuman |
| Orang Tua/Wali | Anaknya | PWA: kehadiran, nilai, tagihan (modul Keuangan), pengumuman |

**Aturan RBAC:** menggunakan *Spatie Laravel Permission*; setiap aksi penting (create/update/delete/approve/export) dipetakan ke permission granular, mis. `nilai.input`, `absensi.rekap`, `laporan.export`.

**Keputusan desain — model identitas (G-08, DICABUT — diganti ADR-0004):** berlaku **identitas global + membership per tenant**. Satu `users` per orang (tanpa `school_id`); keanggotaan tiap sekolah dicatat di `memberships` (status aktif/dinonaktifkan). Token = 1 membership aktif. Wali dengan anak di sekolah berbeda memakai SATU akun (FR-203) — dashboard lintas tenant diagregasi server-side per membership. Operator platform = `users.is_platform = true` + guard `platform` terpisah (DILARANG menyimpulkan privilege dari `school_id` null).

## 6. Tech Stack & Arsitektur

### 6.1 Tech Stack

| Lapisan | Teknologi | Keterangan |
|---|---|---|
| Framework | **Laravel 13** (PHP 8.3+) | Monolit modular; didukung bug-fix s/d Q3 2027, security s/d Mar 2028 |
| Admin Panel | **UI custom** (Blade + Livewire 3 + Tailwind, mengikuti design system) | Panel staf sesuai brand; design system disediakan terpisah |
| Interaktivitas | Livewire 3 + Alpine.js | Form dinamis tanpa SPA penuh |
| CSS | Tailwind CSS | Utility-first, mengikuti design system |
| Database | **PostgreSQL 16** (utama) / MySQL 8 (alternatif) | Rekomendasi PostgreSQL untuk agregasi laporan |
| Cache/Queue/Session | Redis | Cache query, session, queue job |
| Queue Worker | Laravel Horizon + Supervisor | Job berat: rekap nilai, export Excel/PDF, notifikasi massal |
| Auth Web | Laravel session + 2FA (opsional) | Untuk staf |
| Auth API/PWA | Laravel Sanctum (token) | Untuk PWA & integrasi |
| Authorization | Spatie Laravel Permission | RBAC granular |
| Storage file | S3-compatible (MinIO/AWS S3) | Foto siswa, dokumen, export |
| Notifikasi | FCM (push PWA), WhatsApp Gateway (Fonnte/Wablas), Mail | Multi-channel, template per sekolah |
| Dokumen | Laravel Excel (Maatwebsite), Dompdf/Snappy | Import/export & cetak rapor |
| PWA | Service Worker (Workbox) + Web App Manifest | Instalasi, offline cache, push notification |
| Testing | Pest 4 | Unit, feature, dan browser test |
| CI/CD | GitHub Actions / GitLab CI | Test otomatis → staging → production |
| Deployment | Docker + Nginx + PHP-FPM | 1 container app, terpisah DB/Redis; backup otomatis harian |
| Monitoring | Laravel Telescope (non-prod), uptime monitor, log terpusat | Observabilitas |

**Keputusan arsitektur:** monolit modular Laravel (bukan microservices) agar tim kecil bisa cepat deliver; modul dipisah per *namespace/domain* (`app/Domains/Akademik`, `.../Kepegawaian`, dst.) supaya mudah dipecah di masa depan.

### 6.2 Arsitektur Multi-Tenant SaaS (Satu Aplikasi, Banyak Pelanggan)

**Pendekatan yang dipakai: single database + kolom `school_id` (shared schema). Setiap sekolah = satu tenant = satu pelanggan yang berlangganan.**

- Setiap tabel data operasional memiliki `school_id`; *Global Scope* Eloquent otomatis memfilter sesuai tenant aktif dari request.
- **Resolusi tenant:** via subdomain `{sekolah}.sims.id` (atau domain kustom via CNAME untuk paket Enterprise); API/PWA menentukan tenant dari token (relasi user → sekolah), klien tidak pernah mengirim `school_id`.
- Tabel `schools` menyimpan identitas & konfigurasi per sekolah (nama, NPSN, jenjang, subdomain, logo, kop surat, zona waktu, template notifikasi) + status langganan.
- **Onboarding mandiri:** registrasi → verifikasi email/WA → provisioning otomatis (seed: akun Admin Sekolah, tahun ajaran default, preset jenjang, modul inti aktif) → trial 30 hari. Target: registrasi → siap pakai ≤ 15 menit.
- Platform Admin dapat masuk konteks tenant tertentu untuk operasional (tercatat di audit log); Support/CS hanya via impersonasi terbatas per tiket.
- Isolasi dijamin di 3 lapis: (1) query scope, (2) policy/authorization, (3) validasi tenant pada setiap request API.
- **Jalur evolusi:** tier Enterprise dapat memilih *dedicated database* (isolasi penuh untuk kebutuhan regulasi/data residency) tanpa mengubah domain logic — tenant resolution yang sama, koneksi DB dipilih per tenant.

**Alasan:** biaya infrastruktur 1x, deploy 1x, *time-to-value* hitungan menit untuk pelanggan baru, dan skala ekonomi SaaS (margin membaik seiring tambah tenant).

### 6.3 Struktur Aplikasi (Monolit Modular)

Struktur mengikuti kontrak modul pada §6.5:

```
app/
  Modules/
    IntiAkademik/     → Siswa, Kelas, Mapel, Jadwal, Absensi, Nilai, TahunAjaran
    Kepegawaian/      → Guru, Pegawai, WaliMurid (bagian dari Inti Akademik)
    ...               → modul add-on lain (Keuangan, Ppdb, Erapor, Organisasi, dst.)
  Platform/           → Tenant, Billing, Modul registry, Provisioning (milik operator SaaS)
  Http/Controllers, Livewire/Components, ...
```

Setiap modul punya Model, Policy, Service, komponen UI panel admin, dan test sendiri.

### 6.4 Infrastruktur & DevOps

- Environment: `local` → `staging` → `production`; seed data demo per environment.
- Backup: database full harian + incremental, retensi 30 hari; file storage versioning; *restore drill* tiap kuartal (RPO ≤ 24 jam, RTO ≤ 4 jam).
- **Strategi restore per-tenant (T-01):** karena database memakai shared schema (`school_id`), restore backup full akan mengembalikan *semua* tenant sekaligus. Untuk kebutuhan rollback satu sekolah (mis. salah impor massal), strateginya: (1) *point-in-time recovery* ke database klon, lalu ekspor logis (dump per `school_id`) dan impor selektif ke production; (2) mitigasi operasional: fitur **snapshot pra-impor massal** — sebelum impor massal, sistem menyimpan snapshot data terdampak sehingga impor bisa di-*undo* dari panel tanpa menyentuh backup; (3) prosedur ini didokumentasikan di runbook DR dan diuji tiap kuartal bersama *restore drill*.
- Skalabilitas awal: 1 app server + 1 DB server menangani ±50 sekolah / ±50.000 siswa; scale horizontal via load balancer bila perlu.
- Multi-region siap di fase skala (data residency untuk tier Enterprise).

### 6.5 Arsitektur Modular (Feature Flag per Tenant)

Setiap modul (Modul Inti Akademik, Keuangan/SPP, PPDB, E-Rapor, Organisasi, dst.) adalah **unit terpisah** yang dapat diaktifkan/nonaktifkan per tenant. Kontrak sebuah modul:

1. **ServiceProvider sendiri** — mendaftarkan route, config, view/lang namespace, dan permission miliknya; tanpa mengubah kode modul lain.
2. **Migrasi terpisah** dengan prefix nama modul (mis. `modul_keuangan_*`) agar dapat di-*install/uninstall* per kebutuhan.
3. **Permission namespaced** `{modul}.{resource}.{aksi}` (mis. `keuangan.tagihan.create`, `ppdb.pendaftar.verify`) yang didaftarkan modul ke registry permission saat instalasi.
4. **Manifest modul** (`module.json`): key unik, nama, versi, deskripsi, **prasyarat** (modul lain yang wajib aktif), dan registrasi menu & route otomatis ke navigasi.
5. **Feature flag per tenant** — tabel `tenant_modules` (`school_id`, `module_key`, `aktif`, `config` json); middleware `EnsureModuleActive` menolak akses modul non-aktif (403 + pesan upgrade).
6. **Modul Inti Akademik selalu aktif** untuk semua tenant; add-on aktif hanya bila (a) tenant berlangganan modul tersebut, dan (b) status langganan valid (tidak *suspended*).
7. Penonaktifan modul = *soft-off*: menu & route disembunyikan, data historis tetap tersimpan (read-only bila paket masih mengizinkan).

```
app/
  Modules/
    IntiAkademik/     → Siswa, Kelas, Mapel, Jadwal, Absensi, Nilai, TahunAjaran, Pengguna
    Keuangan/         → Tagihan SPP, pembayaran, kuitansi (add-on)
    Ppdb/             → Pendaftaran online (add-on)
    Erapor/           → Template & cetak rapor (add-on)
    Organisasi/       → Grup sekolah + konsolidasi (add-on)
    ...               → modul lain mengikuti kontrak yang sama
  Platform/           → Tenant, Billing, Modul registry (milik operator SaaS, bukan add-on)
```

Setiap modul punya migrasi, Policy, Service, komponen UI panel admin, API, dan test sendiri.

## 7. Kebutuhan Fungsional per Modul

> Format: **FR-XXX** = functional requirement. Setiap FR wajib punya validasi, audit log (siapa, kapan, apa yang berubah), dan mengikuti `school_id` aktif.

### 7.1 Data Siswa

**Deskripsi:** master data seluruh peserta didik per sekolah, menjadi acuan semua modul lain.

- **FR-101** CRUD data siswa: NIS, NISN, nama, jenis kelamin, tempat/tgl lahir, agama, alamat, no. HP, foto, status (aktif/pindah/lulus/keluar/DO).
- **FR-102** Setiap siswa terikat ke **satu sekolah** dan **satu rombel/kelas aktif** per tahun ajaran; riwayat kelas tersimpan (mutasi naik kelas).
- **FR-103** Import massal via Excel/CSV dengan template unduhan + validasi baris per baris (laporan baris gagal diunduh).
- **FR-104** Pencarian cepat (nama/NIS/NISN) dan filter (kelas, angkatan, status, jenis kelamin).
- **FR-105** Profil 360° siswa: biodata, wali, kelas & riwayat, kehadiran, nilai, catatan BK, prestasi — dalam satu halaman.
- **FR-106** Nonaktifkan (soft delete) siswa pindah/keluar; data historis nilai & absensi tetap tersimpan.
- **Kriteria penerimaan:** import 1.000 siswa ≤ 60 detik dengan laporan error per baris; NIS duplikat dalam satu sekolah ditolak.

### 7.2 Data Wali Murid / Orang Tua

**Deskripsi:** data orang tua/wali yang terhubung ke siswa dan menjadi akun PWA.

- **FR-201** CRUD wali: NIK, nama, hubungan (ayah/ibu/wali), pekerjaan, no. HP/WA, email, alamat.
- **FR-202** Relasi many-to-many siswa↔wali (satu anak bisa punya ayah+ibu; satu wali bisa punya banyak anak).
- **FR-203** Satu akun login PWA per wali dapat melihat **semua anaknya**, termasuk jika anak berada di sekolah (tenant) yang berbeda.
- **FR-204** Verifikasi no. WA (OTP) saat aktivasi akun PWA.
- **Kriteria penerimaan:** wali dengan 3 anak di 2 sekolah melihat ketiganya dalam satu dashboard PWA.

### 7.3 Data Guru dan Pegawai

**Deskripsi:** master data SDM sekolah: guru, tendik, dan pegawai lainnya.

- **FR-301** CRUD: NIP/NUPTK, nama, jenis (guru mapel/guru kelas/tendik), status kepegawaian (PNS/PPPK/honorer), jabatan, mata pelajaran ampuan, foto, status aktif.
- **FR-302** Penugasan mengajar: guru ↔ mapel ↔ kelas (banyak-ke-banyak per tahun ajaran).
- **FR-303** Data wali kelas: satu rombel punya tepat satu wali kelas aktif per tahun ajaran.
- **FR-304** Riwayat penugasan & mutasi antar sekolah (membutuhkan add-on **Organisasi**).
- **FR-305** Import Excel seperti FR-103.
- **Kriteria penerimaan:** bentrok penugasan (guru sama di 2 kelas pada jam yang sama) terdeteksi saat penyusunan jadwal.

### 7.4 Data Kelas dan Mata Pelajaran

- **FR-401** CRUD rombongan belajar (rombel): tingkat (X/XI/XII atau 1–6), nama kelas (mis. XII IPA 1), kapasitas, wali kelas, tahun ajaran.
- **FR-402** CRUD mata pelajaran: kode, nama, kelompok (wajib/peminatan/mulok), KKM default per mapel.
- **FR-403** Struktur kurikulum per tingkat: daftar mapel wajib per jenjang (Merdeka/K13 dapat dikonfigurasi).
- **FR-404** Kenaikan kelas massal (promosi rombel) di akhir tahun ajaran dengan pratinjau sebelum eksekusi.
- **Kriteria penerimaan:** promosi 500 siswa antar rombel dapat dipratinjau dan dibatalkan sebelum disimpan permanen.

### 7.5 Jadwal Pelajaran

- **FR-501** Susun jadwal mingguan per rombel: hari, jam ke-, mapel, guru pengajar, ruang (opsional).
- **FR-502** **Deteksi bentrok otomatis**: guru ganda di jam sama, kelas ganda di jam sama, ruang ganda.
- **FR-503** Template jam pelajaran per sekolah (mis. jam ke-1 = 07.00–07.45) yang bisa beda tiap sekolah.
- **FR-504** Jadwal khusus: pergantian guru (tugas), jadwal ujian, jadwal piket — tanpa merusak jadwal reguler.
- **FR-505** Tampilan jadwal: per kelas (dinding), per guru, per hari; cetak PDF.
- **FR-506** Perubahan jadwal memicu notifikasi ke guru & siswa terkait (via PWA/push).
- **Kriteria penerimaan:** sistem menolak menyimpan jadwal yang bentrok dengan pesan yang jelas (siapa bentrok dengan siapa, jam berapa).

### 7.6 Absensi Siswa

- **FR-601** Input absensi harian per rombel oleh wali kelas/guru piket: Hadir, Izin, Sakit, Alpa (+ keterangan).
- **FR-602** Absensi per mapel oleh guru (opsional, untuk jenjang SMA/SMK).
- **FR-603** Mode cepat: tandai semua hadir lalu koreksi yang tidak hadir; riwayat perubahan tercatat.
- **FR-604** Kunci absensi: setelah dikunci (per tanggal), hanya admin yang bisa membuka dengan alasan tercatat.
- **FR-605** Rekap otomatis: per siswa/bulan/semester, persentase kehadiran, daftar siswa dengan alpa ≥ N kali (peringatan dini).
- **FR-606** Notifikasi otomatis ke orang tua via WA/push jika anak **Alpa/Tidak hadir tanpa keterangan** (maks. 30 menit setelah absensi dikunci).
- **Kriteria penerimaan:** input absensi 36 siswa ≤ 2 menit; notifikasi alpa terkirim ≤ 30 menit.

### 7.7 Nilai Siswa

- **FR-701** Struktur penilaian konfigurabel: komponen (tugas, UH, PTS, PAS, dll.) dengan bobot per mapel; mendukung Kurikulum Merdeka (TP) & K13 (KD).
- **FR-702** Input nilai oleh guru mapel hanya untuk kelas/mapel ampuannya (otorisasi ketat).
- **FR-703** Import nilai dari Excel per mapel/kelas dengan validasi rentang (0–100).
- **FR-704** Perhitungan otomatis: nilai akhir = Σ(komponen × bobot); konversi ke predikat & deskripsi rapor.
- **FR-705** Kunci periode nilai per semester; pembukaan kunci butuh persetujuan kepala sekolah + alasan.
- **FR-706** Leger nilai per kelas & per mapel; peringkat opsional (dapat dimatikan per sekolah).
- **FR-707** Nilai tampil di PWA siswa/orang tua **hanya setelah** dirilis resmi oleh wali kelas/kepala sekolah.
- **Kriteria penerimaan:** rekap nilai akhir 1 rombel (36 siswa × 12 mapel) terhitung < 10 detik; guru tidak bisa input nilai kelas yang bukan ampuannya.

### 7.8 Manajemen Tahun Ajaran

- **FR-801** CRUD tahun ajaran (mis. 2026/2027) + semester (ganjil/genap) dengan tanggal mulai–selesai.
- **FR-802** Satu tahun ajaran aktif per sekolah; penggantian tahun ajaran butuh konfirmasi + snapshot.
- **FR-803** Penanggalan akademik: hari libur nasional, jeda semester, pekan ujian — dipakai validasi jadwal & absensi.
- **FR-804** Tutup tahun ajaran: arsipkan data (read-only), siapkan struktur tahun baru (salin mapel, rombel kosong).
- **Kriteria penerimaan:** data tahun ajaran yang sudah ditutup tidak bisa diubah kecuali oleh Platform Admin dengan audit khusus.

### 7.9 Pengelolaan Pengguna

- **FR-901** CRUD akun untuk semua peran §5; username = NIP/NIS/email; reset password oleh admin.
- **FR-902** Penetapan peran & permission granular (Spatie); satu user bisa punya >1 peran (mis. guru + wali kelas).
- **FR-903** Kebijakan password: min. 8 karakter, kombinasi huruf+angka; kunci akun setelah 5x gagal login.
- **FR-904** 2FA (TOTP) **WAJIB** untuk seluruh peran level platform sejak go-live (Sprint 9); **dianjurkan kuat** untuk Admin Sekolah.
- **FR-905** Log aktivitas login (waktu, IP, perangkat) + peringatan login dari perangkat baru.
- **FR-906** Impersonasi (*login as*) oleh Support/CS atau Platform Admin untuk troubleshooting, dengan banner jelas & audit.
- **Kriteria penerimaan:** user tanpa permission `nilai.input` mendapat 403 saat mengakses endpoint nilai.

### 7.10 Laporan Data Sekolah

- **FR-1001** Laporan bawaan: data siswa, guru, rekap absensi, leger nilai, daftar kelas, mutasi siswa.
- **FR-1002** Export Excel & PDF dengan **kop surat & logo per sekolah** otomatis.
- **FR-1003** Laporan konsolidasi **Organisasi** (add-on): perbandingan antar sekolah dalam satu organisasi (jumlah siswa, rata-rata kehadiran, rata-rata nilai).
- **FR-1004** *Report builder* sederhana: pilih kolom + filter + simpan sebagai template laporan.
- **FR-1005** Semua export besar (>5.000 baris) diproses via queue dengan notifikasi unduhan siap.
- **Kriteria penerimaan:** export 10.000 baris tidak menyebabkan timeout; file tersedia via notifikasi.

### 7.11 Dashboard Informasi

- **FR-1101** Dashboard per peran: KPI relevan (jml siswa/guru, % kehadiran hari ini, rata-rata nilai, siswa bermasalah).
- **FR-1102** Dashboard **Organisasi** (add-on): agregat sekolah-sekolah dalam satu organisasi + tren per semester + perbandingan.
- **FR-1103** Widget pengumuman & agenda sekolah; kalender akademik mini.
- **FR-1104** Peringatan dini: daftar siswa alpa tinggi, nilai di bawah KKM, guru belum input nilai/absensi (deadline).
- **Kriteria penerimaan:** dashboard staf termuat ≤ 2 detik (data diagregasi via cache/job, bukan query live berat).

### 7.12 Responsive Tampilan Web

- **FR-1201** Seluruh halaman staf usable di layar ≥ 360px (HP), ≥ 768px (tablet), desktop.
- **FR-1202** Tabel data besar memakai paginasi server-side + pencarian; tidak ada load 10.000 baris sekaligus.
- **FR-1203** Mode cetak rapi untuk laporan & jadwal (print CSS).
- **FR-1204** Dukungan Bahasa Indonesia penuh; kesiapan multi-bahasa (file lang) untuk ekspansi.
- **Kriteria penerimaan:** lolos audit Lighthouse mobile ≥ 85 untuk halaman utama; tidak ada scroll horizontal pada 360px.

### 7.13 PWA untuk Siswa dan Orang Tua

- **FR-1301** Instalasi *Add to Home Screen*; ikon & splash screen per sekolah (branding dinamis via manifest).
- **FR-1302** Fitur siswa: jadwal hari ini, absensi saya (riwayat + %), nilai per mapel (setelah dirilis), pengumuman, agenda.
- **FR-1303** Fitur orang tua: ringkasan semua anak (kehadiran hari ini, nilai terbaru, pengumuman), riwayat per anak.
- **FR-1304** Push notification: anak alpa, nilai dirilis, pengumuman penting, pengingat jadwal.
- **FR-1305** Mode offline: jadwal & nilai terakhir tersimpan (cache) tetap bisa dibuka tanpa internet; banner "mode offline".
- **FR-1306** Login aman: nomor WA + OTP atau username+password; sesi perangkat dapat dicabut dari web staf.
- **FR-1307** Ukuran ringan: *first load* ≤ 1,5 MB; bekerja baik di HP RAM 2 GB / jaringan 3G.
- **Kriteria penerimaan:** terinstal dari Chrome Android tanpa Play Store; push diterima dalam ≤ 60 detik; halaman jadwal terbuka offline setelah 1x kunjungan online.

## 8. Katalog Modul (Add-on)

Modul inti pada §7 selalu aktif untuk semua pelanggan. Di bawah ini **katalog add-on** yang diaktifkan per sekolah via *feature flag* (§6.5) sesuai paket langganan. **Rekomendasi 3 prioritas tertinggi: Keuangan/SPP, PPDB Online, dan E-Rapor.**

| # | Modul | Deskripsi Singkat | Model Lisensi | Prasyarat | Prioritas | Alasan Bisnis |
|---|---|---|---|---|---|---|
| 1 | **Keuangan & Pembayaran SPP** | Tagihan SPP & biaya lain per siswa, VA bank/QRIS/e-wallet via payment gateway, kuitansi otomatis, tunggakan & reminder WA, laporan arus kas per sekolah | Termasuk Pro (add-on di Basic) | Inti Akademik | **Must** (Fase 2) | Pain point terbesar TU; otomasi menekan tunggakan |
| 2 | **PPDB Online** | Formulir pendaftaran online, upload berkas, seleksi/tes, pengumuman kelulusan, daftar ulang → otomatis jadi data siswa | Termasuk Pro (add-on di Basic) | Inti Akademik | **Must** (Fase 2) | Pintu masuk data; mengurangi antrean & berkas fisik |
| 3 | **E-Rapor & Cetak Rapor** | Template rapor Kurikulum Merdeka/K13 per jenjang, deskripsi otomatis, tanda tangan digital/QR verifikasi, cetak massal | Termasuk Pro (add-on di Basic) | Inti Akademik (Nilai) | **Must** (Fase 2) | Output wajib tiap semester; QR verifikasi cegah pemalsuan |
| 4 | **Tata Tertib & Poin Pelanggaran** | Katalog pelanggaran & bobot poin, input oleh guru/BK, akumulasi poin → surat peringatan otomatis ke ortu | Termasuk Pro (add-on di Basic) | Inti Akademik | Should | Disiplin terukur; transparan ke orang tua |
| 5 | **BK & Konseling** | Jadwal konseling, catatan hasil (akses terbatas konselor), rujukan | Termasuk Pro (add-on di Basic) | Inti Akademik | Should | Layanan kesiswaan profesional & rahasia |
| 6 | **Perpustakaan Digital** | Katalog buku, peminjaman/pengembalian + denda, OPAC sederhana untuk siswa | Termasuk Pro (add-on di Basic) | Inti Akademik | Should | Aset literasi sekolah |
| 7 | **Ekstrakurikuler & Prestasi** | Pendaftaran ekskul, absensi ekskul, bank prestasi siswa (sertifikat) | Termasuk semua paket | Inti Akademik | Should | Portofolio siswa untuk SNBP/lanjut studi |
| 8 | **LMS Mini / Tugas Online** | Guru mengunggah materi, memberi tugas & kuis sederhana, pengumpulan via PWA | Termasuk Pro (add-on di Basic) | Inti Akademik | Should | Pembelajaran blended tanpa aplikasi terpisah |
| 9 | **Notifikasi Terjadwal** | Broadcast pengumuman tersegmentasi (per kelas/angkatan), template WA, riwayat terkirim | Termasuk Pro (add-on di Basic; kuota per paket) | Inti Akademik | Should | Komunikasi resmi sekolah, mengurangi grup WA liar |
| 10 | **Inventaris & Aset** | Data barang/aset, kondisi, lokasi, peminjaman, penyusutan sederhana | Termasuk Pro (add-on di Basic) | Inti Akademik | Could | Tertib administrasi BOS |
| 11 | **UKS & Kesehatan** | Rekam kunjungan UKS, riwayat alergi/penyakit, surat izin sakit digital | Termasuk Pro (add-on di Basic) | Inti Akademik | Could | Keselamatan & data kesehatan siswa |
| 12 | **Antar-Jemput / Transportasi** | Rute & armada, absensi naik/turun, notifikasi ke ortu saat anak naik/turun bus | Termasuk Pro (add-on di Basic) | Inti Akademik | Could | Nilai jual untuk sekolah dengan layanan antar-jemput |
| 13 | **Kantin/Koperasi Digital** | Saldo e-money siswa, transaksi kantin via QR, limit harian oleh ortu | Termasuk Pro (add-on di Basic) | Inti Akademik | Could | Edukasi finansial + kontrol jajan oleh ortu |
| 14 | **Survei & Evaluasi** | Survei kepuasan ortu/siswa, evaluasi guru oleh siswa (anonim) | Termasuk Pro (add-on di Basic) | Inti Akademik | Could | Umpan balik mutu layanan |
| 15 | **Alumni & Karier** | Database alumni, tracer study, info lowongan/beasiswa | Termasuk Pro (add-on di Basic) | Inti Akademik | Won't (nanti) | Engagement jangka panjang |
| 16 | **Organisasi (grup sekolah)** | Mengelompokkan beberapa sekolah (mis. yayasan), peran Admin Organisasi, laporan & dashboard konsolidasi lintas sekolah | Add-on (per organisasi) | Inti Akademik | Should (Fase 3) | Menggantikan konsep "yayasan terpusat" — konsolidasi menjadi fitur opsional berbayar |

Setiap modul mengikuti kontrak §6.5: ServiceProvider sendiri, migrasi terpisah, permission namespaced `{modul}.{resource}.{aksi}`, dan *feature flag* per tenant. Modul yang non-aktif: menu & route disembunyikan, data historis tetap tersimpan (read-only).

## 9. Model Bisnis SaaS

### 9.1 Paket Langganan

Harga dihitung **flat per sekolah per bulan** — *keputusan produk 9 Oktober 2026* (merujuk riset pasar & rasional di Dok 10). Modelnya dua tier sederhana:

- **Basic** = modul inti + plugin **à la carte** sesuai kebutuhan (bayar per modul yang dipakai).
- **Pro** = **all-inclusive**: seluruh 16 modul katalog termasuk, tanpa pilih-pilih.
- **Enterprise** = kustom (kontrak tahunan: dedicated DB, CSM, SSO, domain kustom).

Logika upsell: Basic (Rp 499 rb) + 1 modul besar (mis. Keuangan Rp 499 rb) ≈ harga Pro (Rp 999 rb) — sekolah yang butuh ≥2 modul otomatis lebih hemat naik ke Pro.

| | **Basic** | **Pro** ⭐ | **Enterprise** |
|---|---|---|---|
| Harga | **Rp 499.000**/bln (Rp 4.990.000/thn) | **Rp 999.000**/bln (Rp 9.990.000/thn) | Mulai **Rp 2.500.000**/bln (kustom, kontrak tahunan) |
| Maks. siswa aktif | 300 | 1.500 | Tanpa batas |
| Maks. user staf | 20 | 75 | Tanpa batas |
| Storage | 10 GB | 50 GB | 200 GB+ |
| Modul | Inti Akademik (§7) + Ekstrakurikuler; modul lain **à la carte** (lihat §9.2) | **Semua 16 modul katalog termasuk** (all-inclusive) | Semua modul + kustom |
| Domain | `{sekolah}.sims.id` | `{sekolah}.sims.id` | + domain kustom (CNAME) |
| Database | Shared | Shared | Dedicated (opsional) |
| SLA uptime | 99% | 99,5% | 99,9% |
| Support | Email (2×24 jam) | WA prioritas (1×24 jam) | Dedicated CSM |

- **Tier Rintisan** (varian Basic): Rp 299.000/bln, kuota 100 siswa, modul à la carte sama — untuk sekolah <100 siswa yang sensitif harga.
- Harga tahunan = 10× bulanan (gratis 2 bulan).
- **Kebijakan "all-inclusive" Pro**: mencakup seluruh modul di katalog §8; modul baru yang dirilis kemudian otomatis termasuk dalam Pro (menjadi nilai tambah retensi), kecuali modul yang eksplisit ditandai Enterprise-only (butuh infra khusus).

### 9.2 Katalog Add-on (untuk tier Basic/Rintisan — tier Pro & Enterprise sudah termasuk semua)

| Add-on | Harga | Model |
|---|---|---|
| Keuangan & SPP (di Basic) | Rp 499.000/bln | Bulanan |
| PPDB Online | Rp 750.000/musim | Per periode PPDB (termasuk di Pro) |
| E-Rapor (di Basic) | Rp 299.000/bln | Bulanan — harga rendah vs e-Rapor Kemendikbud gratis; jual nilai tambah (QR verifikasi, deskripsi otomatis) |
| LMS Mini | Rp 349.000/bln | Bulanan |
| Perpustakaan Digital | Rp 249.000/bln | Bulanan |
| Notifikasi Terjadwal | Rp 199.000/bln + kuota | Bulanan; di atas kuota paket Rp 150/pesan |
| Tata Tertib / BK / Survei (di Basic) | Rp 149.000/bln per modul | Bulanan |
| Inventaris / UKS / Transportasi / Kantin / Alumni | Rp 149–249.000/bln per modul | Bulanan |
| **Organisasi** (grup sekolah) | Rp 499.000/bln | Per organisasi, mencakup semua sekolahnya |

### 9.3 Masa Trial 30 Hari

- Fitur setara paket **Pro**, tanpa kartu kredit; aktivasi otomatis setelah verifikasi email + WA.
- **Satu trial per sekolah** — dideduplikasi via NPSN + domain; percobaan trial berulang otomatis ditolak.
- **Batas trial (anti-abuse)** — trial adalah evaluasi, bukan pemakaian gratis tanpa batas:

| Batas trial | Nilai | Keterangan |
|---|---|---|
| Maks. siswa aktif | 100 | Cukup untuk evaluasi; cegah eksploitasi |
| Maks. user staf | 5 | |
| Storage | 1 GB | |
| Pesan WA/bulan | 200 | Cegah spam broadcast |
| Broadcast massal | ❌ Nonaktif | Dibuka setelah berlangganan |
| API / webhook keluar | ❌ Nonaktif | |
| Domain kustom | ❌ Nonaktif | |
| Masa berlaku | 30 hari kalender | Tidak dapat diperpanjang; trial kedua tidak diberikan |

- Pengingat H-7, H-3, H-1 sebelum trial berakhir (email + WA ke Admin Sekolah).
- Trial berakhir tanpa upgrade → mode **read-only 14 hari** (kesempatan upgrade atau ekspor data) → *suspended*.

### 9.4 Dunning, Grace Period & Suspend

1. Invoice terbit otomatis H-7 sebelum jatuh tempo; pengingat H-3, H+1, H+7 via email + WA.
2. H+8: status `past_due` + banner peringatan di dashboard; H+15: grace period berakhir → status `suspended` (seluruh akses tulis dibekukan; data read-only).
3. H+90: kontrak dianggap berakhir → status `cancelled` → masuk siklus retensi §17.6 (read-only 14 hari untuk ekspor/reaktivasi → soft-delete → purge T+30). Tidak ada lagi 'penghapusan setelah konfirmasi tertulis' sebagai jalur terpisah — satu state machine berlaku untuk semua.
4. Reaktivasi: pelunasan tunggakan → layanan aktif kembali otomatis ≤ 1 jam.

### 9.5 Kuota & Fair Use

- Kuota dihitung dari **siswa berstatus aktif**; melewati kuota memicu penawaran upgrade (peringatan di 80% & 100%), bukan pemblokiran mendadak.
- Pemakaian storage & pengiriman WA diukur per tenant dan tampil di dashboard Admin Sekolah.
**Matriks enforcement kuota** — setiap metrik punya perlakuan soft (peringatan) dan hard (penolakan):

| Metrik | Soft limit (80% & 100%: banner + WA ke Admin Sekolah) | Hard limit (tercapai) |
|---|---|---|
| Siswa aktif | Tawarkan upgrade | Tolak tambah siswa baru (422 `KUOTA_PENUH`) |
| User staf | Tawarkan upgrade | Tolak tambah user baru |
| Storage | Tawarkan upgrade / hapus file lama | Tolak upload baru |
| Pesan WA/bulan | Notifikasi pemakaian | Pesan diantrekan hingga reset bulanan / upgrade |
| Rate limit API | — | 429 otomatis |
| Perangkat PWA per akun | — | Tolak perangkat baru hingga ada yang dicabut |

**Override kuota per tenant** — Platform Admin (permission `tenant.quota.override`) dapat memberikan batas kustom sementara untuk satu sekolah (mis. dispensasi PPDB, pilot khusus): wajib mencantumkan alasan + tanggal kedaluwarsa, tercatat di audit log platform, dan otomatis kembali ke batas paket saat kedaluwarsa. Override tidak dapat memperpanjang masa trial.

- Kebijakan anti-abuse: tenant trial dibatasi (maks. 100 siswa, rate limit ketat, tanpa broadcast WA massal) untuk mencegah spam/penyalahgunaan.

**Rate limit API per tier (fair use)** — menutup gap T-04:

| Tier | Rate limit API | Catatan |
|---|---|---|
| Basic | 60 req/menit per token | Cukup untuk operasional harian sekolah kecil |
| Pro | 300 req/menit per token | Mendukung integrasi webhook & trafik PPDB |
| Enterprise | Kustom (disepakati di kontrak) | Termasuk burst allowance saat periode puncak |
| Platform (internal) | 30 req/menit + IP allowlist | Sesuai Dok 03 §8 |

Pelanggaran berulang memicu 429 + notifikasi ke Admin Sekolah; upgrade tier menaikkan limit otomatis.

## 10. Kebutuhan Non-Fungsional

| Kategori | Kebutuhan |
|---|---|
| **Kinerja** | Halaman daftar ≤ 2 dtk (p95); dashboard ≤ 2 dtk via cache; API PWA ≤ 800 ms; mendukung 500 pengguna konkuren per app server |
| **Skalabilitas** | Arsitektur siap scale horizontal; job berat via queue; target 50 sekolah / 50.000 siswa di 1 cluster awal |
| **Ketersediaan** | Uptime ≥ 99,5%/bulan; maintenance window diumumkan H-3 |
| **Keamanan** | Enkripsi TLS 1.2+; password di-hash (bcrypt/argon2); proteksi CSRF/XSS/SQLi bawaan Laravel; rate limit login & API; 2FA untuk admin; data PII (NIK) terenkripsi at-rest (opsional kolom sensitif) |
| **Privasi** | Akses data berbasis peran; orang tua hanya melihat anaknya; log akses data sensitif; retensi & hapus data sesuai kebijakan sekolah |
| **Audit** | Semua create/update/delete + login + export tercatat (user, waktu, IP, before/after); retensi log ≥ 2 tahun |
| **Backup** | DB full harian, retensi 30 hari; RPO ≤ 24 jam, RTO ≤ 4 jam; uji restore tiap kuartal |
| **Kualitas kode** | PSR-12, static analysis (Larastan level ≥ 5), coverage test ≥ 70% untuk domain kritis (nilai, absensi, auth) |
| **Aksesibilitas** | Kontras & ukuran font memadai; navigasi keyboard untuk form utama |
| **Dokumentasi** | API docs (Scribe/Scramble), runbook deploy & restore, panduan per peran (PDF/video singkat) |

## 11. Model Data — Ringkasan Entitas Inti

```
schools (id, nama, npsn, jenjang, logo, kop_surat, timezone, konfigurasi json)
academic_years (id, school_id, nama mis. 2026/2027, tgl_mulai, tgl_selesai, aktif)
semesters (id, academic_year_id, nama ganjil/genap, tgl_mulai, tgl_selesai, aktif)
users (id, school_id nullable → null = akun level platform, username, password, tipe)
roles/permissions (spatie) + model_has_roles

students (id, school_id, nis, nisn, nama, jk, ttl, alamat, foto, status)
guardians (id, school_id, nik, nama, hubungan, no_wa, email)
guardian_student (guardian_id, student_id, hubungan)

employees (id, school_id, nip/nuptk, nama, jenis guru/tendik, status_kepegawaian, jabatan)
subjects (id, school_id, kode, nama, kelompok, kkm_default)
school_classes / rombels (id, school_id, academic_year_id, tingkat, nama, kapasitas, wali_kelas_id)
class_student (riwayat kelas siswa per tahun ajaran)
teaching_assignments (employee_id, subject_id, rombel_id, academic_year_id)

schedules (id, school_id, rombel_id, hari, jam_ke, subject_id, employee_id, ruang, academic_year_id)
attendances (id, school_id, student_id, rombel_id, tanggal, status H/I/S/A, keterangan, input_by)
grade_components (id, school_id, subject_id, nama mis. UH/PTS, bobot)
grades (id, school_id, student_id, subject_id, component_id, semester_id, nilai)
grade_releases (semester_id, rombel_id, dirilis_pada, dirilis_oleh) → pengunci tampil di PWA

announcements (id, school_id, judul, isi, target peran/kelas, tgl_tayang)
audit_logs (id, school_id, user_id, aksi, model, before, after, ip, waktu)
```

**Entitas SaaS (level platform):**

```
organizations (id, nama, tipe yayasan/perusahaan/perorangan, kontak, status)
organization_school (organization_id, school_id, peran) → grup sekolah opsional

plans (id, kode basic/pro/enterprise, nama, harga_per_siswa, kuota json, fitur json)
modules (key unique, nama, versi, deskripsi, prasyarat json, harga_addon, aktif_global)
tenant_modules (id, school_id, module_key, aktif, config json, diaktifkan_pada)
  → feature flag per tenant; modul Inti selalu aktif

subscriptions (id, school_id, plan_id, status trial/active/past_due/suspended/cancelled,
  mulai, berakhir, trial_berakhir)
invoices (id, school_id, subscription_id, nomor unique, periode, total, status,
  jatuh_tempo, dibayar_pada)
tenant_usages (school_id, metrik mis. siswa_aktif/storage_mb/pesan_wa, nilai, periode; batas kuota diambil dari plans)
```

Catatan relasi:
- `schools` += `organization_id` (nullable), `subdomain` (unique), `custom_domain` (nullable).
- `users.school_id` = null → akun **level platform** (Platform Admin/Support/Finance); terisi → akun tenant.
- Permission platform (`platform.*`, mis. `platform.tenant.suspend`) terpisah dari permission tenant (`{modul}.{resource}.{aksi}`); satu user tidak memegang keduanya kecuali Platform Admin.
- Relasi kunci: satu `school_id` di hampir semua tabel operasional → fondasi isolasi tenant.

## 12. Integrasi & API

- **API internal PWA:** REST JSON via Sanctum (`/api/v1/...`): auth OTP, jadwal, absensi, nilai, pengumuman, profil. Versioning v1; rate limit 60/menit per token.
- **Payment gateway (Fase 2):** Midtrans/Xendit untuk VA, QRIS, e-wallet **+ recurring/subscription** (penagihan langganan otomatis); webhook terverifikasi signature; dunning & suspend terpicu dari status invoice.
- **WhatsApp gateway:** provider terpisah (Fonnte/Wablas/dll.) via adapter — ganti provider tanpa ubah kode bisnis.
- **Push notification:** Firebase Cloud Messaging untuk PWA.
- **Dapodik/EMIS (Fase 2):** modul sinkronisasi impor/ekspor format Dapodik; API disiapkan sejak MVP.
- **SSO opsional:** Google/Microsoft login untuk guru (fase lanjutan).

## 13. Detail Teknis PWA

- **Manifest dinamis per sekolah:** nama, ikon, warna tema diambil dari konfigurasi `schools` → setiap sekolah terasa punya "aplikasi sendiri".
- **Service worker (Workbox):** strategi *stale-while-revalidate* untuk API jadwal/nilai/pengumuman; *cache-first* untuk aset statis; halaman fallback offline.
- **Push:** langganan per perangkat tersimpan di `push_subscriptions` (user_id, endpoint, key); pengiriman via queue agar tidak memblokir request.
- **Keamanan:** token Sanctum dengan masa berlaku + refresh; *device management* (cabut sesi per perangkat); tidak menyimpan password di local storage.
- **Update:** banner "versi baru tersedia" saat service worker diperbarui; update tanpa kehilangan cache data penting.

## 14. Roadmap / Fase Pengembangan

| Fase | Durasi (estimasi) | Lingkup |
|---|---|---|
| **Fase 0 — Fondasi SaaS** | Sprint 1–2 (minggu 1–4) | Setup Laravel 13, multi-tenant (`school_id` + scope) + resolusi subdomain, **registrasi tenant mandiri + provisioning otomatis**, paket & subscription, **feature flag modul + kontrak modul (§6.5)**, preset jenjang (SD/SMP/SMA/SMK), RBAC 2 lapis (platform vs tenant), audit log, CI/CD, design system, API v1 skeleton |
| **Fase 1 — MVP Akademik** | Sprint 3–12 (minggu 5–24) | §7.1–7.12 (web staf responsif) + PWA §7.13; trial billing manual/terbatas; pilot 2–3 sekolah (trial gratis). Total 12 sprint / 24 minggu sampai pilot stabil (re-baseline pasca-audit, Dok 15) |
| **Fase 2 — Monetisasi** | 8–10 minggu | **Billing penuh**: invoice otomatis, payment gateway recurring, dunning, grace period, suspend otomatis; **marketplace modul**: Keuangan/SPP, PPDB Online, E-Rapor; integrasi Dapodik |
| **Fase 3 — Ekspansi** | 6–8 minggu | Modul add-on **Organisasi** (konsolidasi multi-sekolah); modul #4–#9 (tata tertib, BK, perpus, ekskul, LMS mini, broadcast); SSO; report builder lanjutan |
| **Fase 4 — Skala** | berjalan | Modul #10–#15, tier Enterprise (dedicated DB, custom domain), optimasi performa, SLA & CS operasional, akuisisi massal sekolah |

*Estimasi untuk tim 3–5 orang (1 BE, 1 FE (Livewire/Blade + design system), 1 mobile/PWA, 1 QA, 0.5 PM). Go-live per sekolah: registrasi mandiri (≤15 menit) atau migrasi data berbantuan (1–2 minggu) + pelatihan (3 hari) + pendampingan (2 minggu).*

## 15. Kriteria Penerimaan Global (Definition of Done per Fitur)

1. Kebutuhan fungsional terpenuhi & lolos skenario uji (happy path + 1 edge case).
2. Otorisasi: peran yang tidak berhak mendapat 403 di web & API.
3. Isolasi tenant: data sekolah A tidak pernah muncul di konteks sekolah B (diuji otomatis).
4. Audit log tercatat untuk setiap mutasi data.
5. Responsif: lolos cek 360px tanpa scroll horizontal; tabel besar server-side pagination.
6. Kode melewati CI (test + Larastan) dan code review.
7. Dokumentasi singkat cara pakai untuk peran terkait.
8. (Untuk modul add-on) Modul terdaftar di registry dengan manifest valid; *feature flag* aktif hanya untuk tenant yang berlangganan; tenant tanpa langganan mendapat 403 + pesan upgrade.

## 16. Asumsi, Ketergantungan & Risiko

**Asumsi**
- Setiap sekolah memiliki minimal 1 admin yang melek komputer untuk pelatihan.
- Data awal (siswa/guru) tersedia dalam Excel untuk migrasi.
- Konektivitas internet memadai di sekolah; PWA menoleransi internet putus-nyambung.
- Satu sekolah = satu langganan (tenant); harga paket final ditetapkan sebelum go-to-market.

**Ketergantungan**
- Ketersediaan WhatsApp gateway & payment gateway pihak ketiga (SLA mereka).
- Dukungan FCM/browser untuk push notification di iOS (keterbatasan Safari dicatat di panduan).

**Risiko & Mitigasi**

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Migrasi data kotor (NIS ganda, nama tidak konsisten) | Tinggi | Template + validasi import; tools deduplikasi; pendampingan TU |
| Penolakan pengguna (guru terbiasa manual) | Tinggi | UX ≤ 2 menit untuk tugas harian; pelatihan + champion per sekolah; PWA memberi nilai langsung ke ortu |
| Kebocoran data antar pelanggan (tenant) | Kritis | Triple-layer isolation + automated test tenant; audit log; review keamanan & pentest sebelum go-live |
| *Churn* pelanggan tinggi (berhenti berlangganan) | Tinggi | Onboarding ≤15 menit; *time-to-value* cepat (PWA ortu); CS proaktif untuk tenant tidak aktif; ekspansi add-on (NRR > 100%) |
| Gagal bayar / invoice menumpuk | Sedang | Dunning otomatis bertahap; grace period; suspend otomatis; opsi pembayaran fleksibel (transfer, VA, QRIS) |
| Abuse/spam dari tenant trial gratis | Sedang | Batasan trial (maks. 100 siswa, rate limit ketat, tanpa broadcast WA massal); verifikasi email/WA wajib |
| Lonjakan trafik (awal semester/PPDB) | Sedang | Queue untuk job berat; load test sebelum fase 2; autoscale app server |
| Ketergantungan provider WA/pembayaran (termasuk recurring) | Sedang | Adapter pattern; provider cadangan terdaftar; retry + rekonsiliasi webhook berkala |

## 17. Keamanan & Kepatuhan SaaS

Bagian ini menutup kebutuhan keamanan yang melekat pada model SaaS multi-tenant: satu insiden bisa berdampak ke banyak pelanggan, dan data yang dikelola adalah data anak (perlindungan lebih tinggi menurut UU PDP).

### 17.1 Threat Model (ringkas)

| Aset yang dilindungi | Ancaman utama | Mitigasi (rujukan) |
|---|---|---|
| Data siswa & wali (PII, data anak) | Akses lintas tenant; scraping massal via API | Isolasi 3 lapis (`school_id` scope + policy + validasi request); token tanpa `school_id` dari klien (Dok 03 §8); rate limit; audit akses sensitif |
| Kredensial pengguna | Credential stuffing, brute force | Lockout 5x gagal, kebijakan password, 2FA untuk peran platform & admin sekolah (FR-903/904) |
| Token API/PWA | Pencurian token dari perangkat | Token berumur + refresh, *device management* (cabut per perangkat), TLS wajib |
| Webhook pembayaran | Request palsu yang mengubah status langganan | Verifikasi HMAC signature; tanpa Bearer token (Dok 03 §8) |
| Penyalahgunaan tenant trial | Spam broadcast, scraping, resource abuse | Kuota trial ketat (maks. 100 siswa, tanpa broadcast massal), verifikasi email/WA wajib, monitoring anomali |
| Akses insider (support/CS) | Melihat/mengubah data tenant berlebihan | Guard terpisah, impersonasi tercatat + berlabel + kedaluwarsa (Dok 05 §5), *least privilege* |
| Supply chain | Dependensi bervulnerabilitas | `composer audit` + Dependabot di CI; lockfile; image scan |

### 17.2 Security Hardening Checklist (wajib per rilis mayor)

**Aplikasi**
- Security headers via middleware: CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy.
- Semua input divalidasi (Form Request); mass assignment hanya via `$fillable`; field sensitif di-`$hidden` pada API Resource.
- Kolom PII (NIK, dsb.) memakai cast `encrypted`; `APP_KEY` disimpan di vault/HSM dan dirotasi terjadwal (catatan: kolom terenkripsi tidak bisa di-WHERE).
- Cookie session: `secure`, `http_only`, `same_site=lax`; idle timeout 30 menit untuk panel staf.
- Upload file: validasi mime + ukuran, nama file acak, simpan di object storage (bukan web root); opsi scan antivirus untuk dokumen.
- 2FA **wajib** untuk seluruh peran level platform; dianjurkan untuk admin sekolah.

**Infrastruktur**
- WAF + proteksi DDoS (mis. Cloudflare) di depan aplikasi; TLS 1.2+ dengan HSTS.
- Database & Redis tidak terekspos ke internet; backup terenkripsi (at-rest).
- Secret/credential hanya via vault atau secret manager — **dilarang** di repo, log, atau chat.
- Rotasi kredensial operasional tiap 90 hari; akses SSH/DB berbasis identitas personal (tanpa akun bersama).

### 17.3 Deteksi & Monitoring Keamanan

- Log keamanan terpusat (terpisah dari log aplikasi): login gagal, rangkaian 403, aktivasi impersonasi, perubahan peran/permission, export massal, perubahan paket/langganan.
- Aturan alert otomatis, mis.: > 50 login gagal / 5 menit dari 1 IP → blokir sementara; 1 token mengakses pola scraping → cabut + investigasi.
- Dashboard keamanan untuk Platform Admin: anomali per tenant, status patch, umur sertifikat (termasuk custom domain).
- Audit akses data sensitif dapat ditampilkan per tenant saat diminta pelanggan (transparansi).

### 17.4 Jadwal Keamanan Rutin

| Kegiatan | Frekuensi |
|---|---|
| Dependency & container scan di CI (`composer audit`, image scan) | Setiap pipeline |
| Review ulang hak akses (recertification, selaras Dok 05 §7) | Tiap semester |
| Drill incident response | Tiap 6 bulan |
| Penetration test oleh pihak ketiga | Tahunan + setelah rilis modul pembayaran/perubahan auth |
| Review konfigurasi WAF, backup & restore | Tiap kuartal (restore drill) |

### 17.5 Incident Response Plan

| Tahap | Isi | SLA |
|---|---|---|
| 1. Deteksi & triase | Klasifikasi severity: **S1** = kebocoran data / akses lintas tenant terbukti; **S2** = percobaan masif / modul pembayaran terganggu; **S3/S4** = temuan non-kritis | S1 ≤ 1 jam, S2 ≤ 4 jam |
| 2. Kontainmen | Isolasi tenant/aplikasi terdampak; cabut token & kunci kredensial yang dicurigai; bekukan deploy | — |
| 3. Eradikasi & recovery | Perbaiki akar masalah; restore dari backup bila perlu; verifikasi ulang isolasi tenant sebelum dibuka | — |
| 4. Komunikasi | Notifikasi ke tenant terdampak **≤ 72 jam** (kewajiban UU PDP); laporan ke otoritas bila diwajibkan; status page publik | — |
| 5. Post-mortem | Dokumen akar masalah + aksi perbaikan; tanpa menyalahkan individu; dibagikan ke tim ≤ 7 hari | — |

Peran insiden: Incident Commander (Tech Lead), komunikasi pelanggan (PM/CS), forensik & recovery (BE/DevOps). Kontak darurat dan *runbook* disimpan di luar sistem utama (tetap aksesibel saat sistem down).

### 17.6 Retensi & Penghapusan Data Tenant (Offboarding)

**State machine tunggal** (berlaku untuk cancel, dunning H+90, maupun permintaan berhenti):

`trialing → active → past_due → read_only (14 hari: ekspor penuh + reaktivasi) → cancelled → purge_scheduled (soft-delete) → purged (T+30)` — dengan cabang `legal_hold` yang menghentikan purge hingga hold dicabut.


1. Tenant berhenti/cancel → status `cancelled` → mode **read-only 14 hari**: kesempatan ekspor penuh (CSV/Excel seluruh data + unduh dokumen) dan/atau reaktivasi.
2. Setelah 14 hari → job penghapusan permanen terjadwal: data operasional, file di storage, push subscription, token & sesi, modul add-on. Soft-delete terlebih dahulu, purge T+30 hari.
3. **Dikecualikan dari penghapusan** (kewajiban hukum): invoice & catatan pembayaran (retensi 10 tahun fiskal) dan ringkasan audit log **tanpa PII**.
4. **Sertifikat penghapusan** (*deletion certificate*) diterbitkan ke tenant + tercatat di audit level platform.
5. Permintaan hapus data individu (siswa/orang tua): alur verifikasi identitas → dieksekusi ≤ 30 hari, kecuali data yang wajib dipertahankan karena dokumen legal yang sudah terbit (mis. rapor yang sudah ditandatangani).

### 17.7 Kepatuhan Privasi — UU PDP No. 27/2022

- Dasar pemrosesan: kontrak langganan (data operasional) + persetujuan wali murid (data anak — kategori data spesifik, perlindungan lebih tinggi).
- Hak subjek data difasilitasi: akses, koreksi, hapus, dan tarik persetujuan via panel/PWA; SLA penanganan ≤ 30 hari.
- Prinsip minimasi: kumpulkan hanya yang dibutuhkan modul aktif; PII terenkripsi at-rest; akses berbasis peran + kebutuhan.
- Penunjukan penanggung jawab pelindungan data (DPO/pic) di level platform; pencatatan aktivitas pemrosesan (RoPA) per tenant.
- Kebijakan privasi & syarat layanan per versi tersimpan dan dapat diaudit pelanggan.

### 17.8 Kriteria Penerimaan Keamanan (tambahan untuk DoD §15)

1. Security scan otomatis di CI hijau (tidak ada temuan *critical/high* yang belum ditangani).
2. Tidak ada secret di repo/lingkungan build; security headers terpasang dan terverifikasi.
3. Test negatif isolasi tenant (coba akses data tenant lain → 403/404) hijau untuk web & API.
4. Checklist §17.2 terpenuhi dan terdokumentasi per rilis mayor.

## 18. Glosarium

- **Tenant/Sekolah:** satu pelanggan SaaS (satu sekolah = satu langganan); data terisolasi penuh antar tenant.
- **Organisasi:** grup opsional yang menaungi beberapa sekolah (mis. yayasan); konsolidasi datanya adalah add-on berbayar.
- **Modul/Add-on:** unit fitur terpisah yang diaktifkan per tenant via *feature flag*; modul inti selalu aktif, sisanya opsional berbayar.
- **Feature flag:** penanda aktif/nonaktif modul per tenant (tabel `tenant_modules`).
- **Dunning:** rangkaian pengingat tagihan otomatis sebelum/sesudah jatuh tempo hingga *suspend*.
- **MRR / NRR / Churn:** metrik SaaS — *monthly recurring revenue*, *net revenue retention*, tingkat berhenti berlangganan.
- **Rombel:** rombongan belajar (kelas fisik, mis. XII IPA 1).
- **KKM:** kriteria ketuntasan minimal per mapel.
- **PWA:** Progressive Web App — web yang bisa diinstal & berjalan seperti aplikasi native.
- **RBAC:** Role-Based Access Control.
- **Leger:** rekap nilai seluruh siswa dalam satu kelas/mapel.
- **Threat model:** pemetaan aset, ancaman, dan mitigasi keamanan sistem.
- **DPO:** *Data Protection Officer* — penanggung jawab pelindungan data pribadi.
- **RoPA:** *Record of Processing Activities* — catatan aktivitas pemrosesan data.
- **Deletion certificate:** bukti tertulis bahwa data tenant telah dihapus permanen.

## 19. Persetujuan

| Peran | Nama | Tanda Tangan & Tanggal |
|---|---|---|
| Product Owner | | |
| Tech Lead | | |
| Perwakilan Sekolah (Pelanggan) | | |

---

*Dokumen ini hidup (living document). Perubahan lingkup setelah persetujuan dicatat di changelog dengan versi baru.*
