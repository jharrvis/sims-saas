# Dok 08 — Audit Dokumentasi, Gap Analysis & Rekomendasi

| Atribut | Keterangan |
|---|---|
| Tanggal audit | 9 Oktober 2026 |
| Cakupan | 8 dokumen (±4.700 baris): PRD v1.2 + Dok 02–07 |
| Metode | Baca struktur + verifikasi silang otomatis (kode FR, nama tabel, permission, endpoint, kode modul, peran) antar dokumen |
| Hasil | 3 inkonsistensi ditemukan & **sudah diperbaiki** saat audit; 19 gap + 7 ide tambahan teridentifikasi |

---

## 1. Ringkasan Eksekutif

Dokumentasi secara umum **siap menjadi acuan Sprint 1** — arsitektur, model data, API, backlog, dan RBAC konsisten satu sama lain setelah 7 putaran perbaikan (4 pada audit pivot sebelumnya + 3 pada audit ini). Gap yang tersisa sebagian besar berada di **luar inti MVP**: dokumen legal, go-to-market, dan beberapa alur pendukung (lupa password, Dapodik, template notifikasi).

### Skor kesiapan per dokumen

| Dokumen | Skor | Catatan |
|---|---|---|
| PRD Induk (v1.2) | 9/10 | Lengkap: SaaS, modul, bisnis, keamanan §17. Kurang: FR formal untuk kebutuhan SaaS (masih memakai ref `SaaS-01..08` informal) |
| Dok 02 — Database | 9/10 | 33 migrasi + konvensi modul + kuota. Perhatian: strategi restore per-tenant (lihat T-01) |
| Dok 03 — API | 9/10 | 33 endpoint, tabel ringkasan kini selaras dengan section |
| Dok 04 — Backlog | 8.5/10 | 104 stories, 10 sprint. US-004 sudah diselaraskan ke model platform |
| Dok 05 — RBAC | 9/10 | 105 permission, dua guard. Peran Bendahara menunggu modul Keuangan (tercatat) |
| Dok 06 — Biaya | 8/10 | Estimasi realistis per tier. Angka wajib revalidasi saat pengadaan (±20–30%) |
| Dok 07 — Wireframe | 8/10 | 14 layar; belum mencakup layar lupa password & manajemen template notifikasi (lihat G-01, G-04) |

---

## 2. Inkonsistensi yang Ditemukan & Diperbaiki Saat Audit

| ID | Lokasi | Temuan | Perbaikan |
|---|---|---|---|
| A-01 | Dok 03 | Nomor §9 hilang (lompat §8 → §10) setelah penambahan section SaaS | Renumber §10→9, §11→10, §12→11, §13→12 + seluruh rujukan internal & changelog |
| A-02 | Dok 03 §6 | Tabel ringkasan memakai `{id}` sedangkan section memakai `{siswa_id}`; satu baris tabel (`jadwal/minggu-ini` versi ortu) tidak punya section | Parameter diseragamkan ke `{siswa_id}`; baris tanpa section dihapus. Tabel kini 33 baris = 33 section |
| A-03 | Dok 04 US-004 | Masih berwatak pra-pivot ("superadmin tenant switcher kelola banyak sekolah") — bertentangan dengan model platform + impersonasi tercatat | Ditulis ulang: peran **Platform Support**, story impersonasi tercatat (selaras US-102 & Dok 05 §5) |

*(Audit pivot sebelumnya sudah memperbaiki: masa trial 30 hari, registry 16 modul, `tenant_usages`, format permission `{modul}.{resource}.{aksi}`, kode error `MODULE_DISABLED`.)*

---

## 3. Gap Fungsional

