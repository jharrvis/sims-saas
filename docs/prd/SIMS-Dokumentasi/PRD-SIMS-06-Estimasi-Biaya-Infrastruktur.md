# Dok 06 — Estimasi Biaya Infrastruktur & Unit Economics

| Atribut | Keterangan |
|---|---|
| Versi Dokumen | 1.0 |
| Tanggal | 9 Oktober 2026 |
| Acuan Arsitektur | PRD §6 (Laravel 13, monolit modular, shared-schema multi-tenant), §9 (model bisnis SaaS), §17 (backup, WAF, sertifikat) |

> ⚠️ **Semua angka dalam dokumen ini adalah ESTIMASI** untuk perencanaan. Wajib divalidasi ulang saat pengadaan (harga cloud berubah, promo, dan kebutuhan aktual bisa berbeda ±20–30%).

## 1. Asumsi & Metodologi

| Asumsi | Nilai |
|---|---|
| Mata uang & kurs acuan | IDR; **Rp 16.500 = USD 1** |
| Region | **Jakarta** (latensi terbaik untuk pengguna Indonesia) atau Singapore (fallback bila layanan tidak tersedia di Jakarta) |
| Profil 1 sekolah (rata-rata campuran SD/SMP/SMA) | 500 siswa aktif, 40 user staf, 10 GB storage |
| Hari efektif sekolah | 200 hari/tahun |
| Tingkat alpa harian (pemicu notifikasi WA) | 2% dari siswa |
| Ukuran data | Foto siswa ±300 KB; dokumen PPDB ±5 MB/pendaftar (musiman) |
| Klasifikasi biaya | **Infra** (server/DB/storage/jaringan) vs **Pihak ketiga** (WA, payment gateway, dsb.) vs **SDM operasional** — dipisahkan di tiap tabel |
| Belanja modal (capex) | Diasumsikan **nol** — seluruhnya model sewa/opex bulanan (cloud) |

Metode: tiap tier dihitung bottom-up per komponen, lalu dihitung **biaya infra per sekolah per bulan** untuk dibandingkan dengan harga paket ilustratif PRD §9 (Rp 2.500–4.000/siswa/bulan).

---

## 2. Estimasi per Tier Skala

### 2.1 Tier Pilot — 1–10 sekolah, < 5.000 siswa

Strategi: **VPS hemat**, Redis + worker di server yang sama, backup snapshot. Cocok untuk validasi produk & 1–2 sekolah pilot.

| # | Komponen | Spesifikasi | Estimasi/bln (IDR) | Catatan |
|---|---|---|---|---|
| 1 | App server (VPS) | 4 vCPU / 8 GB RAM / 160 GB SSD, Jakarta | 750.000 | Nginx + PHP-FPM + Horizon worker |
| 2 | DB server (VPS) | 4 vCPU / 8 GB RAM / 240 GB SSD, PostgreSQL 16 | 850.000 | Tidak terekspos ke internet (§17.2) |
| 3 | Redis | Berjalan di app server | 0 | Cache + queue + session |
| 4 | Object storage (S3-compatible) | 200 GB (foto, dokumen, export) | 250.000 | Pilih yang egress murah/gratis |
| 5 | Backup snapshot harian | 2 VPS, retensi 7 hari | 200.000 | DB dump harian terpisah via cron |
| 6 | Domain + DNS | `sims.id` + wildcard, cert Let's Encrypt | 25.000 | Amortisasi biaya tahunan domain |
| 7 | Monitoring dasar | Uptime Kuma (self-host) + alert Telegram/WA | 0 | — |
| | **TOTAL Tier Pilot** | | **± Rp 2.075.000/bln** | |

### 2.2 Tier Growth — 50 sekolah, ±50.000 siswa (target awal PRD §6.4)

Strategi: **managed services** untuk DB & Redis (HA), load balancer, WAF — mengejar SLA 99,5% paket Pro.

