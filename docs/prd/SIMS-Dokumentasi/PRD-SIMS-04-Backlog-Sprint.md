# Dok 04 — Backlog & Sprint Plan (Fase 0–1 / MVP)

| Atribut | Keterangan |
|---|---|
| Produk | SIMS Multi-Sekolah Terpusat |
| Dokumen induk | PRD v1.0 — `PRD-SIMS-Multi-Sekolah.md` |
| Cakupan | Fase 0 (Fondasi) + Fase 1 (MVP Akademik), selaras PRD §13 |
| Versi | 1.1 — 9 Oktober 2026 (revisi pivot SaaS) |

> **Pivot SaaS (v1.1):** SIMS adalah layanan **SaaS multi-tenant publik** — tiap sekolah adalah tenant pelanggan yang registrasi mandiri. Modul bersifat **add-on per tenant** (feature flag), ada paket langganan + billing, dan peran platform (operator/support/finance). Istilah "tenant" = sekolah pelanggan. Perubahan dari v1.0: tambah Epic E10–E14, sprint menjadi 10, sebagian billing (E12 penuh) dan organisasi grup sekolah (E14) dijadwalkan di Fase 2/3.

---

## 1. Asumsi Perencanaan

| Asumsi | Nilai |
|---|---|
| Komposisi tim | 5 orang: 1 BE, 1 FE (Livewire/Blade + design system), 1 PWA/API, 1 QA, 0.5 PM |
| Panjang sprint | 2 minggu |
| Velocity | ±40 story points/sprint (toleransi 35–44) |
| Total sprint Fase 0–1 | **12 sprint (±24 minggu)** — Sprint 1–2 = Fase 0, Sprint 3–12 = Fase 1 |
| Kapasitas dev tersedia | ±480 story points (12 × 40) |
| Scope MVP | **Must saja = 449 pts** (buffer ±31). Should (97) + Could (5) = **Fase 2 (pasca-pilot)** |
| Model bisnis | **SaaS multi-tenant publik**: tiap sekolah = tenant pelanggan; registrasi mandiri + trial 30 hari |
| Modul | Inti selalu aktif; modul tambahan = add-on per tenant (feature flag, E11) |
| Estimasi | Fibonacci: 1, 2, 3, 5, 8 |
| Prioritas | MoSCoW: Must / Should / Could / Won't |

**Catatan:** 3 item E9 (US-079, US-081, US-082) dikerjakan **track paralel PM/QA/DevOps** di Sprint 9–10, di luar velocity dev. Sprint 12 menyisakan buffer untuk bugfix UAT. Billing penuh (invoice, recurring, dunning — E12) dijadwalkan di **Fase 2**; MVP hanya mencakup paket + trial + kuota enforcement. Organisasi grup sekolah (E14) di **Fase 3**.

**Re-baseline pasca-audit (Dok 15, P1-04):** scope MVP = story Must (449 pts, 12 sprint). Story Should/Could berikut **dipindah ke Fase 2** (102 pts): US-063, US-072, US-079, US-088, US-093, US-096, US-097, US-098, US-099, US-104, US-108, US-109, US-110, US-111, US-112, US-114, US-115, US-116, US-117, US-121, US-127. (US-097/098 dikoreksi dari Must → Should agar konsisten dengan label Fase 2 di teks story-nya.) Pemetaan detail story→sprint direvisi di sprint planning.

---

## 2. Daftar Epic

**Legenda FR SaaS** (kebutuhan baru dari pivot, belum ada di PRD v1.0): `SaaS-01` registrasi & verifikasi tenant · `SaaS-02` provisioning otomatis · `SaaS-03` resolusi tenant & isolasi · `SaaS-04` paket, trial & kuota · `SaaS-05` sistem modul/feature flag · `SaaS-06` billing, invoice & dunning · `SaaS-07` operasional platform · `SaaS-08` organisasi grup sekolah.

| Epic | Nama | Tujuan | FR yang dicakup |
|---|---|---|---|
| **E1** | Fondasi multi-tenant, auth & RBAC | Satu aplikasi SaaS aman melayani banyak sekolah pelanggan dengan isolasi data per tenant | §6.2, FR-901–906, SaaS-03, SaaS-07 |
| **E2** | Data master: siswa, wali, guru/pegawai | Master data akurat sebagai fondasi semua transaksi | FR-101–106, 201–204, 301–305 |
| **E3** | Kelas, mapel & tahun ajaran | Struktur akademik per tenant per tahun ajaran | FR-401–404, 801–804 |
| **E4** | Jadwal pelajaran | Jadwal mingguan valid tanpa bentrok + API untuk PWA | FR-501–506 |
| **E5** | Absensi siswa | Absensi ≤2 menit/kelas + notifikasi alpa otomatis ke ortu | FR-601–606 |
| **E6** | Nilai siswa | Input → hitung → kunci → rilis nilai yang terkontrol | FR-701–707 |
| **E7** | Laporan & dashboard | Laporan operasional + dashboard per peran (konsolidasi lintas-sekolah pindah ke E14) | FR-1001–1002, 1004–1005, 1101–1104 |
| **E8** | PWA siswa/orang tua + notifikasi | Aplikasi mobile ringan: jadwal, absensi, nilai, push | FR-1301–1307, 204 |
| **E9** | Hardening, migrasi data & go-live pilot | Siap produksi: responsif, migrasi, runbook, UAT | FR-1201–1204, NFR §9 |
| **E10** | Fondasi SaaS & tenant | Registrasi mandiri, verifikasi, provisioning otomatis, resolusi tenant via subdomain | SaaS-01, SaaS-02, SaaS-03 |
| **E11** | Sistem modul (feature flags) | Modul sebagai add-on per tenant; menu & permission mengikuti modul aktif | SaaS-05 |
| **E12** | Billing & langganan | Paket, trial, invoice, recurring payment, dunning & suspend otomatis (penuh di Fase 2) | SaaS-04, SaaS-06 |
| **E13** | Operasional platform | Dashboard operator (MRR, churn), manajemen tenant & paket | SaaS-07 |
| **E14** | Organisasi grup sekolah (Fase 3) | Grup beberapa sekolah, admin organisasi, laporan konsolidasi | SaaS-08, FR-1003 |

---

## 3. User Stories

### E1 — Fondasi multi-tenant, auth & RBAC

