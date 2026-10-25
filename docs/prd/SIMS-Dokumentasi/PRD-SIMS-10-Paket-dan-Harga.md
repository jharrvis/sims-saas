# Dok 10 — Paket Layanan, Matriks Fitur per Tier & Harga

| Atribut | Keterangan |
|---|---|
| Versi | 1.0 |
| Tanggal | 9 Oktober 2026 |
| Acuan | PRD §8 (katalog 16 modul), §9 (model bisnis), Dok 02 (tabel `plans`/`modules`) |
| Status | Rekomendasi — harga final wajib divalidasi via pilot |

---

## 1. Ringkasan Hasil Riset Pasar

Riset dilakukan 9 Oktober 2026 via pencarian web (Bahasa Indonesia). Temuan diringkas di bawah; detail sumber di §8.

### 1.1 Harga produk sejenis di Indonesia

| Produk | Model harga | Harga | Catatan |
|---|---|---|---|
| AdminSekolah.net (SaaS admin sekolah lokal) | Flat per unit/tingkat/bulan | ±Rp 202–600 rb/bulan | Termasuk notifikasi WA, multi-user; pembanding terdekat |
| Software sekolah desktop lokal (software-id.com) | Sekali bayar | Rp 1,5 jt | Lisensi + update 1 tahun; tanpa cloud |
| Paket PPDB Online System (Tokopedia) | Bulanan | Rp 1,75 jt/bulan | Website + PPDB + payment gateway |
| DigiSchool Display (jadwal digital) | Sekali bayar + maintenance | Rp 3,5 jt + Rp 1 jt/tahun | Niche signage |
| Fekara (global) | Per siswa / sekali bayar | $0,20/siswa atau $350 flat | Freemium 50 siswa |
| Eduwonka (global) | Per siswa/tahun | Mulai $50/tahun per 50 siswa (±Rp 16 rb/siswa/tahun) | — |
| Aplikasi rapor Excel/PHP (Tokopedia/Lynk) | Sekali bayar | Rp 80–350 rb | Fitur tunggal, tanpa support |
| Google Workspace for Education | Freemium → per user | Gratis s.d. $5/user/bulan | Jangkar ekspektasi "gratis" di edukasi |
| **e-Rapor Kemendikbud** | **Gratis** | Rp 0 | Resmi, terintegrasi Dapodik — **kompetitor langsung modul E-Rapor kita** |

**Implikasi:** (1) Pasar Indonesia terbiasa **flat per sekolah/unit/bulan** (AdminSekolah, PPDB system), bukan per siswa — kecuali pemain global. (2) Harga psikologis yang diterima: ratusan ribu–1 jutaan per bulan per sekolah. (3) e-Rapor pemerintah gratis → modul E-Rapor kita hanya laku bila menawarkan nilai tambah jelas (otomatisasi deskripsi, QR verifikasi, integrasi nilai real-time).

### 1.2 Jangkar daya beli: kisaran SPP sekolah swasta

| Jenjang | Kisaran SPP/bulan (swasta) | Contoh konkret dari riset |
|---|---|---|
| SD swasta menengah | Rp 1–2,2 jt | SD Islam Al Fath Tangerang Rp 1,75 jt; SD Al Lauzah Tangsel Rp 1 jt; SD Sinar Cendekia Serpong Rp 1,42 jt |
| SMP swasta | Rp 2–4,5 jt | — (kisaran umum) |
| SMA swasta | Rp 750 rb–2,4 jt+ | SMA Suluh Rp 750 rb; SMA Islam Harapan Ibu Rp 1,7 jt; Labschool Cibubur Rp 2,4 jt |

**Jangkar penetapan harga:** biaya langganan yang "tidak terasa" = **setara < 1 SPP satu siswa per bulan**. Untuk SD swasta menengah (SPP ±Rp 1 jt), harga Rp 499 rb–999 rb/bulan = 0,5–1 SPP siswa — mudah disetujui kepala sekolah.

### 1.3 Dana BOS: bolehkah untuk langganan aplikasi?

| Aturan | Isi | Sumber |
|---|---|---|
| **Larangan** | Dana BOS **dilarang** untuk "membeli software/perangkat lunak untuk pelaporan keuangan BOS atau software sejenis" | rkas.kemdikbud.go.id (larangan BOS); Permendikbud No. 63/2022 |
| **Diperbolehkan** | "Langganan platform belajar digital seperti LMS", "biaya penilaian dan ujian sekolah", dan "pembiayaan lain yang relevan dalam rangka menunjang operasional administrasi kegiatan sekolah" | Permendikbud No. 63/2022 (komponen penggunaan BOS) |