| # | Komponen | Spesifikasi | Estimasi/bln (IDR) | Catatan |
|---|---|---|---|---|
| 1 | Load balancer (managed) | HTTP/HTTPS, health check | 250.000 | — |
| 2 | App server 2× | 4 vCPU / 8 GB RAM / 160 GB SSD per node | 1.500.000 | Horizontal scale; deploy tanpa downtime |
| 3 | Managed PostgreSQL (HA) | 4 vCPU / 8 GB RAM / 500 GB SSD + standby | 3.000.000 | Backup otomatis + point-in-time recovery |
| 4 | Managed Redis (HA) | 2 GB RAM, replika | 400.000 | Cache + queue terpisah dari app |
| 5 | Object storage + CDN | 1 TB storage + CDN untuk aset publik | 800.000 | CDN menekan egress origin |
| 6 | WAF + DDoS protection | Paket bisnis dasar (aturan OWASP) | 350.000 | Wajib per §17.2 |
| 7 | Backup & DR | Snapshot terjadwal + retensi 30 hari | 300.000 | RPO ≤ 24 jam, RTO ≤ 4 jam (§6.4) |
| 8 | Log terpusat | Agregator log + dashboard (self-host ringan) | 200.000 | Log keamanan terpisah (§17.3) |
| 9 | Domain, DNS & sertifikat | Wildcard + on-demand untuk custom domain | 50.000 | Rate limit ACME dipantau |
| | **TOTAL Tier Growth** | | **± Rp 6.850.000/bln** | |

### 2.3 Tier Scale — 200 sekolah, ±200.000 siswa

Strategi: autoscale pool, read replica DB, Redis HA/cluster, multi-AZ.

| # | Komponen | Spesifikasi | Estimasi/bln (IDR) | Catatan |
|---|---|---|---|---|
| 1 | Load balancer (managed, HA) | Multi-AZ | 400.000 | — |
| 2 | App pool autoscale 4–6 node | 4 vCPU / 8 GB RAM per node | 4.000.000 | Scale up otomatis saat puncak (PPDB/awal semester) |
| 3 | Managed PostgreSQL | Primary 8 vCPU/16 GB + 1 read replica + standby | 8.000.000 | Laporan berat diarahkan ke replica |
| 4 | Redis HA/cluster | 4 GB, multi-AZ | 1.000.000 | — |
| 5 | Object storage + CDN | 5 TB + CDN global | 2.500.000 | Foto/dokumen 200 sekolah |
| 6 | WAF + DDoS (paket lanjutan) | Aturan kustom per tenant, bot management dasar | 1.000.000 | — |
| 7 | Backup & DR lintas region | Replikasi backup ke region kedua | 800.000 | Siap data residency Enterprise |
| 8 | Observabilitas | Log + metrik + tracing (managed/self-host) | 500.000 | Termasuk dashboard keamanan §17.3 |
| 9 | Domain, DNS & sertifikat | On-demand cert untuk ratusan custom domain | 100.000 | Otomatisasi penuh (ACME) |
| | **TOTAL Tier Scale** | | **± Rp 17.800.000/bln** | |

> Konsistensi antar tier: Growth ≈ 3,3× Pilot; Scale ≈ 2,6× Growth — naik sejalan dengan 5× dan 4× lipat jumlah sekolah, menunjukkan skala ekonomi (biaya per sekolah **turun**).

## 3. Biaya Pihak Ketiga (di luar infra)

Biaya ini **variabel mengikuti pemakaian** dan sebagian dapat diteruskan ke sekolah (model PRD §9.2: kuota + Rp 150/pesan).

| # | Layanan | Model biaya | Estimasi | Contoh volume Tier Growth (50 sekolah) |
|---|---|---|---|---|
| 1 | **WhatsApp gateway** (Fonnte/Wablas/dll.) | Per pesan terkirim | Rp 150–300/pesan | Notifikasi alpa: 2% × 50.000 siswa = 1.000 pesan/hari × 200 hari = 200.000 pesan/tahun ≈ **Rp 2,5 jt/bln** (di Rp 150/pesan). Broadcast insidental di luar ini. |
| 2 | **Payment gateway** (Midtrans/Xendit) | MDR per transaksi | QRIS 0,7%; VA bank ±Rp 4.000/tx; e-wallet 1,5–2%; kartu kredit ±2,9% + Rp 2.000 | **% dari GMV**, bukan biaya tetap. Keputusan bisnis: MDR ditanggung sekolah atau dibebankan ke tagihan. Recurring langganan memakai tarif yang sama. |
| 3 | **SMS OTP fallback** | Per SMS | Rp 350–500/SMS | Hanya fallback bila WA gagal; estimasi **Rp 200 rb/bln** |
| 4 | **Push notification (FCM)** | Gratis | Rp 0 | — |
| 5 | **Email transaksional** (SES/Mailgun) | Per 1.000 email | ±Rp 1.650/1.000 (SES) | 100 rb email/bln (invoice, pengingat, verifikasi) ≈ **Rp 165 rb/bln** |
| 6 | **Domain + sertifikat** | Tahunan + gratis | Domain `.id` ±Rp 250 rb/thn; Let's Encrypt gratis | Custom domain tenant: verifikasi TXT + cert on-demand otomatis; perhatikan rate limit ACME (50 cert/minggu) |
| | **TOTAL pihak ketiga (Growth, tanpa MDR)** | | **± Rp 2,9 jt/bln** | MDR payment gateway terpisah (variabel dari GMV) |