| ID | Peran | User Story | Acceptance Criteria | FR ref | Pts | Prioritas |
|---|---|---|---|---|---|---|
| US-001 | Tech Lead | Sebagai tech lead, saya ingin setup proyek Laravel 13 + Docker + CI/CD agar tim develop & deploy konsisten | • Pipeline hijau (test+lint) ≤10 menit • `docker compose up` jalan di mesin baru ≤15 menit • Environment local/staging/prod terpisah | §6.1, §6.4 | 5 | Must |
| US-002 | BE | Sebagai BE, saya ingin struktur domain modular + static analysis agar kode konsisten | • Struktur `app/Domains/*` sesuai PRD §6.3 • CI gagal jika Larastan < level 5 • PSR-12 lolos | §6.3, §9 | 3 | Must |
| US-003 | BE | Sebagai BE, saya ingin tabel `schools` + kolom `school_id` + global scope agar data terisolasi per sekolah | • Semua tabel operasional punya `school_id` • Query tanpa konteks sekolah melempar exception • Automated test isolasi tenant hijau | §6.2 | 8 | Must |
| US-004 | Platform Support | Sebagai Platform Support, saya ingin masuk ke konteks tenant via impersonasi tercatat untuk troubleshooting | • Tanpa memakai password tenant • Banner impersonasi selalu tampil + auto-expire 30 menit • Semua aksi tercatat di audit log ganda (platform + tenant) | SaaS-07, §6.2 | 5 | Must |
| US-005 | Staf | Sebagai staf, saya ingin login/logout dengan kebijakan password & lockout agar akun aman | • Password min. 8 karakter huruf+angka • Terkunci 15 menit setelah 5x gagal • Semua percobaan gagal tercatat • Riwayat login (waktu, IP, perangkat) tercatat + peringatan perangkat baru | FR-903, 905 | 5 | Must |
| US-006 | Superadmin | Sebagai superadmin, saya ingin role & permission granular agar akses sesuai tugas | • 10 peran (PRD §5) terseed • Permission per aksi (mis. `nilai.input`) • 1 user bisa multi-peran | FR-901, 902 | 5 | Must |
| US-007 | Auditor | Sebagai auditor, saya ingin audit log setiap mutasi data agar perubahan terlacak | • Catat user, waktu, IP, before/after • Retensi ≥2 tahun • Riwayat tampil per record | §9 | 3 | Must |

### E2 — Data master: siswa, wali, guru/pegawai

| ID | Peran | User Story | Acceptance Criteria | FR ref | Pts | Prioritas |
|---|---|---|---|---|---|---|
| US-010 | Tata Usaha | Sebagai TU, saya ingin CRUD data siswa agar master peserta didik akurat | • NIS unik per sekolah (duplikat ditolak) • Foto ≤2 MB • Pencarian nama/NIS ≤1 detik untuk 10.000 data • Filter kelas/angkatan/status/JK • Status pindah/lulus/keluar = soft delete; histori nilai & absensi tetap tersimpan | FR-101, 104, 106 | 8 | Must |
| US-012 | Tata Usaha | Sebagai TU, saya ingin import Excel siswa agar migrasi cepat | • 1.000 baris ≤60 detik • Template unduhan tersedia • Laporan baris gagal dapat diunduh | FR-103 | 8 | Must |
| US-014 | Wali Kelas | Sebagai wali kelas, saya ingin profil 360° siswa dalam satu halaman | • Tab: biodata, wali, riwayat kelas, kehadiran, nilai • Termuat ≤2 detik • Riwayat kelas per tahun ajaran tampil | FR-105, 102 | 5 | Must |
| US-016 | Tata Usaha | Sebagai TU, saya ingin CRUD wali murid agar data orang tua terkelola | • NIK 16 digit tervalidasi • No. WA format E.164 | FR-201 | 5 | Must |
| US-017 | Tata Usaha | Sebagai TU, saya ingin relasi siswa↔wali many-to-many | • 1 anak bisa punya ayah+ibu; 1 wali bisa banyak anak • Hapus relasi tidak menghapus data induk | FR-202 | 3 | Must |
| US-018 | Tata Usaha | Sebagai TU, saya ingin CRUD guru/pegawai agar data SDM akurat | • NIP/NUPTK unik per sekolah • Filter jenis (guru/tendik) & status kepegawaian | FR-301 | 5 | Must |
| US-019 | Admin Sekolah | Sebagai admin, saya ingin penugasan mengajar (guru↔mapel↔kelas) per tahun ajaran | • 1 guru bisa banyak kelas • Histori penugasan per tahun ajaran tersimpan | FR-302 | 5 | Must |
| US-020 | Admin Sekolah | Sebagai admin, saya ingin tetapkan wali kelas | • Validasi: 1 rombel = 1 wali kelas aktif/tahun • Pergantian wali tercatat historis | FR-303 | 3 | Must |
| US-021 | Tata Usaha | Sebagai TU, saya ingin import Excel guru/pegawai | • 500 baris ≤60 detik • Laporan error per baris | FR-305 | 5 | Must |

### E3 — Kelas, mapel & tahun ajaran

| ID | Peran | User Story | Acceptance Criteria | FR ref | Pts | Prioritas |
|---|---|---|---|---|---|---|
| US-023 | Admin Sekolah | Sebagai admin, saya ingin CRUD tahun ajaran + semester | • Format 2026/2027; tgl mulai < tgl selesai • Semester ganjil/genap • 1 tahun ajaran aktif per sekolah (ganti butuh konfirmasi + snapshot) | FR-801, 802 | 5 | Must |
| US-025 | Admin Sekolah | Sebagai admin, saya ingin kalender akademik (libur/jeda/ujian) | • Hari libur dapat diinput massal • Absensi di hari libur diblokir • Jadwal di pekan ujian ditandai khusus | FR-803 | 5 | Must |
| US-026 | Admin Sekolah | Sebagai admin, saya ingin tutup tahun ajaran | • Data tahun tutup menjadi read-only (kecuali superadmin + audit khusus) • Struktur tahun baru terbentuk (mapel tersalin, rombel kosong) | FR-804 | 5 | Must |
| US-027 | Tata Usaha | Sebagai TU, saya ingin CRUD rombel | • Nama unik per tahun ajaran • Kapasitas default 40, dapat diubah | FR-401 | 5 | Must |
| US-028 | Tata Usaha | Sebagai TU, saya ingin CRUD mapel + KKM | • Kode unik per sekolah • KKM 0–100 • Kelompok: wajib/peminatan/mulok | FR-402 | 3 | Must |
| US-030 | Admin Sekolah | Sebagai admin, saya ingin kenaikan kelas massal dengan pratinjau | • Pratinjau 500 siswa sebelum eksekusi • Dapat dibatalkan sebelum simpan permanen • Eksekusi ≤2 menit | FR-404 | 8 | Must |

### E4 — Jadwal pelajaran

| ID | Peran | User Story | Acceptance Criteria | FR ref | Pts | Prioritas |
|---|---|---|---|---|---|---|
| US-032 | Admin Sekolah | Sebagai admin, saya ingin template jam pelajaran per sekolah | • Mis. jam ke-1 = 07.00–07.45 • Template dapat disalin ke tahun ajaran baru | FR-503 | 3 | Must |
| US-033 | Admin Sekolah | Sebagai admin, saya ingin susun jadwal mingguan per rombel | • Input 1 minggu/rombel ≤10 menit • Kolom: hari, jam ke-, mapel, guru, ruang | FR-501 | 8 | Must |
| US-034 | Admin Sekolah | Sebagai admin, saya ingin deteksi bentrok otomatis | • Jadwal bentrok (guru/kelas/ruang ganda) ditolak simpan • Pesan jelas: siapa bentrok dengan siapa, jam berapa • Pengecekan ≤2 detik | FR-502 | 8 | Must |
| US-036 | Guru | Sebagai guru, saya ingin lihat jadwal per kelas/guru/hari + cetak PDF | • 3 tampilan tersedia • Cetak rapi 1 halaman A4 | FR-505 | 5 | Must |
| US-038 | PWA Dev | Sebagai dev PWA, saya ingin API jadwal | • `GET /api/v1/jadwal` respons ≤800 ms • 401 tanpa token • Rate limit 60/menit | FR-501, §11 | 5 | Must |