**Implikasi positioning (penting):** SIMS **boleh** dibiayai BOS bila diposisikan sebagai *platform pembelajaran digital (LMS), penilaian/evaluasi, dan administrasi operasional sekolah*. **Jangan** memposisikan/menamakan modul sebagai "software pelaporan keuangan BOS" — itu justru masuk larangan. Modul Keuangan & SPP diposisikan sebagai "administrasi pembayaran siswa", bukan pelaporan BOS.

### 1.4 Model harga umum SaaS edukasi

| Model | Contoh | Kelebihan | Kelemahan |
|---|---|---|---|
| **Flat per sekolah/bulan** | AdminSekolah, PPDB system | Sederhana, mudah dianggarkan sekolah, insentif adopsi penuh | ARPU tidak tumbuh dengan ukuran sekolah |
| **Per siswa aktif/bulan** | Fekara, Eduwonka | Adil untuk sekolah kecil; skala dengan value | Friksi: sengketa hitungan siswa, disinsentif daftarkan semua siswa, rumit dijelaskan ke bendahara |
| **Sekali bayar** | Software desktop lokal | Cocok pasar sensitif langganan | Tidak ada recurring revenue; support tak berkelanjutan |

---

## 2. Rekomendasi Model Harga: **Flat per Sekolah per Bulan** ✅

**Keputusan: model utama = flat per sekolah per bulan**, dengan add-on per modul dan tier Enterprise kustom.

**Alasan:**
1. **Kebiasaan pasar Indonesia** — pembanding lokal terdekat (AdminSekolah, PPDB system) memakai flat; kepala sekolah/TU Indonesia lebih mudah menyetujui "Rp 999 rb/bulan" daripada "Rp 2.500 × 437 siswa".
2. **Insentif adopsi penuh** — per-siswa menghukum sekolah yang mendaftarkan seluruh siswa ke PWA (padahal itu KPI kita: ≥70% ortu aktif). Flat membuat sekolah bebas onboard 100% siswa tanpa biaya tambahan.
3. **Prediktabilitas anggaran** — sekolah menyusun RKAS tahunan; tagihan tetap jauh lebih mudah dianggarkan daripada tagihan fluktuatif mengikuti jumlah siswa.
4. **Operasional sederhana** — tidak ada sengketa "siswa aktif dihitung kapan", tidak ada invoice koreksi tiap bulan.
5. **Jangkar psikologis kuat** — "lebih murah dari SPP satu siswa" adalah kalimat jualan satu baris yang ampuh.

**Hybrid terbatas (pengaman ARPU):** kuota siswa per tier (300 / 1.500 / unlimited). Sekolah yang melewati kuota naik tier — ini menangkap value dari sekolah besar tanpa kompleksitas per-siswa. Enterprise (>1.500 siswa atau butuh dedicated) memakai harga kustom tahunan.

**Siklus tahunan:** bayar 12 bulan, **gratis 2 bulan** (efektif diskon ±16,7%) — selaras tahun ajaran dan RKAS sekolah.

> ✅ **Disetujui product owner 9 Oktober 2026** — PRD §9.1 sudah diperbarui menjadi flat per sekolah per bulan.

---

## 3. Matriks Fitur per Tier

Legenda: ✅ termasuk · 💰 add-on berbayar — hanya untuk tier Basic/Rintisan (harga di §5); Pro & Enterprise all-inclusive · ❌ tidak tersedia · kuota angka = batas tier.

> **Keputusan produk 9 Okt 2026:** model 2-tier — Basic + plugin à la carte, Pro all-inclusive (seluruh 16 modul). Lihat PRD §9.1.

### 3.1 Modul inti & katalog add-on (16 modul, konsisten dengan PRD §8)