| ID | Gap | Dampak | Rekomendasi |
|---|---|---|---|
| G-01 | **Lupa/reset password mandiri untuk staf** — FR-901 hanya mengatur reset oleh admin | Staf terkunci = beban support; standar SaaS wajib ada | Tambah story: kirim link reset via email/WA terdaftar, token sekali pakai ≤60 menit. **Fase 1** |
| G-02 | **Ganti password via PWA** — layar P-06 Profil ada, endpoint tidak ada | Pengguna PWA tidak bisa ganti password sendiri | Tambah `POST /auth/password` (password lama + baru). **Fase 1** |
| G-03 | **Integrasi Dapodik nol story** — disebut di PRD Fase 2, tapi tidak ada satu pun item backlog | Risiko scope Fase 2 membengkak tanpa persiapan | Buat epic placeholder + 3–5 story persiapan di carry-over: studi format, adapter impor/ekspor, uji dengan data dummy. **Persiapan Fase 1 akhir** |
| G-04 | **Manajemen template notifikasi WA per sekolah** — template disebut ada, tapi siapa & lewat UI apa tidak didefinisikan | Template hardcoded = setiap perubahan butuh developer | Tambah layar W-09 (di wireframe) + story CRUD template + pratinjau + variabel (`{{nama_siswa}}`). **Fase 1/2** |
| G-05 | **Global search** lintas modul (cari siswa/guru dari mana saja) | UX admin; sepele tapi sering diminta | Tambah ke backlog Fase 2 (search scoped tenant, ≤1 dtk) |
| G-06 | **Bulk user management** — aktivasi/nonaktif massal, reset password massal awal tahun ajaran | Operasional awal semester | Story kecil, **Fase 1** |
| G-07 | **Mutasi siswa antar tenant** — siswa pindah ke sekolah pelanggan lain: bagaimana data portabel? | Kasus nyata tiap tahun ajaran | Keputusan desain: ekspor paket data standar (JSON+PDF) yang bisa diimpor tenant tujuan **dengan persetujuan kedua belah pihak**; tanpa transfer otomatis (privasi). **Fase 2** |
| G-08 | **Guru mengajar di >1 sekolah (multi-tenant)** — satu akun, dua tenant? | Mempengaruhi desain auth & token | **Keputusan produk sebelum Sprint 1**: opsi A — satu akun per tenant (sederhana, direkomendasikan); opsi B — akun gabungan dengan switcher tenant. Rekomendasi: opsi A untuk MVP |

---

## 4. Gap Teknis

| ID | Gap | Dampak | Rekomendasi |
|---|---|---|---|
| T-01 | **Restore satu tenant di shared-schema DB itu sulit** — backup full mengembalikan semua tenant sekaligus | Jika satu sekolah butuh rollback data (mis. salah impor massal), tidak bisa restore selektif | Strategi resmi: (1) *point-in-time recovery* ke DB klon + ekspor logis per `school_id` untuk kasus insidental; (2) fitur "snapshot pra-impor massal" (undo import) sebagai mitigasi operasional; (3) dokumentasikan di runbook DR. **Sebelum go-live** |
| T-02 | **Kebijakan depresiasi API** — v1 ada, aturan kapan v2 & berapa lama v1 didukung tidak ada | Integrator (sekolah/partner) butuh kepastian | Tambah ke Dok 03: *deprecation policy* (pengumuman H-90, header `Sunset`, dukungan N-1 versi). **Fase 2** |
| T-03 | **Outbound webhooks untuk sekolah** — sekolah tidak bisa menerima event (siswa baru, pembayaran lunas) ke sistemnya sendiri | Nilai tambah integrasi, umum di SaaS | Jadikan fitur paket Pro+: `webhook.subscriptions` + signature + retry. **Fase 2/3** |
| T-04 | **Rate limit per tier paket** — disebut 60/menit global, belum dibedakan per paket | Fair use & upsell | Definisikan di §9 model bisnis: Basic 60/mnt, Pro 300/mnt, Enterprise custom. **Sebelum go-live** |
| T-05 | **Halaman audit log untuk admin tenant** — US-007 mencakup riwayat per record, tapi tidak ada halaman audit global berfilter | Kebutuhan kepatuhan sekolah | Story kecil: halaman log + filter tanggal/aksi/user + export. **Fase 1** |

---

## 5. Gap Bisnis & Legal

| ID | Gap | Dampak | Rekomendasi |
|---|---|---|---|
| B-01 | **Dokumen legal belum ada workstream** — ToS, Privacy Policy, dan **DPA** (perjanjian pemrosesan data) | **Blocker go-live**: UU PDP mewajibkan kejelasan pemrosesan data anak; sekolah (sebagai pengendali data) akan meminta DPA | Workstream legal paralel Sprint 1–4: draft ToS + Privacy Policy + template DPA; §17.7 jadi acuan isi. **Kritis** |
| B-02 | **Go-to-market plan** — pilot program, finalisasi harga, sales motion, materi demo | Dokumen teknis siap, strategi jualan belum | Dokumen terpisah: kriteria sekolah pilot, paket pilot (gratis/diskon), playbook onboarding 14 hari, target konversi |
| B-03 | **Help center & materi onboarding** — US-079 (panduan) ada, tapi belum ada rencana konten (video, KB, FAQ) | Adopsi rendah jika pengguna bingung | Rencana konten per peran + format (video ≤3 menit per tugas utama); mulai produksi di Sprint 6–8 |
| B-04 | **Product analytics** — funnel aktivasi tenant, adopsi fitur/modul tidak direncanakan | Keputusan produk buta data | Definisikan event kunci (registrasi → provisioning → import siswa → absensi pertama → langganan) + dashboard. **Fase 1** |
| B-05 | **NPS/CSAT loop** — tidak ada mekanisme ukur kepuasan | Churn tidak terdeteksi dini | Survei singkat di PWA/panel tiap semester + alert skor rendah ke CS. **Fase 2** |