### E5 — Absensi siswa

| ID | Peran | User Story | Acceptance Criteria | FR ref | Pts | Prioritas |
|---|---|---|---|---|---|---|
| US-039 | Wali Kelas | Sebagai wali kelas, saya ingin input absensi harian + mode cepat | • 36 siswa ≤2 menit • "Tandai semua hadir" lalu koreksi • Status H/I/S/A + keterangan • Riwayat perubahan tercatat | FR-601, 603 | 8 | Must |
| US-042 | Admin Sekolah | Sebagai admin, saya ingin kunci/buka absensi per tanggal | • Setelah dikunci, hanya admin yang bisa buka • Pembukaan butuh alasan tercatat | FR-604 | 5 | Must |
| US-043 | Kepsek/Wali Kelas | Sebagai kepsek, saya ingin rekap absensi + peringatan dini | • % kehadiran per siswa/bulan/semester • Daftar siswa alpa ≥3x/bulan • Rekap 1 semester ≤5 detik | FR-605 | 8 | Must |
| US-044 | Orang Tua | Sebagai ortu, saya ingin notifikasi otomatis jika anak alpa | • Terkirim ≤30 menit setelah absensi dikunci • Via WA/push sesuai preferensi • Hanya untuk status Alpa/tanpa keterangan | FR-606 | 8 | Must |
| US-045 | BE | Sebagai BE, saya ingin adapter notifikasi (WA gateway + FCM) + template per sekolah | • Ganti provider tanpa ubah kode bisnis • Pengiriman massal via queue • Log status terkirim/gagal per pesan | FR-606, §11 | 8 | Must |
| US-046 | PWA Dev | Sebagai dev PWA, saya ingin API absensi siswa | • Riwayat + % kehadiran • 403 jika meminta data bukan anaknya • Respons ≤800 ms | FR-601, §11 | 3 | Must |

### E6 — Nilai siswa

| ID | Peran | User Story | Acceptance Criteria | FR ref | Pts | Prioritas |
|---|---|---|---|---|---|---|
| US-047 | Admin Sekolah | Sebagai admin, saya ingin atur komponen & bobot penilaian per mapel | • Total bobot = 100% (divalidasi) • Mendukung mode TP (Merdeka) / KD (K13) | FR-701 | 5 | Must |
| US-048 | Guru Mapel | Sebagai guru, saya ingin input nilai hanya untuk kelas/mapel ampuan saya | • 403 jika bukan ampuan • Autosave per sel • Input 36 siswa × 5 komponen ≤5 menit | FR-702 | 8 | Must |
| US-049 | Guru Mapel | Sebagai guru, saya ingin import Excel nilai | • Validasi rentang 0–100 per sel • Baris gagal dilaporkan | FR-703 | 5 | Must |
| US-050 | Guru Mapel | Sebagai guru, saya ingin nilai akhir terhitung otomatis + predikat | • Nilai akhir = Σ(komponen × bobot) • Rekap 36 siswa × 12 mapel <10 detik • Konversi predikat + deskripsi rapor | FR-704 | 5 | Must |
| US-051 | Kepala Sekolah | Sebagai kepsek, saya ingin kunci/buka periode nilai dengan persetujuan | • Pembukaan kunci butuh alasan + approve kepsek • Tercatat di audit log | FR-705 | 5 | Must |
| US-052 | Wali Kelas | Sebagai wali kelas, saya ingin leger nilai per kelas/mapel | • Peringkat dapat dimatikan per sekolah • Cetak PDF | FR-706 | 5 | Must |
| US-053 | Wali Kelas | Sebagai wali kelas, saya ingin rilis nilai ke PWA | • Sebelum rilis, PWA tampil "belum dirilis" • Rilis per rombel per semester | FR-707 | 3 | Must |
| US-054 | PWA Dev | Sebagai dev PWA, saya ingin API nilai yang sudah dirilis | • Hanya nilai yang sudah dirilis yang keluar • 403 untuk data anak lain | FR-707, §11 | 3 | Must |

### E7 — Laporan & dashboard

| ID | Peran | User Story | Acceptance Criteria | FR ref | Pts | Prioritas |
|---|---|---|---|---|---|---|
| US-056 | Kepsek/Admin | Sebagai kepsek, saya ingin laporan bawaan (siswa, guru, kelas, mutasi) | • 6 laporan sesuai FR-1001 • Filter tahun ajaran/kelas/status | FR-1001 | 5 | Must |
| US-057 | Tata Usaha | Sebagai TU, saya ingin export Excel/PDF berkop sekolah | • Kop + logo otomatis per sekolah • PDF 1.000 baris ≤10 detik • Tampilan cetak rapi (print CSS) untuk laporan & jadwal | FR-1002, 1203 | 5 | Must |
| US-058 | Admin | Sebagai admin, saya ingin export besar via queue | • >5.000 baris diproses via queue • Notifikasi saat file siap diunduh • Tidak timeout | FR-1005 | 5 | Must |
| US-060 | Staf | Sebagai staf, saya ingin dashboard sesuai peran saya | • KPI relevan per peran • Termuat ≤2 detik (agregasi via cache/job) • Widget pengumuman, agenda, kalender mini | FR-1101, 1103 | 8 | Must |
| US-063 | Kepsek/Wali Kelas | Sebagai kepsek, saya ingin peringatan dini | • Daftar: siswa alpa tinggi, nilai <KKM, guru belum input nilai/absensi • Ambang batas dapat dikonfigurasi • Diperbarui harian | FR-1104 | 5 | Should |

### E8 — PWA siswa/orang tua + notifikasi

| ID | Peran | User Story | Acceptance Criteria | FR ref | Pts | Prioritas |
|---|---|---|---|---|---|---|
| US-064 | Siswa/Ortu | Sebagai pengguna, saya ingin install PWA dari browser | • "Add to Home Screen" di Chrome Android tanpa Play Store • Ikon, nama, warna tema per sekolah (manifest dinamis) | FR-1301 | 5 | Must |
| US-065 | Siswa/Ortu | Sebagai pengguna, saya ingin login via WA+OTP atau username+password | • OTP 6 digit kedaluwarsa ≤5 menit • Verifikasi no. WA saat aktivasi akun • Rate limit permintaan OTP | FR-1306, 204 | 5 | Must |
| US-067 | Siswa | Sebagai siswa, saya ingin lihat jadwal hari ini | • Termuat ≤2 detik • Tersedia offline setelah 1x kunjungan online | FR-1302 | 3 | Must |
| US-068 | Siswa | Sebagai siswa, saya ingin lihat riwayat & % kehadiran saya | • Per bulan/semester • Data maksimal H-1 | FR-1302 | 3 | Must |
| US-069 | Siswa | Sebagai siswa, saya ingin lihat nilai per mapel yang sudah dirilis | • Nilai belum dirilis tidak tampil • Tampil predikat + deskripsi | FR-1302, 707 | 3 | Must |
| US-070 | Orang Tua | Sebagai ortu, saya ingin dashboard semua anak saya, bahkan beda sekolah | • 3 anak di 2 sekolah tampil dalam 1 dasbor • Per anak: kehadiran hari ini, nilai terbaru, pengumuman | FR-1303 | 8 | Must |
| US-071 | Ortu/Siswa | Sebagai pengguna, saya ingin push notification | • Diterima ≤60 detik untuk: anak alpa, nilai dirilis, pengumuman penting • Pengiriman via queue • Dapat dinonaktifkan per kategori | FR-1304 | 5 | Must |
| US-072 | Siswa | Sebagai siswa, saya ingin buka jadwal & nilai saat offline | • Cache data terakhir tampil + banner "mode offline" • First load ≤1,5 MB; lancar di HP RAM 2 GB / 3G | FR-1305, 1307 | 5 | Should |
| US-073 | Admin Sekolah | Sebagai admin, saya ingin cabut sesi perangkat PWA yang hilang | • Daftar perangkat per user terlihat • Pencabutan berlaku ≤5 menit • Tidak menyimpan password di local storage | FR-1306 | 3 | Must |