## 4. Lingkungan Non-Produksi

| Environment | Spesifikasi | Estimasi/bln (IDR) | Catatan |
|---|---|---|---|
| **Development** | 1 VPS 2 vCPU / 4 GB RAM (DB + Redis lokal di dalamnya) | 300.000 | Per tim dev; bisa sharing 1 VPS untuk 2–3 dev |
| **Staging** | 1 VPS 4 vCPU / 8 GB RAM + object storage kecil (mirror arsitektur prod Tier Pilot) | 800.000 | Data dummy; dipakai UAT & load test ringan |
| CI/CD (GitHub Actions) | Menit build + runner | 150.000 | Estimasi 3.000 menit/bln paket tim |
| **TOTAL non-prod** | | **± Rp 1.250.000/bln** | Tetap, tidak tergantung jumlah tenant |

## 5. SDM Operasional (baris biaya)

Bukan rencana rekrutmen — hanya agar total *cost of running* realistis.

| Tier | Kebutuhan | Model | Estimasi/bln (IDR) |
|---|---|---|---|
| Pilot | DevOps fractional (satu orang pegang deploy, backup, monitoring; 20–30% waktu) | Paruh waktu / konsultan | 5.000.000 |
| Growth | DevOps 1 orang (atau fractional senior + on-call) | Full-time / retainer | 15.000.000 |
| Scale | SRE 1–2 orang + on-call rotation | Full-time | 30.000.000 |

> Catatan: di Tier Pilot/Growth awal, peran ini umumnya dirangkap engineer backend yang sama (biaya sudah ada di tim produk) — angka di atas untuk skenario perlu orang khusus.

## 6. Unit Economics — Biaya per Sekolah & Margin

Asumsi ARPU dari harga flat yang disetujui (PRD §9.1): campuran 70% Basic (Rp 499 rb), 25% Pro (Rp 999 rb), 5% Enterprise (Rp 2,5 jt) → **ARPU ≈ Rp 724.000/bln** per sekolah.

| Tier | Infra/bln | Sekolah | **Infra per sekolah/bln** | ARPU/sekolah | **Margin kotor (infra)** | BEP infra* |
|---|---|---|---|---|---|---|
| Pilot | Rp 2.075.000 | 10 | **Rp 207.500** | Rp 724.000 | ±71% | 3 sekolah |
| Growth | Rp 6.850.000 | 50 | **Rp 137.000** | Rp 724.000 | ±81% | 10 sekolah |
| Scale | Rp 17.800.000 | 200 | **Rp 89.000** | Rp 724.000 | ±88% | 25 sekolah |

\* *BEP infra = titik impas biaya infrastruktur saja (infra ÷ ARPU). BEP usaha sebenarnya lebih tinggi karena harus menutup SDM operasional, pihak ketiga, dan tim produk.*

**Total cost of running per bulan (contoh Tier Growth):**

| Pos | Estimasi/bln (IDR) |
|---|---|
| Infra produksi | 6.850.000 |
| Non-produksi | 1.250.000 |
| Pihak ketiga (WA, SMS, email; tanpa MDR) | 2.900.000 |
| SDM operasional (DevOps) | 15.000.000 |
| **Total** | **± Rp 26.000.000/bln** |
| Pendapatan 50 sekolah × Rp 724 rb | Rp 36.200.000/bln |
| **Surplus kotor operasional** | **± Rp 10.200.000/bln** (sebelum gaji tim produk & MDR) |