---

## 6. Ide Tambahan (di luar roadmap saat ini)

| ID | Ide | Nilai | Effort kasar | Usulan fase |
|---|---|---|---|---|
| I-01 | **Asisten AI**: draf deskripsi rapor dari nilai + deteksi anomali (nilai janggal, pola absensi) | Diferensiasi kuat; hemat waktu guru | Sedang (integrasi LLM + guardrail) | Fase 3 |
| I-02 | **Chatbot WA untuk orang tua**: cek kehadiran/nilai/tagihan via chat | Adopsi ortu tanpa install apa pun | Sedang | Fase 2/3 |
| I-03 | **Kartu pelajar digital** (QR di PWA) untuk presensi & perpustakaan | Mengurangi kartu fisik; rapi | Kecil | Fase 2 |
| I-04 | **Developer portal + sandbox API** untuk integrasi IT sekolah/partner | Ekosistem; kunci enterprise | Sedang | Fase 3 |
| I-05 | **White-label app wrapper** (Enterprise): PWA dibungkus jadi aplikasi bermerek sekolah | Nilai jual tier atas | Kecil–Sedang | Fase 3 |
| I-06 | **Penjadwalan pertemuan ortu–guru** (booking slot konsultasi) | Komunikasi terstruktur | Kecil | Fase 3 |
| I-07 | **Gamifikasi siswa**: lencana kehadiran, prestasi, papan apresiasi (bukan ranking nilai) | Engagement siswa | Kecil | Fase 3 |

---

## 7. Rencana Tindak Lanjut (prioritas)

*Status per 9 Oktober 2026: gap yang bisa ditutup di level dokumen sudah dikerjakan (✅) — stories di Dok 04 §8, endpoint di Dok 03, ketentuan di PRD, layar di wireframe. Tersisa pekerjaan yang butuh eksekusi di luar dokumen (⏳).*

**Sebelum Sprint 1 dimulai**
- [x] ✅ Keputusan G-08 — didokumentasikan di PRD §5: satu akun per tenant untuk MVP
- [ ] ⏳ Workstream B-01 dimulai (draft ToS/Privacy/DPA) — butuh konsultan legal, tidak bisa dikerjakan di dokumen teknis
- [x] ✅ T-04: rate limit per tier ditetapkan di PRD §9.5 (Basic 60/mnt, Pro 300/mnt, Enterprise kustom)
- [x] ✅ T-01: strategi restore per-tenant ditulis di PRD §6.4 (PITR ke DB klon + snapshot pra-impor)

**Fase 1 (MVP, 10 sprint)**
- [x] ✅ Stories ditambahkan (Dok 04 §8): US-105 (G-01 lupa password mandiri), US-106 (G-02 ganti password PWA + Dok 03 §2.8), US-109 (G-06 bulk user), US-111 (T-05 halaman audit log), US-107 (G-04 template notifikasi + layar wireframe W-09); rekomendasi penempatan Sprint 7–9
- [x] ✅ B-04: US-113 (product analytics) — instrumentasi sejak awal
- [x] ✅ Persiapan G-03: US-115/116/117 (studi format + adapter impor/ekspor Dapodik) di sprint akhir Fase 1

**Fase 2**
- [x] ✅ Stories & spec ditambahkan: US-110 (G-07 portabilitas mutasi antar tenant), US-108 (G-05 global search), US-112 + Dok 03 §11.4–11.6 (T-03 outbound webhook, paket Pro+), US-114 (B-05 NPS/CSAT), Dok 03 §13 (T-02 kebijakan depresiasi API)
- [ ] ⏳ Eksekusi ide: I-02 (chatbot WA), I-03 (kartu pelajar digital) — butuh spek detail saat Fase 2 dimulai

**Fase 3+**
- [ ] ⏳ I-01, I-04, I-05, I-06, I-07, B-02 (GTM penuh), B-03 (help center & materi onboarding)

---

## 8. Kesimpulan

Fondasi dokumentasi **kokoh dan siap eksekusi**: tidak ada inkonsistensi struktural yang tersisa, dan seluruh rujukan silang (FR, tabel, endpoint, permission, modul) telah terverifikasi. Pekerjaan rumah terbesar bukan di teknis, melainkan di **legal (DPA/UU PDP)** dan **go-to-market** — keduanya sebaiknya berjalan paralel dengan Sprint 1, bukan menunggu produk jadi.