### E9 — Hardening, migrasi data & go-live pilot

| ID | Peran | User Story | Acceptance Criteria | FR ref | Pts | Prioritas |
|---|---|---|---|---|---|---|
| US-075 | QA | Sebagai QA, saya ingin audit responsif seluruh halaman staf | • Tanpa scroll horizontal di 360 px • Lighthouse mobile ≥85 untuk halaman utama • Tabel besar memakai server-side pagination | FR-1201, 1202, 1204 | 5 | Must |
| US-078 | Tata Usaha | Sebagai TU, saya ingin tools migrasi data agar go-live cepat | • Template + validasi + deduplikasi NIS • ≥90% data siswa & guru valid dalam 1 bulan go-live | KPI §3 | 8 | Must |
| US-079 | PM | Sebagai PM, saya ingin panduan per peran | • PDF + video singkat (≤5 menit) per peran utama • Terbit sebelum UAT pilot *(track paralel)* | §9 | 5 | Should |
| US-081 | DevOps | Sebagai DevOps, saya ingin runbook deploy/backup/restore | • Backup DB full harian, retensi 30 hari • Drill restore tiap kuartal: RPO ≤24 jam, RTO ≤4 jam *(track paralel)* | §6.4 | 5 | Must |
| US-082 | PM | Sebagai PM, saya ingin UAT di 1–2 sekolah pilot + go-live checklist | • Daftar temuan terdokumentasi & ditindaklanjuti • Checklist go-live (data, pelatihan 3 hari, pendampingan 2 minggu) *(track paralel)* | §15 | 8 | Must |

### E10 — Fondasi SaaS & tenant

| ID | Peran | User Story | Acceptance Criteria | FR ref | Pts | Prioritas |
|---|---|---|---|---|---|---|
| US-083 | Calon pelanggan | Sebagai calon pelanggan, saya ingin registrasi mandiri sekolah agar bisa coba tanpa hubungi sales | • Form: nama sekolah, NPSN, jenjang, email & no. WA admin • CAPTCHA + rate limit 3x/jam/IP • Email verifikasi terkirim ≤2 menit, link kedaluwarsa 24 jam | SaaS-01 | 5 | Must |
| US-084 | Sistem | Sebagai sistem, saya ingin provisioning otomatis tenant baru agar sekolah langsung bisa dipakai | • Subdomain unik `<sekolah>.sims.id` dibuat ≤5 menit • Seed otomatis: akun admin, tahun ajaran aktif, modul inti aktif • Welcome email terkirim • Kegagalan → retry 3x lalu alert ke operator + status terlihat | SaaS-02 | 8 | Must |
| US-085 | BE | Sebagai BE, saya ingin resolusi tenant via subdomain agar isolasi antar pelanggan otomatis | • Middleware resolve tenant dari subdomain; subdomain tak dikenal → halaman 404 + tautan daftar • Query tanpa konteks tenant melempar exception • Test: data tenant A tak terbaca dari subdomain B | SaaS-03 | 5 | Must |
| US-086 | Admin Sekolah | Sebagai admin sekolah, saya ingin halaman status langganan agar tahu posisi paket saya | • Tampil: paket aktif, masa berlaku, sisa trial, kuota terpakai (siswa/storage/user) • Tautan ke halaman billing/upgrade | SaaS-04 | 3 | Must |
| US-087 | Operator Platform | Sebagai operator, saya ingin peran platform (support/finance) terpisah dari admin sekolah | • Peran: Platform Admin, Support, Finance • Support bisa lihat semua tenant (read) tanpa ubah data billing • Semua aksi operator tercatat di audit log lintas tenant | SaaS-07 | 5 | Must |
| US-088 | Admin Sekolah | Sebagai admin baru, saya ingin onboarding checklist agar setup awal tidak ada yang terlewat | • Langkah: lengkapi profil → tahun ajaran → import siswa → undang guru • Progress % tersimpan • Checklist hilang setelah 100% | SaaS-02 | 3 | Should |

### E11 — Sistem modul (feature flags)

| ID | Peran | User Story | Acceptance Criteria | FR ref | Pts | Prioritas |
|---|---|---|---|---|---|---|
| US-089 | Platform Admin | Sebagai platform admin, saya ingin registry modul pusat agar katalog modul terkendali | • Daftar modul: kode, nama, versi, dependensi modul lain, paket yang boleh • Modul inti (siswa, jadwal, absensi, nilai) terkunci aktif | SaaS-05 | 5 | Must |
| US-090 | Operator/Admin | Sebagai operator, saya ingin aktif/nonaktif modul per tenant | • Toggle per tenant ≤1 menit berlaku • Modul nonaktif: route → 403, data lama tetap tersimpan (arsip read-only) • Perubahan tercatat di audit log | SaaS-05 | 8 | Must |
| US-091 | Staf | Sebagai staf, saya ingin menu & permission otomatis mengikuti modul aktif | • Sidebar hanya tampilkan modul aktif tenant • Permission modul nonaktif tidak diberikan ke peran mana pun • Test otomatis: matriks peran × modul | SaaS-05 | 5 | Must |
| US-092 | Admin Sekolah | Sebagai admin sekolah, saya ingin katalog modul untuk melihat & mengajukan add-on | • Katalog: nama modul, deskripsi, harga add-on/bulan • Tombol "Ajukan" → tiket ke operator + notifikasi • Riwayat pengajuan terlihat | SaaS-05 | 5 | Must |
| US-093 | Platform Admin | Sebagai platform admin, saya ingin feature flag rollout bertahap per tenant | • Flag dapat diaktifkan untuk % tenant atau daftar tenant tertentu • Rollback 1 klik *(Fase 2)* | SaaS-05 | 3 | Could |

### E12 — Billing & langganan

