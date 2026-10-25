# Dok 11 — Katalog Integrasi

| Atribut | Keterangan |
|---|---|
| Versi | 1.0 |
| Tanggal | 9 Oktober 2026 |
| Hubungan dengan PRD | Perluasan dari PRD §12 (Integrasi & API). PRD §12 menyatakan arah; dokumen ini merinci tiap integrasi: mekanisme realistis, prioritas, effort, dan risiko. |
| Prinsip kejujuran | Jika suatu platform **tidak menyediakan API resmi**, dokumen ini menyatakannya eksplisit beserta alternatif yang realistis. |

---

## 1. Dapodik (Kemendikdasmen) — Data Pokok Pendidikan

**Tujuan:** Dapodik adalah sumber kebenaran (*source of truth*) data sekolah, PTK, peserta didik, dan rombel di Indonesia. Integrasi menghemat input ganda dan menjaga konsistensi NPSN/NISN/NUPTK.

**Data yang mengalir (dua arah):**
- **Masuk (pull):** profil sekolah, PTK/guru, peserta didik, rombel, mata pelajaran.
- **Keluar (push):** nilai rapor (mata evaluasi + nilai akhir) ke Dapodik lokal.

**Mekanisme teknis realistis:**
- Dapodik menyediakan **Web Service** yang disajikan oleh **aplikasi Dapodik desktop di komputer sekolah** (umumnya `http://localhost:5774` atau IP LAN sekolah, mis. `http://192.168.1.100:5774`). Autentikasi memakai **NPSN + token/key** yang di-generate dari aplikasi Dapodik; IP klien perlu terdaftar.
- Pola yang dipakai aplikasi e-Rapor resmi Kemendikbud: *Tes Koneksi → Tarik Data → Kirim Mata Evaluasi (Matev) → Kirim Nilai*.
- Alternatif tanpa koneksi langsung: file **prefill** / unduhan Excel dari portal Dapodik.
- **Catatan arsitektur penting untuk SaaS cloud:** web service Dapodik berjalan di jaringan lokal sekolah, bukan di internet. SIMS (cloud) **tidak bisa menjangkaunya langsung**. Opsi realistis: (a) **agen bridge ringan** di PC operator sekolah yang meneruskan request (pola paling umum dipakai aplikasi pihak ketiga); (b) panduan + tombol "Tes Koneksi" agar operator paham; (c) fallback impor file Excel. SDK komunitas (Python/TypeScript) tersedia sebagai referensi protokol, bukan dependensi resmi.

**Prioritas:** **Must** — **Fase 2** (fondasi: tabel mapping ID + halaman koneksi di Fase 1 akhir).

**Estimasi effort:** L (kompleksitas ada di bridge lokal + mapping referensi, bukan di API-nya).

**Risiko/catatan:**
- Protokol web service tidak didokumentasikan secara publik dan dapat berubah antar versi Dapodik → bungkus dalam adapter + feature flag per versi.
- Kunci/token milik sekolah (per tenant), disimpan terenkripsi; SIMS tidak pernah menyimpan kredensial Dapodik di log.
- Data yang di-push harus memiliki referensi Dapodik yang valid (data yang dibuat manual tanpa referensi akan ditolak).

---

## 2. EMIS (Kementerian Agama) — untuk Madrasah

**Tujuan:** Madrasah (MI/MTs/MA) wajib lapor via EMIS 4.0 (lembaga) dan EMIS GTK (guru). Tanpa dukungan EMIS, SIMS tidak bisa dipakai madrasah secara penuh.

**Data yang mengalir (satu arah, keluar dari SIMS sebagai bahan):**
- Data lembaga, peserta didik, GTK dalam format yang siap diinput ke portal EMIS.

**Mekanisme teknis realistis:**
- **Tidak ada API/web service publik EMIS yang dapat dipakai aplikasi pihak ketiga** (hasil riset: EMIS 4.0 hanya berupa portal web — `emisgtk.kemenag.go.id` untuk GTK — tanpa endpoint integrasi resmi).
- Mekanisme realistis: **ekspor Excel/CSV dari SIMS dengan kolom yang dipetakan ke format input EMIS** + panduan langkah input. Proses *bridging* antar sistem (mis. ke Dapodik via Verval) tetap manual oleh operator dan admin kabupaten — bukan sesuatu yang bisa diotomatisasi SIMS.
- Desain SIMS: sediakan template ekspor "Siap EMIS" per jenis data (siswa, GTK) + validasi kelengkapan sebelum ekspor (NIK, dsb. — EMIS 4.0 terintegrasi validasi Dukcapil sehingga data harus akurat).