| # | Modul / Fitur | Basic | Pro | Enterprise |
|---|---|---|---|---|
| – | **Modul Inti Akademik** (§7: siswa, wali, guru, kelas, mapel, jadwal, absensi, nilai, tahun ajaran, pengguna, laporan, dashboard, PWA) | ✅ | ✅ | ✅ |
| 1 | Keuangan & Pembayaran SPP | 💰 | ✅ | ✅ |
| 2 | PPDB Online | 💰 | ✅ | ✅ |
| 3 | E-Rapor & Cetak Rapor | 💰 | ✅ | ✅ |
| 4 | Tata Tertib & Poin Pelanggaran | 💰 | ✅ | ✅ |
| 5 | BK & Konseling | 💰 | ✅ | ✅ |
| 6 | Perpustakaan Digital | 💰 | ✅ | ✅ |
| 7 | Ekstrakurikuler & Prestasi | ✅ | ✅ | ✅ |
| 8 | LMS Mini / Tugas Online | 💰 | ✅ | ✅ |
| 9 | Notifikasi Terjadwal (broadcast) | 💰 | ✅ | ✅ |
| 10 | Inventaris & Aset | 💰 | ✅ | ✅ |
| 11 | UKS & Kesehatan | 💰 | ✅ | ✅ |
| 12 | Antar-Jemput / Transportasi | 💰 | ✅ | ✅ |
| 13 | Kantin/Koperasi Digital | 💰 | ✅ | ✅ |
| 14 | Survei & Evaluasi | 💰 | ✅ | ✅ |
| 15 | Alumni & Karier | 💰 | ✅ | ✅ |
| 16 | Organisasi (grup sekolah) | 💰 | 💰 | 💰 |

### 3.2 Kapabilitas platform

| Kapabilitas | Basic | Pro | Enterprise |
|---|---|---|---|
| PWA siswa & orang tua | ✅ | ✅ | ✅ |
| Custom domain (CNAME) | ❌ | ❌ | ✅ |
| Database | Shared | Shared | Dedicated (opsional) |
| SLA uptime | 99% | 99,5% | 99,9% |
| Support | Email (2×24 jam) | WA prioritas (1×24 jam) | Dedicated CSM + SLA respons |
| Onboarding | Mandiri (trial) | Assisted (remote) | Dedicated + training on-site |
| API & webhook keluar | ❌ | Webhook | Webhook + API key + sandbox |
| SSO (Google/Microsoft) | ❌ | ❌ | ✅ |
| White-label PWA (ikon/nama penuh) | ❌ | ❌ | ✅ |
| Retensi audit log | 1 tahun | 2 tahun | 5 tahun |
| Backup | Harian, retensi 7 hari | Harian, retensi 30 hari | + PITR & DR lintas region |
| Status page & laporan insiden | ❌ | ✅ | ✅ |

---

## 4. Tabel Kuota per Tier (menutup gap T-04)

| Kuota | Basic | Pro | Enterprise |
|---|---|---|---|
| Maks. siswa aktif | 300 | 1.500 | Tanpa batas |
| Maks. user staf | 20 | 75 | Tanpa batas |
| Storage (dokumen/foto) | 10 GB | 50 GB | 200 GB+ |
| Pesan WA/bulan (termasuk) | 500 | 3.000 | 15.000 |
| Rate limit API | 60/menit | 300/menit | Kustom |
| Perangkat PWA per akun | 3 | 5 | 10 |

Aturan: peringatan di 80% & 100% kuota → penawaran upgrade; **bukan** pemblokiran mendadak (kecuali trial abuse). Kelebihan pesan WA: Rp 150/pesan.

---

## 5. Harga Rekomendasi Final (IDR)

> Semua angka = **rekomendasi awal**, wajib divalidasi via pilot (lihat §6). Harga tahunan = 10× bulanan (gratis 2 bulan).

### 5.1 Paket langganan

| | **Basic** | **Pro** ⭐ andalan | **Enterprise** |
|---|---|---|---|
| **Bulanan** | **Rp 499.000** | **Rp 999.000** | Mulai **Rp 2.500.000** (kustom) |
| **Tahunan** | **Rp 4.990.000** | **Rp 9.990.000** | Kontrak tahunan |
| Target | Sekolah kecil (<300 siswa), SD/MI, madrasah | Sekolah menengah (300–1.500 siswa) — mayoritas pasar | >1.500 siswa / grup sekolah / butuh dedicated |

**Rasional per angka:**
- **Basic Rp 499 rb** ≈ 0,5× SPP satu siswa SD swasta menengah (Rp 1 jt) — di bawah harga psikologis AdminSekolah (Rp 600 rb) untuk paket yang jauh lebih lengkap; terjangkau untuk SD kecil/madrasah.
- **Pro Rp 999 rb** ≈ 1× SPP siswa — kalimat jualan: *"lebih murah dari SPP satu siswa sebulan"*. Pada 500 siswa = Rp 2.000/siswa/bulan, di bawah Fekara ($0,20 ≈ Rp 3.300) dan jauh di bawah Eduwonka enterprise.
- **Enterprise mulai Rp 2,5 jt** — jangkar: biaya 1 staf TU/honorer; mencakup dedicated CSM + opsi DB dedicated (biaya infra enterprise ±Rp 89 rb/sekolah/bln di skala — margin sehat).