| ID | Peran | User Story | Acceptance Criteria | FR ref | Pts | Prioritas |
|---|---|---|---|---|---|---|
| US-094 | Platform Admin | Sebagai platform admin, saya ingin kelola paket langganan (basic/pro/enterprise) | • Batas per paket: jml siswa, storage, user staf, modul termasuk • Halaman harga publik otomatis dari data paket • Perubahan harga hanya berlaku siklus berikut | SaaS-04 | 5 | Must |
| US-095 | Calon pelanggan | Sebagai pendaftar, saya ingin trial 30 hari otomatis agar bisa evaluasi dulu | • Trial aktif saat provisioning • Banner sisa trial di dashboard admin • H-3 trial berakhir: notifikasi upgrade • Tanpa kartu kredit untuk trial | SaaS-04 | 3 | Must |
| US-096 | Finance | Sebagai finance, saya ingin invoice otomatis per siklus *(Fase 2)* | • Invoice PDF terkirim H-7 jatuh tempo • Nomor invoice unik per tenant • Riwayat invoice tersimpan | SaaS-06 | 5 | Should |
| US-097 | Sistem | Sebagai sistem, saya ingin pembayaran recurring via gateway *(Fase 2)* | • Midtrans/Xendit: kartu, VA, QRIS • Webhook terverifikasi signature • Status langganan update otomatis ≤5 menit | SaaS-06 | 8 | Should |
| US-098 | Sistem | Sebagai sistem, saya ingin dunning + suspend otomatis untuk gagal bayar *(Fase 2)* | • Pengingat H-7/H-3/H+0 via WA+email • Grace period 14 hari (mode read-only) • Suspend otomatis: login diblokir + halaman billing • Reaktivasi ≤5 menit setelah bayar | SaaS-06 | 8 | Should |
| US-099 | Admin Sekolah | Sebagai admin, saya ingin halaman billing mandiri *(Fase 2)* | • Riwayat invoice + unduh PDF • Ganti paket (prorata) • Kelola metode pembayaran | SaaS-06 | 5 | Should |
| US-100 | Admin Sekolah | Sebagai admin, saya ingin kuota paket ditegakkan dengan pesan yang jelas | • Tambah siswa melebihi kuota → ditolak + CTA upgrade • Peringatan storage 80% & 95% • Cek kuota ≤500 ms per request | SaaS-04 | 5 | Must |

### E13 — Operasional platform

| ID | Peran | User Story | Acceptance Criteria | FR ref | Pts | Prioritas |
|---|---|---|---|---|---|---|
| US-101 | Operator Platform | Sebagai operator, saya ingin dashboard bisnis (MRR, churn, trial conversion) | • MRR, tenant aktif/trial/suspend, churn % bulanan • Daftar tenant bermasalah (provisioning gagal, payment gagal) • Refresh ≤5 menit | SaaS-07 | 8 | Must |
| US-102 | Support | Sebagai support, saya ingin kelola tenant (suspend manual/impersonasi tercatat) | • Cari tenant by nama/subdomain/NPSN • Suspend/unsuspend manual butuh alasan • Impersonasi: banner jelas + audit + auto-expire 30 menit | SaaS-07 | 5 | Must |
| US-103 | Platform Admin | Sebagai platform admin, saya ingin kelola paket & harga | • CRUD paket, ubah harga/kuota • Berlaku untuk siklus berikut; tenant aktif tidak berubah mendadak • Audit perubahan • Job rekonsiliasi idempotent: `tenant_modules` selalu cocok dengan hasil `EntitlementService` (P1-06) | SaaS-07 | 3 | Must |
| US-104 | Operator Platform | Sebagai operator, saya ingin halaman status sistem publik *(Fase 2)* | • Status layanan + riwayat insiden 90 hari • Subscribe notifikasi insiden *(Fase 2)* | SaaS-07 | 2 | Could |

---

## 4. Sprint Plan (Sprint 1–10)

> Prinsip dependensi: **fondasi → master data → transaksi (jadwal/absensi/nilai) → laporan/dashboard → PWA**. API untuk PWA dibangun di sprint yang sama/sebelum layar PWA-nya.

### Sprint 1 — Fondasi teknis & tenant SaaS (Fase 0)

**Tujuan:** aplikasi terinstall, isolasi tenant via subdomain jalan, registry modul & peran platform siap.

| Story | Pts |
|---|---|
| US-001 setup Laravel 13 + Docker + CI/CD | 5 |
| US-002 struktur domain modular + Larastan | 3 |
| US-003 multi-tenant: schools + school_id + global scope | 8 |
| US-085 resolusi tenant via subdomain + middleware | 5 |
| US-089 registry modul pusat | 5 |
| US-087 peran platform (admin/support/finance) | 5 |
| US-005 login/logout + kebijakan password + lockout + riwayat login | 5 |
| **Total** | **36** |

**Risiko/dependensi:** US-003 + US-085 adalah fondasi seluruh timeline — jika molor, semua geser. Mitigasi: spike 2 hari validasi global scope + middleware subdomain sebelum coding penuh.

### Sprint 2 — Sistem modul + RBAC + struktur akademik (Fase 0)

**Tujuan:** feature flag per tenant berfungsi; menu & permission mengikuti modul aktif; tahun ajaran siap.

| Story | Pts |
|---|---|
| US-090 aktif/nonaktif modul per tenant | 8 |
| US-091 menu & permission otomatis mengikuti modul aktif | 5 |
| US-004 tenant switcher operator platform | 5 |
| US-006 RBAC Spatie + seeder peran | 5 |
| US-007 audit log global | 3 |
| US-023 CRUD tahun ajaran + semester (+aktivasi & snapshot) | 5 |
| US-025 kalender akademik | 5 |
| **Total** | **36** |

**Risiko/dependensi:** kompleksitas feature flag (US-090/091) — modul nonaktif harus benar-benar 403 di semua lapis (route, policy, menu). Alokasikan test matrix peran × modul sejak awal.

### Sprint 3 — Registrasi, provisioning & master awal (Fase 0)

**Tujuan:** sekolah bisa daftar mandiri dan langsung dapat tenant jadi; master rombel/mapel/wali/guru jalan.

| Story | Pts |
|---|---|
| US-083 registrasi mandiri + verifikasi email | 5 |
| US-084 provisioning otomatis tenant (subdomain + seed) | 8 |
| US-086 halaman status langganan | 3 |
| US-088 onboarding checklist admin baru | 3 |
| US-027 CRUD rombel | 5 |
| US-028 CRUD mapel + KKM | 3 |
| US-016 CRUD wali murid | 5 |
| US-018 CRUD guru/pegawai | 5 |
| **Total** | **37** |

**Risiko/dependensi:** provisioning gagal (US-084) = calon pelanggan hilang. Wajib: retry + alert + halaman status; uji dengan 50 registrasi dummy beruntun.

### Sprint 4 — Master siswa, penugasan & katalog modul (Fase 1)

**Tujuan:** data siswa lengkap + import; penugasan mengajar rampung; admin bisa lihat katalog add-on.

| Story | Pts |
|---|---|
| US-010 CRUD siswa (+validasi NIS unik, pencarian & filter, soft delete) | 8 |
| US-012 import Excel siswa | 8 |
| US-014 profil 360° siswa | 5 |
| US-017 relasi siswa↔wali | 3 |
| US-019 penugasan mengajar guru-mapel-kelas | 5 |
| US-020 penetapan wali kelas | 3 |
| US-021 import Excel guru | 5 |
| US-092 katalog modul + pengajuan add-on | 5 |
| **Total** | **42** |

**Risiko/dependensi:** butuh contoh data Excel asli dari sekolah pilot untuk validasi format import (US-012/US-021) — minta di awal sprint.

### Sprint 5 — Jadwal + paket, trial & kuota (Fase 1)

**Tujuan:** jadwal mingguan valid tanpa bentrok; fondasi komersial SaaS (paket + trial + enforcement) jalan.