**Prioritas:** **Should** — **Fase 2** (bersamaan dengan modul madrasah/preset jenjang MA).

**Estimasi effort:** S (ekspor terpola + dokumentasi).

**Risiko/catatan:** Jangan menjanjikan "sinkronisasi otomatis EMIS" — itu tidak dimungkinkan secara teknis dan menyesatkan pengguna.

---

## 3. ARKAS (Aplikasi RKAS) — Dana BOS

**Tujuan:** ARKAS adalah aplikasi tunggal perencanaan & pelaporan dana BOS. Sekolah ingin angka anggaran (RKAS) selaras dengan realisasi belanja yang dicatat di modul Keuangan SIMS.

**Data yang mengalir:**
- **Keluar (satu arah, manual):** rekap realisasi belanja per kode rekening/kegiatan dari SIMS → menjadi bahan input ke ARKAS.

**Mekanisme teknis realistis:**
- ARKAS adalah **aplikasi desktop Windows** dengan database lokal **terenkripsi (SQLCipher)**. **Tidak ada API resmi dan tidak ada jalur integrasi resmi.**
- Aplikasi pendamping pihak ketiga yang ada di pasaran bekerja dengan membaca file database lokal ARKAS di komputer yang sama — pendekatan ini **tidak resmi**, rapuh terhadap update ARKAS, dan berisiko dari sisi keamanan/dukungan. **SIMS tidak akan menempuh jalur ini.**
- Mekanisme realistis dan jujur: modul Keuangan SIMS menyediakan **laporan "Rekap Realisasi per Kode Rekening ARKAS"** (Excel/PDF) dengan struktur kode yang selaras, sehingga bendahara tinggal menyalin/mengimpor ke ARKAS desktop. Jika suatu saat Kemendikbud membuka API, adapter sudah disiapkan polanya.

**Prioritas:** **Could** — **Fase 3** (sebagai bagian dari pematangan modul Keuangan; bukan integrasi API).

**Estimasi effort:** S (laporan terpola; bukan integrasi sistem).

**Risiko/catatan:** Ekspektasi pengguna harus dikelola sejak awal: brosur/marketing dilarang mengklaim "terintegrasi ARKAS otomatis".

---

## 4. ANBK (Asesmen Nasional Berbasis Komputer)

**Tujuan:** Setiap tahun, sekolah menyiapkan biodata peserta ANBK (sampel siswa kelas 5/8/11 + guru/kepala sekolah untuk survei). Data ini ditarik dari BIO-AN ke portal ANBK.

**Data yang mengalir (keluar dari SIMS sebagai bahan):**
- Biodata peserta: nama (kapital), NISN, nomor peserta (format `AA-BB-CCCC`), kelas, jenis kelamin (L/P), sesi, ruang, serta paket foto ZIP (nama file = username peserta).

**Mekanisme teknis realistis:**
- **Tidak ada API publik.** Alur resmi: login ke `anbk.kemdikbud.go.id` → menu Data Peserta → "Ambil dari BIO AN"; untuk data manual tersedia **upload template Excel** biodata.
- Peran SIMS: **generator file Excel biodata ANBK sesuai template resmi** (aturan penulisan: nama kapital, format nomor peserta, sesi 1/2/3) + validasi kelengkapan NISN sebelum unduh. Ini menghemat pekerjaan proktor secara signifikan dibanding mengetik manual.
- Peserta ANBK adalah **sampel acak** yang ditetapkan pusat (bukan seluruh siswa) → SIMS menyediakan penanda "peserta ANBK" per siswa per tahun.

**Prioritas:** **Should** — **Fase 2** (fitur kecil, nilai operasional besar saat musim ANBK).

**Estimasi effort:** S.

**Risiko/catatan:** Template dapat berubah tiap tahun → simpan sebagai template ter-versioning per tahun ajaran, bukan hardcode.

---

## 5. E-Rapor Kemendikbud (format & pengiriman nilai)

**Tujuan:** Nilai rapor resmi harus tercatat di Dapodik (dan tersinkron ke PDSS untuk SNBP). Modul E-Rapor SIMS harus menghasilkan output yang kompatibel dengan alur resmi.

**Data yang mengalir (dua arah, mengikuti pola aplikasi e-Rapor resmi):**
- **Masuk:** master Dapodik (siswa, guru, rombel, mapel) sebagai referensi.
- **Keluar:** mata evaluasi + nilai rapor per semester ke Dapodik lokal.