Kesimpulan: dengan harga flat yang disetujui, **unit economics tetap sehat** — margin infra 71–88% dan BEP infra tercapai di 3–25 sekolah. Surplus operasional Growth menipis dibanding skenario harga lama (±Rp 49 jt → ±Rp 10 jt), sehingga **menaikkan ARPU via add-on dan paket Pro menjadi kunci** (land-and-expand, selaras strategi Dok 10 §6): tiap sekolah Pro (+Rp 275 rb vs rata-rata) atau 1 add-on (Rp 149–499 rb) mengangkat margin signifikan.

## 7. Rekomendasi Strategi Infrastruktur

1. **Mulai dari Tier Pilot (VPS Jakarta).** Jangan langsung managed services — validasi produk dulu dengan 1–2 sekolah pilot. Latensi Jakarta > Singapore untuk pengguna Indonesia.
2. **Naik ke managed DB/Redis saat salah satu trigger terpenuhi:** (a) > 30 tenant aktif; (b) insiden DB yang mengganggu jam sekolah; (c) butuh SLA 99,5% untuk menjual paket Pro. Managed DB adalah lompatan biaya terbesar — tunda sampai perlu.
3. **Pisahkan beban baca sejak Growth:** laporan/agregasi berat (rekap nilai, konsolidasi) ke read replica; request interaktif ke primary.
4. **Manfaatkan queue untuk puncak musiman:** PPDB, input nilai akhir semester, dan cetak rapor massal **wajib** via Horizon job — ini yang membuat 2 app server cukup untuk 50 sekolah.
5. **Hemat yang terukur:** reserved/savings plan untuk komponen yang stabil (DB, app baseline); object storage dengan egress gratis/murah (pertimbangkan R2-kompatibel); CDN agresif untuk aset publik & PWA; autoscale terjadwal (scale up hanya di jam sibuk/puncak PPDB).
6. **Custom domain (CNAME):** otomatisasi penuh sejak awal (verifikasi TXT + ACME on-demand) supaya tidak jadi beban operasional manual per pelanggan Enterprise.
7. **Satu prinsip:** infra mengikuti *committed* tenant, bukan proyeksi — naikkan tier saat utilisasi CPU/RAM/DB > 70% selama 2 minggu berturut-turut, bukan karena target di slide.

## 8. Risiko Biaya & Mitigasi

| # | Risiko | Dampak | Mitigasi |
|---|---|---|---|
| 1 | **Lonjakan storage** — foto resolusi tinggi, dokumen PPDB massal, backup menumpuk | Storage 5× lipat dalam 1 semester | Kompresi + resize otomatis saat upload; lifecycle policy (arsip ke cold storage setelah 1 tahun); kuota storage per paket ditegakkan (§9.5) |
| 2 | **Broadcast WA massal** oleh sekolah (pengumuman ke ribuan wali) | Tagihan WA meledak | Rate limit + kuota broadcast per paket; estimasi biaya tampil **sebelum** kirim; model *pass-through* (sekolah bayar kelebihannya) |
| 3 | **Egress bandwidth** — unduhan rapor massal, PWA update | Biaya transfer tak terduga | CDN di depan object storage; cache agresif; pilih provider dengan egress murah |
| 4 | **Trial abuse** — akun trial dipakai spam/scraping | Biaya WA + komputasi terbuang | Batasan trial §9.5 (maks. 100 siswa, tanpa broadcast massal, rate limit ketat); deteksi anomali §17.3 |
| 5 | **MDR payment gateway** menggerus margin add-on Keuangan | 2–3% dari setiap pembayaran SPP | Jelaskan di kontrak: MDR ditanggung sekolah / ditambahkan ke tagihan; pilih metode termurah sebagai default (QRIS 0,7%) |
| 6 | **Scope creep infra Enterprise** — dedicated DB + data residency per pelanggan besar | Biaya khusus tak tertutup harga paket | Dedicated infra **hanya** untuk kontrak Enterprise dengan harga kustom yang sudah memperhitungkan biayanya |

---

*Dokumen pendamping: PRD-SIMS-Multi-Sekolah.md (§6, §9, §17). Estimasi ini sebaiknya ditinjau ulang tiap 6 bulan atau setiap kali berpindah tier.*