| Story | Pts |
|---|---|
| US-032 template jam pelajaran per sekolah | 3 |
| US-033 susun jadwal mingguan per rombel | 8 |
| US-034 deteksi bentrok otomatis | 8 |
| US-036 tampilan jadwal + cetak PDF | 5 |
| US-038 API jadwal untuk PWA | 5 |
| US-094 manajemen paket langganan + halaman harga | 5 |
| US-095 trial 30 hari otomatis + banner | 3 |
| US-100 kuota enforcement (siswa/storage/user) | 5 |
| **Total** | **42** |

**Risiko/dependensi:** deteksi bentrok (US-034) kompleksitas tertinggi modul akademik — pairing BE+QA. Kuota enforcement (US-100) harus cepat (≤500 ms) agar tidak memperlambat setiap request.

### Sprint 6 — Absensi + notifikasi + laporan dasar (Fase 1)

**Tujuan:** absensi harian ≤2 menit/kelas; notifikasi alpa otomatis; laporan bawaan & export jalan.

| Story | Pts |
|---|---|
| US-039 input absensi harian + mode cepat | 8 |
| US-042 kunci/buka absensi | 5 |
| US-043 rekap absensi + peringatan dini | 8 |
| US-045 adapter WA gateway + FCM + template per sekolah | 8 |
| US-044 notifikasi otomatis alpa ke ortu (≤30 menit) | 8 |
| **Total** | **37** |

**Risiko/dependensi:** butuh akun WhatsApp gateway & Firebase project aktif (keputusan vendor di Sprint 5). Akurasi nomor WA ortu menentukan keberhasilan US-044.

### Sprint 7 — Nilai (input) + PWA shell/auth + dashboard operator (Fase 1)

**Tujuan:** guru bisa input & import nilai; PWA bisa diinstall & login; operator pantau bisnis.

| Story | Pts |
|---|---|
| US-047 komponen & bobot penilaian per mapel | 5 |
| US-048 input nilai (otorisasi ampuan) | 8 |
| US-049 import Excel nilai | 5 |
| US-058 export besar via queue | 5 |
| US-064 PWA shell: manifest dinamis + service worker + instalasi | 5 |
| US-065 login PWA (WA+OTP / username+password) | 5 |
| US-101 dashboard operator (MRR, churn, trial conversion) | 8 |
| **Total** | **41** |

**Risiko/dependensi:** otorisasi input nilai (US-048) wajib diuji penetrasi peran. US-101 butuh definisi metrik bisnis disepakati PM di awal sprint.

### Sprint 8 — Nilai (hitung–kunci–rilis) + dashboard + laporan + ops tenant (Fase 1)

**Tujuan:** siklus nilai lengkap; dashboard per peran; laporan bawaan; support bisa kelola tenant.

| Story | Pts |
|---|---|
| US-050 hitung nilai akhir + predikat (<10 detik) | 5 |
| US-051 kunci/buka periode nilai + approve kepsek | 5 |
| US-052 leger nilai per kelas/mapel | 5 |
| US-053 rilis nilai ke PWA | 3 |
| US-054 API nilai (hanya yang dirilis) | 3 |
| US-060 dashboard per peran + widget | 8 |
| US-056 laporan bawaan (siswa, guru, kelas, mutasi) | 5 |
| US-057 export Excel/PDF berkop sekolah | 5 |
| **Total** | **39** |

**Risiko/dependensi:** sprint padat — prioritaskan penyelesaian siklus nilai (US-050–054) bila velocity tertekan; US-056/057 bisa geser ke buffer Sprint 9.

### Sprint 9 — PWA siswa/ortu + push + peringatan dini (Fase 1)

**Tujuan:** PWA lengkap untuk siswa & ortu; push notification; peringatan dini akademik.

| Story | Pts |
|---|---|
| US-067 PWA: jadwal siswa | 3 |
| US-068 PWA: absensi saya | 3 |
| US-069 PWA: nilai siswa | 3 |
| US-070 PWA: dashboard ortu multi-anak | 8 |
| US-071 push notification (alpa, nilai rilis, pengumuman) | 5 |
| US-046 API absensi untuk PWA | 3 |
| US-063 peringatan dini (alpa tinggi, <KKM, guru belum input) | 5 |
| US-102 manajemen tenant operator (suspend/impersonasi tercatat) | 5 |
| US-103 manajemen paket & harga | 3 |
| **Total** | **38 + buffer kecil untuk bugfix integrasi** |

**Risiko/dependensi:** US-070 agregasi multi-anak — di model SaaS, anak di tenant berbeda hanya bisa digabung bila dalam satu organisasi (E14) atau akun wali terdaftar di tiap tenant; putuskan aturannya di sprint planning. Push iOS/Safari terbatas — catat di panduan.

### Sprint 10 — Hardening, migrasi & go-live pilot (Fase 1)

**Tujuan:** siap produksi — tutup tahun ajaran, kenaikan kelas, offline PWA, audit responsif, tools migrasi; buffer bugfix UAT.

| Story | Pts |
|---|---|
| US-026 tutup tahun ajaran (arsip read-only) | 5 |
| US-030 kenaikan kelas massal + pratinjau | 8 |
| US-072 mode offline PWA | 5 |
| US-073 manajemen perangkat (cabut sesi) | 3 |
| US-075 audit responsif + Lighthouse ≥85 | 5 |
| US-078 tools migrasi data + deduplikasi | 8 |
| **Total** | **34 + buffer ±6 untuk bugfix UAT** |

**Track paralel PM/QA/DevOps (di luar velocity):** US-079 panduan per peran, US-081 runbook + drill restore, US-082 UAT sekolah pilot + go-live checklist.

**Risiko/dependensi:** jadwal UAT butuh komitmen sekolah pilot — kunci tanggal di Sprint 8. Skenario suspend tenant (SaaS-06) diuji di staging sebelum go-live meski billing penuh di Fase 2.

## 5. Definition of Ready & Definition of Done

### Definition of Ready (syarat masuk sprint)
1. Story punya peran, keinginan, dan tujuan yang jelas.
2. Acceptance criteria terukur (ada angka/waktu) dan FR ref terisi.
3. Desain UI / kontrak API disepakati (untuk story FE/PWA).
4. Dependensi ke story lain teridentifikasi dan terjadwal.
5. Diestimasi bersama tim (planning poker).

### Definition of Done (selaras PRD §14 + kriteria QA)
1. Kebutuhan fungsional terpenuhi & lolos skenario uji (happy path + minimal 1 edge case).
2. Otorisasi: peran yang tidak berhak mendapat **403** di web & API (diuji).
3. Isolasi tenant: data tenant A tidak muncul di konteks tenant B — diuji otomatis **antar subdomain pelanggan** (bukan hanya antar sekolah dummy).
4. Modul nonaktif: route → 403, menu hilang, permission tidak diberikan (test matrix peran × modul).
5. Suspend: tenant suspended tidak bisa login (semua peran) & data tetap utuh; reaktivasi memulihkan akses ≤5 menit (diuji di staging).
6. Audit log tercatat untuk setiap mutasi data, termasuk aksi operator platform lintas tenant.
7. Responsif: lolos cek 360 px tanpa scroll horizontal; tabel besar server-side pagination.
8. Kode lolos CI (Pest hijau + Larastan level ≥5) dan code review.
9. Tidak ada bug **critical/major** terbuka; QA sign-off di staging.
10. Dokumentasi singkat cara pakai untuk peran terkait (atau tiket docs tercatat).