**Mekanisme teknis realistis:**
- Mengikuti pola aplikasi e-Rapor resmi Kemendikbud (SD/SMP/SMA/SMK): koneksi ke web service Dapodik lokal (`:5774`, NPSN + token) dengan tahapan **Tes Koneksi → Kirim Mata Evaluasi → Kirim Nilai**.
- Aturan penting yang harus ditiru: **hanya data yang memiliki referensi Dapodik valid yang bisa dikirim**; data yang dibuat manual tanpa referensi akan ditolak. Nilai yang dikirim harus sudah final/diverifikasi.
- Template cetak rapor mengikuti struktur resmi (komponen pengetahuan/keterampilan/sikap, deskripsi, kehadiran, ekstrakurikuler, catatan wali kelas) per jenjang & kurikulum (Merdeka/K13).
- Efek samping positif: nilai yang masuk Dapodik otomatis menjadi basis PDSS → mendukung SNBP siswa.

**Prioritas:** **Must** (untuk modul E-Rapor) — **Fase 2**.

**Estimasi effort:** M (bersama dengan Dapodik; memakai adapter web service yang sama).

**Risiko/catatan:** Perubahan versi aplikasi e-Rapor/Dapodik tiap tahun → modul E-Rapor SIMS butuh pengujian regresi tahunan sebelum musim rapor.

---

## 6. PPDB/SPMB Daerah (portal provinsi)

**Tujuan:** PPDB sekolah negeri dikelola portal dinas provinsi (mis. `spmb.jakarta.go.id`), berbasis NIK, dengan jalur zonasi/afirmasi/prestasi. Sekolah swasta umumnya memakai sistem sendiri.

**Data yang mengalir (masuk ke SIMS):**
- Daftar calon peserta didik yang **diterima & lapor diri** di sekolah tersebut → menjadi data siswa baru SIMS.

**Mekanisme teknis realistis:**
- **Tidak ada API publik** dari portal-portal daerah; tiap provinsi berbeda sistem dan aturannya.
- Mekanisme realistis: **impor Excel dari unduhan portal dinas** (SIMS menyediakan template mapping per provinsi yang umum) + alur daftar ulang di SIMS. Untuk sekolah swasta, modul PPDB Online SIMS menangani seluruh alur mandiri (formulir → seleksi → pengumuman → daftar ulang → auto-create siswa + akun ortu).
- Desain: impor PPDB daerah diperlakukan sebagai *sumber* pada pipeline penerimaan yang sama dengan PPDB mandiri (status: diterima → lapor diri → aktif).

**Prioritas:** **Should** — **Fase 2** (bersamaan modul PPDB Online).

**Estimasi effort:** S–M (tergantung jumlah template provinsi yang didukung; mulai dari 3 provinsi terbesar).

**Risiko/catatan:** Aturan PPDB daerah berubah tiap tahun (jalur, kuota, jadwal) → template impor dibuat konfigurabel per tahun, bukan hardcode.

---

## 7. Payment Gateway — Midtrans / Xendit

**Tujuan:** Dua kebutuhan pembayaran: (1) **langganan SaaS** — sekolah membayar paket SIMS (recurring otomatis); (2) **modul Keuangan sekolah** — orang tua membayar SPP/tagihan via VA/QRIS/e-wallet.

**Data yang mengalir (dua arah via webhook):**
- Keluar: pembuatan invoice/tagihan + link bayar ke gateway.
- Masuk: notifikasi status pembayaran (webhook terverifikasi signature) → update status invoice/tagihan → picu dunning/aktivasi.

**Mekanisme teknis realistis:**
- Keduanya menyediakan **REST API + SDK PHP + webhook** yang andal. **Xendit** memiliki keunggulan untuk model SaaS: *recurring/subscription billing built-in* (penagihan otomatis berkala) — selaras dengan kebutuhan langganan SIMS. **Midtrans** unggul di dokumentasi Bahasa Indonesia dan adopsi developer lokal.
- Metode yang dipakai: Virtual Account bank (fee ±Rp 4.000/transaksi), QRIS (±0,7%), e-wallet (1,5–2%), kartu kredit untuk langganan (2,9% + Rp 2.500). *Recurring* umumnya dikenai fee tambahan (±0,5%).
- Pola implementasi: satu **adapter `PaymentGateway`** (sesuai PRD §12) dengan dua profil: `saas_billing` (recurring langganan sekolah) dan `school_billing` (tagihan SPP per siswa, kredensial gateway milik masing-masing sekolah/tenant).
- Keamanan: webhook **wajib** verifikasi signature; status pembayaran hanya dipercaya dari server-to-server/webhook, bukan dari callback frontend; job rekonsiliasi berkala untuk status yang menggantung.