### 5.2 Harga add-on per modul

| Add-on | Harga | Model | Rasional |
|---|---|---|---|
| Keuangan & SPP (di Basic) | Rp 499.000/bln | Bulanan | Pain point terbesar; ROI jelas (menekan tunggakan) |
| PPDB Online | Rp 750.000/musim | Per periode PPDB | Di bawah PPDB standalone Rp 1,75 jt/**bulan**; dipakai 2–3 bulan/tahun |
| E-Rapor | Rp 299.000/bln (di Basic) | Bulanan | Bersaing dengan e-Rapor Kemendikbud **gratis** → harga rendah, jual nilai tambah (QR verifikasi, deskripsi otomatis) |
| LMS Mini | Rp 349.000/bln | Bulanan | Pengganti Google Classroom berbayar sebagian |
| Perpustakaan Digital | Rp 249.000/bln | Bulanan | — |
| Notifikasi Terjadwal | Rp 199.000/bln + kuota | Bulanan | Di atas kuota paket: Rp 150/pesan |
| Tata Tertib / BK / Survei (di Basic) | Rp 149.000/bln per modul | Bulanan | — |
| Inventaris / UKS / Transportasi / Kantin / Alumni | Rp 149–249.000/bln per modul | Bulanan | Sesuai kompleksitas |
| Organisasi (grup sekolah) | Rp 499.000/bln | Per organisasi | Mencakup semua sekolah anggota; jangkar: 1 paket Basic |

### 5.3 Contoh simulasi tagihan

| Profil sekolah | Komposisi | Total/bulan |
|---|---|---|
| SD swasta 200 siswa | Basic Rp 499 rb | **Rp 499.000** |
| SMP swasta 600 siswa | Pro Rp 999 rb + PPDB (musim) | **Rp 999.000** (+ Rp 750 rb di musim PPDB) |
| SMA swasta 900 siswa | Pro Rp 999 rb (semua modul termasuk) | **Rp 999.000** |
| Grup 4 sekolah (yayasan) | 4× Pro tahunan + Organisasi | **±Rp 3,8 jt** (efektif Rp 950 rb/sekolah) |

---

## 6. Strategi Monetisasi

1. **Trial 30 hari setara Pro, tanpa kartu kredit** (PRD §9.3) — biarkan sekolah merasakan modul bernilai (Keuangan, E-Rapor) lalu *downgrade* ke Basic berbayar atau tetap di Pro. Target konversi trial-to-paid ≥ 25%.
2. **Land-and-expand** — masuk via Basic (murah, minim friksi), ekspansi via add-on per modul dan naik tier saat siswa bertambah. Dashboard admin menampilkan modul nonaktif sebagai *upsell* kontekstual ("Aktifkan E-Rapor untuk semester ini").
3. **Diskon tahunan** (gratis 2 bulan) — selaras RKAS; mengunci churn tahunan dan memperbaiki cash flow.
4. **Harga khusus sekolah kecil** — <100 siswa: **Rp 299.000/bln** (tier "Rintisan", kuota 100 siswa). Menangkap SD pelosok/madrasah/TPQ yang sensitif harga; tetap di atas biaya infra per sekolah (±Rp 89–207 rb).
5. **Siklus musiman** — dorong PPDB Online 2 bulan sebelum tahun ajaran; dorong E-Rapor 1 bulan sebelum akhir semester (kampanye WA/email tersegmentasi).
6. **Program rujukan** — sekolah yang mereferensikan: 1 bulan gratis per konversi (dibayar setelah referee 3 bulan aktif).
7. **Validasi harga via pilot** — 5–10 sekolah pilot membayar harga promo 50% selama 6 bulan dengan komitmen memberi umpan balik harga (willingness-to-pay interview). Harga final ditetapkan dari data ini, bukan asumsi.

---

## 7. Catatan Implementasi (mapping ke Dok 02)

Tabel `plans` (Dok 02 §8) sudah memiliki: `slug`, `nama`, `harga_bulanan`, `harga_tahunan`, `max_siswa`, `max_storage_mb`, `max_user_staf`, `fitur` (json). **Kolom yang perlu ditambah:**

| Kolom | Tipe | Kegunaan |
|---|---|---|
| `rate_limit_per_minute` | integer | Penegakan T-04 per tier (60 / 300 / null=kustom) |
| `sla_uptime` | decimal(4,2) | 99.00 / 99.50 / 99.90 — tampil di halaman status & kontrak |
| `support_level` | string/enum | `email` / `wa_prioritas` / `csm` — routing tiket support |
| `wa_quota_monthly` | integer | Kuota pesan WA termasuk (500 / 3.000 / 15.000) |
| `trial_hari` | integer | Default 30; memungkinkan promo trial berbeda |
| `harga_tahunan` | (sudah ada) | Isi = 10× bulanan |

Tabel `modules` sudah memiliki `termasuk_paket` (json), `harga_addon_bulanan`, `prasyarat` — cukup; isi `termasuk_paket` mengikuti matriks §3.1 (mis. keuangan: `["pro","enterprise"]`, ekstrakurikuler: `["basic","pro","enterprise"]`, ppdb: `[]`). Tambahkan tier `rintisan` bila program sekolah kecil (§6.4) diadopsi.

**Perubahan PRD yang diusulkan:** §9.1 (model per-siswa → flat per sekolah), §9.2 (ganti tabel harga ilustratif dengan §5 dokumen ini), §9.5 (tambah rate limit per tier dari §4).

---

## 8. Sumber Riset

| # | Temuan | Sumber (diakses 9 Okt 2026) |
|---|---|---|
| 1 | Fekara: $0,20/siswa atau $350 sekali bayar | http://blog.kejarcita.id/sistem-administrasi-sekolah-online/ |
| 2 | PPDB Online System: Rp 1,75 jt/bulan | https://www.tokopedia.com/gcmediashop/paket-sekolah-kece-ppdb-online-system |
| 3 | Software sekolah desktop: Rp 1,5 jt sekali bayar | http://www.software-id.com/central.php?aksi=software_detil&kode=software-sekolah |
| 4 | AdminSekolah.net: ±Rp 202–600 rb/bulan per unit | https://adminsekolah.net/promo-akhir-tahun-untuk-sistem-administrasi-sekolahmu/promo-akhir-tahun-2020003/ |
| 5 | Google Workspace for Education: gratis s.d. $5/user/bulan | https://www.jagoweb.com/harga-google-workspace-for-education-solusi-terbaik-untuk-pendidikan |
| 6 | Larangan BOS untuk software pelaporan keuangan BOS | https://www.tokobinamandiri.com/aturan-dana-bos-cara-belanja-atk-siplah-sistem-informasi-pengadaan-sekolah |
| 7 | BOS boleh untuk langganan platform digital/LMS & penilaian (Permendikbud 63/2022) | https://www.sevenstarindonesia.com/2025/07/dana-bos-digunakan-untuk-apa-saja-ini.html |
| 8 | e-Rapor Kemendikbud gratis & terintegrasi Dapodik | https://id.scribd.com/document/979594521/Informasi-Umum-Aplikasi-e-Rapor |
| 9 | SPP SD swasta: Al Fath Rp 1,75 jt; Al Lauzah Rp 1 jt; Sinar Cendekia Rp 1,42 jt | https://www.datapendidikan.com/blog/sd-islam-al-fath-tangerang/ |
| 10 | Kisaran SPP: SD swasta Rp 1,5–2,2 jt; SMP Rp 2–4,5 jt; SMA Rp 150 rb–5 jt | http://finansialku.com/perencana-keuangan/spp/ |
| 11 | Aplikasi rapor PHP: Rp 175 rb sekali bayar | https://www.tokopedia.com/tutoriallengkap-1/software-aplikasi-e-raport-kurikulum-2013-berbasis-website-php |

---

## Agenda Pra-Sales Enterprise & Overage (P1-10, pasca-MVP)

1. **Overage WA** di atas kuota Pro (3.000 msg/bln): skema harga per 1.000 pesan + routing alternatif — dirancang sebelum GA, bukan MVP.
2. **Cost floor Enterprise**: kontrak tahunan minimum berbasis BOM dedicated (DB, infra, CSM hours) — angka shared-infra Rp 89 rb/sekolah **tidak boleh** dipakai untuk justifikasi harga Enterprise.
3. **Contribution margin per kohor** (termasuk CAC, onboarding hours, payment fee, churn) — dashboard finance sebelum skala.