---

## 6. Risiko Sprint & Mitigasi

| Risiko | Sprint terdampak | Dampak | Mitigasi |
|---|---|---|---|
| Desain multi-tenant (US-003) molor | 1 (kritis) | Seluruh timeline geser | Spike 2 hari di awal; review arsitektur sebelum coding |
| Data Excel sekolah pilot tidak tersedia/berantakan | 4, 10 | Import & migrasi gagal uji | Minta contoh data di Sprint 1; template + validasi ketat (US-012/021/078) |
| Scope creep ("tambah sedikit fitur X") | Semua | Velocity jebol | Change request via PM; backlog Fase 2 menampung; sprint backlog dikunci |
| Vendor WA gateway / FCM belum siap | 5–6 | Notifikasi alpa & push tertunda | Keputusan vendor di Sprint 5; adapter pattern agar gampang ganti; fallback email |
| Deteksi bentrok jadwal terlalu lambat/kompleks | 5 | Jadwal tidak usable | Batasi scope: cek bentrok per simpan, bukan optimasi otomatis penuh |
| Sekolah pilot sulit diajak UAT (kalender akademik) | 9–10 | Go-live mundur | Kunci tanggal UAT di Sprint 8; sediakan data dummy realistis sebagai plan B |
| Otorisasi nilai bocor (guru lihat kelas lain) | 7–8 | Kritis keamanan | Test matrix peran × endpoint di setiap story nilai; QA khusus authorization |
| Kompleksitas feature flag: modul nonaktif masih bisa diakses | 2–10 | Kritis keamanan | Test otomatis peran × modul di CI; review setiap route baru wajib daftar modul |
| Provisioning tenant gagal / lambat | 3 | Calon pelanggan hilang | Retry 3x + alert operator; load test 50 registrasi beruntun; halaman status provisioning |
| Payment gateway recurring bermasalah (Fase 2) | Fase 2 | Billing macet, churn naik | Adapter pattern; provider cadangan; mode invoice manual sebagai fallback |
| Trial abuse (daftar berulang) | 3–5 | Beban infra & support | Rate limit + CAPTCHA + deteksi NPSN/email duplikat; trial tanpa kartu kredit tapi 1x per NPSN |
| PWA iOS/Safari: push & instalasi terbatas | 9 | Ekspektasi pengguna | Catat batasan di panduan; prioritaskan Android dulu |

---

## 7. Backlog Fase 2 & 3 (Carry-over dari MVP)

### Fase 2 — Operasional & billing penuh

Item berikut **sengaja ditunda** agar MVP 10 sprint tercapai:

| Item | FR ref | Catatan |
|---|---|---|
| Invoice otomatis per siklus (US-096) | SaaS-06 | PDF + kirim H-7; nomor unik per tenant |
| Pembayaran recurring via gateway (US-097) | SaaS-06 | Midtrans/Xendit + webhook terverifikasi |
| Dunning + suspend otomatis (US-098) | SaaS-06 | Pengingat → grace 14 hari → suspend → reaktivasi |
| Halaman billing mandiri (US-099) | SaaS-06 | Riwayat invoice, ganti paket prorata |
| Feature flag rollout bertahap (US-093) | SaaS-05 | Aktif untuk % tenant; rollback 1 klik |
| Halaman status sistem publik (US-104) | SaaS-07 | Status layanan + riwayat insiden 90 hari |
| Struktur kurikulum per tingkat | FR-403 | MVP: mapel diinput manual per sekolah |
| Jadwal khusus (pengganti/ujian/piket) | FR-504 | — |
| Notifikasi perubahan jadwal | FR-506 | Butuh preferensi notifikasi matang dulu |
| Absensi per mapel | FR-602 | Opsional per PRD; mulai dari absensi harian |
| Report builder mandiri | FR-1004 | — |
| Load test 500 pengguna konkuren | NFR §9 | Dijadwalkan sebelum scale-up Fase 2 |

### Fase 3 — Organisasi grup sekolah (E14)

Add-on untuk grup/yayasan yang menaungi beberapa sekolah pelanggan:

| Item | FR ref | Catatan |
|---|---|---|
| Organisasi: CRUD grup + anggota tenant | SaaS-08 | Satu organisasi menaungi N tenant |
| Admin organisasi (peran baru) | SaaS-08 | Kelola tenant anggota, tanpa akses billing per tenant |
| Laporan konsolidasi organisasi | SaaS-08, FR-1003 | Perbandingan antar sekolah anggota: jml siswa, % kehadiran, rata-rata nilai; ≤5 detik |
| Dashboard organisasi + tren | SaaS-08, FR-1102 | Agregat & tren per semester antar anggota |
| Aturan dashboard ortu multi-anak lintas tenant | SaaS-08 | Anak di tenant berbeda digabung hanya dalam satu organisasi |

---

## 8. Backlog Tambahan (hasil audit Dok 08)

Stories penutup gap G-01..G-07, T-02, T-03, T-05, B-04, B-05 dan persiapan G-03 (Dapodik). ID lanjutan US-105 dst.