**Prioritas:** **Must** — **Fase 2** (bersamaan modul billing SaaS & Keuangan sekolah).

**Estimasi effort:** M (satu adapter, dua profil, webhook + dunning + rekonsiliasi).

**Risiko/catatan:** Dependensi pada satu provider → adapter pattern + provider cadangan terdaftar (sesuai PRD §16). Biaya MDR dihitung dalam unit economics (Dok 06).

---

## 8. Google Workspace for Education / Microsoft 365 — SSO

**Tujuan:** Banyak sekolah memakai Google Workspace for Education (atau Microsoft 365). SSO menghilangkan satu password lagi untuk guru/staf dan mempercepat adopsi.

**Data yang mengalir:**
- **Masuk (autentikasi):** identitas terverifikasi (email, nama) saat login.
- **Opsional (sinkronisasi):** daftar pengguna via Directory API (fase lanjutan).

**Mekanisme teknis realistis:**
- Keduanya mendukung standar terbuka: **OAuth 2.0 / OpenID Connect** (jalur paling pragmatis: tombol "Login dengan Google / Microsoft") dan **SAML 2.0**.
- Implementasi di Laravel: Socialite (Google/Microsoft provider) untuk OAuth; SAML via package (mis. `aacotroneo/laravel-saml2`) bila sekolah mewajibkan SAML.
- Aturan penting: **domain email dibatasi per tenant** (hanya `@sekolah.sch.id` yang terdaftar boleh SSO ke tenant itu) untuk mencegah login lintas sekolah; akun SSO di-*link* ke user SIMS yang sudah ada (berdasarkan email), bukan membuat duplikat; tetap hormati RBAC & 2FA internal untuk peran sensitif.
- Catatan: SSO terutama untuk **staf/guru** (panel web). Siswa/orang tua tetap memakai OTP WA di PWA (lebih universal — tidak semua punya email sekolah).

**Prioritas:** **Should** — **Fase 3** (fase lanjutan sesuai PRD; bukan penghambat adopsi awal).

**Estimasi effort:** S–M (OAuth Socialite = S; SAML per-tenant = M).

**Risiko/catatan:** Konfigurasi di sisi admin Google/Microsoft sekolah (whitelist redirect URI) → butuh panduan bergambar; sediakan fallback login password agar tidak terkunci.

---

## 9. Prinsip Arsitektur Integrasi

Berlaku untuk semua integrasi di atas:

1. **Adapter pattern** — setiap platform dibungkus adapter dengan interface seragam (`connect()`, `pull()`, `push()`, `testConnection()`). Ganti provider/platform tanpa mengubah kode bisnis. Kredensial tiap adapter **milik per tenant** dan disimpan terenkripsi.
2. **Sinkronisasi via antrean** — pull/push data besar (ribuan siswa, pengiriman nilai) selalu lewat job queue (Horizon), bukan request HTTP langsung. Ada halaman status sinkronisasi per tenant: terakhir sukses, jumlah record, error terakhir.
3. **Tabel mapping ID** — setiap integrasi punya tabel mapping (`dapodik_id_mapping`, dsb.): `school_id + local_id ↔ remote_id + remote_rev`. Tidak pernah memakai ID remote sebagai primary key lokal.
4. **Log & rekonsiliasi** — semua aktivitas integrasi tercatat (`integration_logs`: arah, jumlah, sukses/gagal, durasi); job rekonsiliasi berkala mendeteksi selisih (mis. status pembayaran menggantung, data Dapodik berubah sepihak).
5. **Idempotensi & retry** — push bersifat idempoten (kirim ulang aman); retry dengan backoff untuk kegagalan jaringan; *circuit breaker* bila platform tujuan down.
6. **Feature flag per integrasi** — tiap konektor bisa diaktifkan/nonaktifkan per tenant dan per versi platform tujuan (penting saat Dapodik/ARKAS update versi).
7. **Mode degradasi jujur** — bila API tidak tersedia, UI menawarkan jalur file (ekspor/impor) dengan jelas, bukan error misterius.

---

## 10. Tabel Ringkasan Prioritas