| ID | Peran | User Story | Acceptance Criteria | FR ref | Pts | Prioritas |
|---|---|---|---|---|---|---|
| US-105 | Staf | Sebagai staf yang lupa password, saya ingin reset mandiri via email/WA agar tidak menunggu admin | • Link/token sekali pakai kedaluwarsa ≤60 menit • Token tidak bisa dipakai ulang • Notifikasi ke email/WA saat password berhasil diganti | FR-901 | 5 | Must |
| US-106 | Siswa/Ortu | Sebagai pengguna PWA, saya ingin ganti password sendiri dari aplikasi | • Wajib memasukkan password lama • Password baru ikut kebijakan (min. 8 karakter, huruf+angka) • Opsi cabut sesi perangkat lain | Dok 03 §2.8 | 3 | Must |
| US-107 | Admin Sekolah | Sebagai admin, saya ingin kelola template notifikasi WA agar bahasa pesan bisa disesuaikan sekolah | • CRUD template per jenis notifikasi (alpa, nilai dirilis, tagihan) • Variabel `{{nama_siswa}}`, `{{tanggal}}`, dsb. • Pratinjau + kirim tes ke nomor sendiri sebelum aktif | FR-606 | 5 | Must |
| US-108 | Staf | Sebagai staf, saya ingin pencarian global lintas modul agar cepat menemukan siswa/guru | • Hasil ≤1 detik untuk 10.000+ data • Otomatis ter-scope tenant • Menampilkan tipe hasil (siswa/guru/kelas) + link langsung | — | 5 | Should |
| US-109 | Admin Sekolah | Sebagai admin, saya ingin kelola user secara massal di awal tahun ajaran | • Aktivasi/nonaktif massal via checklist • Reset password massal dengan password acak per user • Hasil & kegagalan dilaporkan per baris | FR-901 | 3 | Should |
| US-110 | Admin Sekolah | Sebagai admin, saya ingin mengekspor paket data siswa yang mutasi ke sekolah pelanggan lain | • Paket berisi JSON + PDF + dokumen pendukung • Membutuhkan persetujuan tertulis kedua sekolah (upload bukti) • Tanpa transfer otomatis antar tenant (privasi) | §17.6 | 5 | Should |
| US-111 | Admin Sekolah | Sebagai admin, saya ingin halaman audit log global agar aktivitas mencurigakan terlacak | • Filter tanggal, aksi, user, dan modul • Klik untuk lihat before/after • Export CSV untuk keperluan audit | §9 | 3 | Should |
| US-112 | Admin Sekolah | Sebagai admin (paket Pro+), saya ingin mendaftarkan webhook agar sistem sekolah menerima event otomatis | • Event: `siswa_baru`, `pembayaran_lunas`, `nilai_dirilis`, `pengumuman_baru` • Signature HMAC per pengiriman • Retry backoff maks. 5x/24 jam + log status | Dok 03 §11.4 | 8 | Should |
| US-113 | PM | Sebagai PM, saya ingin product analytics agar keputusan berbasis data | • Event: registrasi, provisioning, import_siswa, absensi_pertama, upgrade_paket • Dashboard funnel aktivasi tenant • Tanpa PII di event tracking | §3 KPI | 5 | Must |
| US-114 | CS | Sebagai CS, saya ingin survei NPS/CSAT tiap semester agar churn terdeteksi dini | • Survei singkat di PWA/panel tiap akhir semester • Skor <7 memicu alert ke CS • Tren skor per tenant tampil di dashboard platform | — | 3 | Should |
| US-115 | BE | Sebagai BE, saya ingin studi format Dapodik agar integrasi Fase 2 terencana | • Dokumen pemetaan field Dapodik ↔ SIMS • Daftar gap format yang butuh transformasi • Estimasi effort integrasi penuh | §11 | 5 | Should |
| US-116 | BE | Sebagai BE, saya ingin adapter impor file Dapodik agar migrasi sekolah cepat | • Upload file ekspor Dapodik → validasi → pratinjau → impor • Laporan baris gagal seperti impor Excel • Idempoten (impor ulang aman) | §11 | 5 | Should |
| US-117 | BE | Sebagai BE, saya ingin adapter ekspor ke format Dapodik agar sekolah memenuhi kewajiban pelaporan | • Ekspor data siswa/guru sesuai skema Dapodik • Validasi skema sebelum unduh • Dijadwalkan per semester | §11 | 5 | Should |
| US-118 | Platform Admin | Sebagai Platform Admin, saya ingin mengelola paket & kuota via UI agar perubahan harga/batas tidak butuh deploy | • CRUD paket (nama, harga, kuota siswa/storage/staf/WA, rate limit) • Perubahan harga hanya berlaku untuk langganan baru (grandfathering) • Setiap perubahan tercatat di audit log platform | §9.1 | 5 | Must |
| US-119 | Platform Admin | Sebagai Platform Admin, saya ingin memberikan override kuota sementara ke satu tenant (mis. dispensasi PPDB) | • Pilih tenant + metrik + batas kustom + alasan wajib + tanggal kedaluwarsa • Banner "kuota khusus" tampil di billing tenant • Kedaluwarsa otomatis kembali ke batas paket; tercatat di audit | §9.5 | 3 | Must |
| US-120 | Sistem | Sebagai sistem, saya ingin batasan trial ditegakkan otomatis agar tidak disalahgunakan | • Blokir tambah siswa ke-101, blokir broadcast massal & API di trial • Satu trial per NPSN (duplikat ditolak) • Banner sisa trial + batas di dashboard admin | §9.3 | 3 | Must |
| US-121 | Admin Sekolah | Sebagai Admin Sekolah, saya ingin peringatan dini saat kuota hampir habis agar tidak terkunci mendadak | • Peringatan di 80% & 100% via banner + WA • Tombol "Lihat paket" langsung ke halaman upgrade • Riwayat pemakaian 6 bulan tampil sebagai grafik | §9.5 | 3 | Should |
| US-122 | BE | Sebagai BE, saya ingin perintah `docs:graph` yang memvisualisasikan alur program dari kode (Graphify) | • Scan route→controller→service→job/event per modul → output Mermaid ke `docs/wiki/diagrams/` • CI gagal bila diagram basi • deptrac.yaml menegakkan batas antar layer/modul | Dok 13 §10 | 5 | Must |
| US-123 | BE | Sebagai BE, saya ingin setiap impor massal tercatat sebagai batch yang bisa diurungkan (P0-06) | • `import_batches` + `import_batch_id` di baris terdampak • Tombol "Urungkan impor" (compensating delete per batch) • Undo ≤5 menit untuk 10rb baris | Dok 15 | 5 | Must |
| US-124 | BE | Sebagai BE, saya ingin buku persetujuan & workflow DSAR sesuai UU PDP (P0-07) | • `consent_ledger` immutable (versi, tujuan, aktor, bukti, penarikan) • `dsar_cases` dengan SLA per jenis hak • Ekspor bundle pemohon | Dok 15 | 8 | Must |
| US-125 | BE | Sebagai BE, saya ingin OpenAPI 3.1 sebagai kontrak API kanonis (P1-12) | • Spec generate docs + validator + typed client + mock • Contract test CI • Enum plan/permission/error terkunci | Dok 15 | 5 | Must |
| US-126 | BE | Sebagai BE, saya ingin model sesi & refresh token yang aman (P1-08) | • Rotasi refresh + reuse detection (revoke family) • Endpoint refresh/sessions/revoke • Device binding | Dok 03 §2.9 | 5 | Must |
| US-127 | BE | Sebagai BE, saya ingin persistence webhook & pembayaran yang reliabel (P1-07) *(Fase 2)* | • `webhook_subscriptions/deliveries` (outbox+retry) • `payment_transactions` ledger • `idempotency_keys` unik per provider/event | Dok 15 | 8 | Should |
| US-128 | PM/Legal | Sebagai PM, saya ingin DPIA + RoPA + register subprosesor sebelum pilot (P0-07) | • DPIA untuk pemrosesan data anak • RoPA • Daftar subprosesor + DPA vendor • PP 17/2025 masuk baseline | Dok 12 | 5 | Must |
| US-129 | BE | Sebagai BE, saya ingin 2FA TOTP wajib untuk peran platform (P1-09) | • Enforce saat login peran platform (Sprint 9) • Recovery codes • Audit event • Opsional untuk admin sekolah | FR-904 | 5 | Must |

### Rekomendasi penempatan sprint

US-105, US-106, US-107, US-109, US-111, US-113 (**total +24 pts**) diusulkan masuk Sprint 7–9: dibagi rata ±8 pts per sprint, masih dalam toleransi velocity (±40) bila buffer bugfix Sprint 8 dipakai sebagian. US-113 (analytics) diprioritaskan paling awal di antara kelompok ini karena instrumentasi lebih murah sebelum fitur menumpuk. US-118, US-119, US-120, US-121 (manajemen kuota & trial, **total +14 pts**) diusulkan masuk Sprint 4–5 menyusul fondasi billing (US-094–096), karena enforcement kuota harus hidup sebelum tenant trial pertama. US-108, US-110, US-112, US-114 masuk Fase 2; US-115/116/117 (persiapan Dapodik) dijadwalkan di 2 sprint terakhir Fase 1 sebagai *spike + fondasi adapter* agar Fase 2 tidak mulai dari nol.

---

*Akhir Dok 04 v1.1 (pivot SaaS) — perubahan backlog dicatat via sprint review; perubahan besar butuh persetujuan Product Owner.*