| # | Integrasi | Arah data | Mekanisme realistis | Prioritas | Fase | Effort |
|---|---|---|---|---|---|---|
| 1 | Dapodik | Dua arah | Web service lokal `:5774` (NPSN+token) via agen bridge + fallback Excel | **Must** | 2 | L |
| 2 | E-Rapor Kemendikbud (pola) | Dua arah | Protokol yang sama dengan Dapodik (Tes Koneksi → Kirim Matev → Kirim Nilai) | **Must** | 2 | M |
| 3 | Payment gateway (Midtrans/Xendit) | Dua arah (webhook) | REST API + SDK PHP + webhook terverifikasi; Xendit untuk recurring | **Must** | 2 | M |
| 4 | EMIS (Kemenag) | Keluar (bahan) | **Tanpa API** — ekspor Excel siap input portal | Should | 2 | S |
| 5 | ANBK | Keluar (bahan) | **Tanpa API** — generator Excel template biodata resmi | Should | 2 | S |
| 6 | PPDB/SPMB daerah | Masuk | **Tanpa API** — impor Excel unduhan portal dinas (template per provinsi) | Should | 2 | S–M |
| 7 | Google/Microsoft SSO | Masuk (auth) | OAuth 2.0 / OpenID Connect (SAML opsional) | Should | 3 | S–M |
| 8 | ARKAS | Keluar (bahan) | **Tanpa API** — laporan rekap per kode rekening untuk input manual | Could | 3 | S |

---

## 11. Rekomendasi Urutan Pengerjaan

1. **Fondasi dulu (akhir Fase 1):** kerangka adapter + `integration_logs` + tabel mapping ID + halaman status sinkronisasi per tenant. Alasan: semua integrasi Fase 2 menumpang di atasnya; membangunnya belakangan berarti retrofit.
2. **Fase 2 — gelombang 1 (nilai uang):** payment gateway. Alasan: ini yang menghidupkan *revenue* (langganan SaaS + SPP), dan pola webhook-nya dipakai ulang untuk integrasi lain.
3. **Fase 2 — gelombang 2 (nilai operasional):** Dapodik (pull master + agen bridge) → E-Rapor (push nilai, memakai adapter yang sama) → generator Excel ANBK/EMIS/PPDB daerah. Alasan: Dapodik adalah *source of truth*; E-Rapor menumpang protokolnya; tiga generator Excel murah tapi berdampak besar saat musimnya tiba (ANBK, PPDB, pelaporan).
4. **Fase 3:** SSO Google/Microsoft, laporan ARKAS, dan penjajakan ulang — cek tiap tahun apakah Kemendikbud/Kemenag membuka API resmi; jika ya, naikkan prioritasnya.

**Catatan ekspektasi:** dari 8 integrasi, hanya 4 yang memiliki API resmi (Dapodik web service, payment gateway, Google/Microsoft). Sisanya realistis via file. Dokumen ini menjadi acuan tim marketing/CS agar tidak menjanjikan "sinkronisasi otomatis" untuk sesuatu yang secara teknis tidak dimungkinkan.

---

### Sumber riset (diakses 9 Oktober 2026)
- Web Service Dapodik: SDK komunitas & panduan e-Rapor SD/SMP resmi (pola `:5774`, NPSN+token, alur Tes Koneksi → Kirim Matev → Kirim Nilai) — github.com/ardianryan/dapodik-sdk, sdanz.raportku.com, icmeraport.online
- EMIS 4.0: portal resmi `emisgtk.kemenag.go.id`; tidak ditemukan API publik — himpasikom.id, bungkuselatan.id
- ARKAS: aplikasi desktop Windows, DB lokal terenkripsi; integrasi resmi tidak tersedia — dikbud.pasuruankota.go.id, websiteedukasi.com, github.com/kevindoni/SMARTSPJ_APP_NEW
- ANBK: portal `anbk.kemdikbud.go.id`, tombol "Ambil dari BIO AN", template Excel biodata — minorrahman.sch.id, sinau-thewe.com
- PPDB daerah: portal provinsi (spmb.jakarta.go.id), pendaftaran via NIK, tanpa API publik — edu.jakarta.go.id, kids.grid.id
- Payment gateway: perbandingan Midtrans vs Xendit 2026 (recurring built-in Xendit, fee VA/QRIS/e-wallet) — albatech.id, buildwithangga.com
- SSO: Google Workspace for Education mendukung SSO SAML/OAuth 2.0 — knowledge.workspace.google.com, developers.google.com
