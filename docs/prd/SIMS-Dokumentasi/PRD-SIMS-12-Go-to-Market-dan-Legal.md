# Dok 12 — Go-to-Market Plan & Checklist Legal

| Atribut | Keterangan |
|---|---|
| Versi | 1.0 |
| Tanggal | 9 Oktober 2026 |
| Menutup gap audit | B-01 (dokumen legal), B-02 (go-to-market plan), B-03 (help center & materi onboarding) |
| Rujukan | PRD §9 (model bisnis), §14 (roadmap), §17.7 (UU PDP) |

> ⚠️ **Disclaimer:** Dokumen ini adalah kerangka perencanaan bisnis dan checklist kepatuhan, **bukan nasihat hukum**. Seluruh draf dokumen legal (ToS, Kebijakan Privasi, DPA, template persetujuan) **wajib direview dan difinalisasi oleh advokat/konsultan hukum** yang kompeten di hukum Indonesia sebelum dipakai, terutama terkait UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP).

---

## Bagian A — Go-to-Market Plan

### A.1 Segmentasi Target

| Segmen | Karakteristik | Pain point utama | Pendekatan jualan |
|---|---|---|---|
| **Swasta kecil** (< 300 siswa, SD/SMP) | Anggaran TI minim; admin rangkap tugas; belum punya sistem | Administrasi manual/Excel berantakan; ortu menuntut info via WA | Paket **Basic** murah; demo 30 menit fokus absensi + info ortu; garansi "siap pakai 15 menit" |
| **Swasta menengah** (300–1.000 siswa) | Punya staf TU; mulai sadar digitalisasi; sensitif harga tapi mau bayar jika jelas manfaat | Rekap nilai & rapor lambat; tunggakan SPP sulit ditagih | Paket **Pro**; tekankan modul Keuangan/SPP + E-Rapor (penghemat waktu TU); tawarkan hitungan ROI |
| **Sekolah besar / grup yayasan** (> 1.000 siswa atau multi-sekolah) | Butuh konsolidasi; punya IT support; proses pengadaan formal | Data antar unit tidak seragam; butuh laporan yayasan | Add-on **Organisasi** + paket **Enterprise**; proposal formal + POC; libatkan pimpinan yayasan |
| **Madrasah** (MI/MTs/MA) | Struktur mirip sekolah umum + kebutuhan data Kemenag; komunitas kuat | Sama dengan swasta + pelaporan ganda | Pendekatan via komunitas/organisasi madrasah; paket Pro; soroti kemudahan laporan |
| **Sekolah negeri** (via Dinas Pendidikan) | Siklus pengadaan panjang (APBD); butuh payung regulasi | Pengadaan formal; butuh jaminan keamanan data | Jalur kemitraan dinas (kab/kota); siapkan dokumen kepatuhan (DPA, keamanan §17); horizon 6–12 bulan |
| **Kota besar vs daerah** | Kota: konektivitas baik, literasi digital tinggi. Daerah: internet putus-nyambung, HP spek rendah | Daerah: aplikasi berat tidak jalan | Tekankan PWA ringan + mode offline (§7.13) untuk segmen daerah; materi promosi sesuaikan |

**Urutan prioritas akuisisi:** swasta kecil–menengah dulu (siklus jual 2–6 minggu, keputusan di kepala sekolah), grup yayasan sebagai *lighthouse*, negeri via dinas sebagai proyek jangka menengah.

### A.2 Program Pilot (pra-launch publik)

**Kriteria 3–5 sekolah pilot:**
1. Campuran jenjang (min. 1 SD, 1 SMP, 1 SMA/sederajat) untuk menguji preset jenjang.
2. Punya **champion internal** (kepala sekolah/waka kurikulum yang antusias) — penentu keberhasilan adopsi.
3. Data siswa/guru tersedia dalam Excel (atau bersedia input manual 1–2 minggu).
4. Bersedia memberi feedback mingguan + menjadi studi kasus publik (logo & testimoni).
5. Minimal 200 siswa agar uji beban bermakna; salah satunya di daerah (uji PWA offline/jaringan lemah).

**Penawaran pilot:** gratis 3 bulan paket Pro + seluruh add-on inti; imbalannya: feedback terstruktur tiap minggu, izin studi kasus, dan 2 referensi ke sekolah lain. Harga pasca-pilot dikunci setahun (*grandfathering*) sebagai apresiasi.

**Durasi:** 2–3 bulan (paralel dengan akhir Fase 1 / awal Fase 2).

**Kriteria sukses pilot (terukur):**

| Indikator | Target |
|---|---|
| Data siswa & guru termigrasi & valid | ≥ 90% dalam 2 minggu |
| Guru aktif input absensi harian | ≥ 70% hari aktif |
| Waktu input absensi per kelas | ≤ 2 menit (sesuai KPI PRD) |
| Orang tua aktivasi PWA | ≥ 60% dari total wali |
| Skor NPS sekolah pilot | ≥ 50 |
| Bug kritikal/mayor terbuka di akhir pilot | 0 |

**Playbook onboarding 14 hari:**

| Hari | Tim SIMS | Pihak sekolah |
|---|---|---|
| 1–2 | Kickoff, buat tenant + akun admin, verifikasi data awal | Tunjuk PIC + champion; siapkan file Excel data |
| 3–5 | Bantu migrasi & validasi data (import, deduplikasi) | Verifikasi hasil migrasi bersama TU |
| 6–7 | Training admin & guru (maks. 3 jam, per tugas utama) + bagikan video panduan | Hadirkan guru wali kelas & operator |
| 8–10 | *Parallel run*: sistem dipakai berdampingan cara lama; daily check-in 15 menit | Guru input absensi/nilai riil |
| 11–12 | Go-live: cara lama dihentikan untuk modul yang sudah stabil | Pengumuman ke orang tua + aktivasi PWA (bantuan wali kelas) |
| 13–14 | Review: ukur 6 indikator sukses; catat bug & request; serah terima ke CS rutin | Tanda tangan berita acara + testimoni |

### A.3 Sales Motion

**Jalur penjualan (realistis untuk tim kecil):**
1. **Direct visit** — kunjungi kepala sekolah/yayasan dengan laptop + demo tenant dummy (15 menit demo + 15 menit diskusi). Konversi tertinggi untuk swasta.
2. **Kemitraan Dinas Pendidikan** — tawarkan paket dinas (multi-sekolah satu kabupaten); butuh dokumen kepatuhan lengkap.
3. **Reseller/agen daerah** — agen lokal (komisi 20–30% tahun pertama) menjangkau daerah yang tidak ter-cover tim.
4. **Digital** — SEO ("aplikasi manajemen sekolah", "e-rapor online"), webinar gratis untuk guru/kepala sekolah, konten studi kasus pilot, komunitas guru di media sosial.

**Materi yang dibutuhkan:** skrip demo 30 menit (alur: absensi → nilai → info ortu → billing), one-pager per segmen (manfaat + harga), kalkulator ROI sederhana (jam TU dihemat × upah), studi kasus pilot (PDF 2 halaman), template proposal untuk yayasan/dinas.

**Funnel & target konversi awal (wajar untuk SaaS baru):**

| Tahap | Definisi | Target konversi |
|---|---|---|
| Lead → Demo | Kunjungan/webinar yang berlanjut ke demo | 30% |
| Demo → Trial | Sekolah setuju coba 30 hari | 50% |
| Trial → Paid | Upgrade setelah trial (dengan pendampingan) | 25–30% |

Implikasi: untuk 60 sekolah berbayar di tahun 1, butuh ±800 lead tersentuh. Rencana aktivitas mingguan tim sales diturunkan dari angka ini.

### A.4 Finalisasi Harga

Harga di PRD §9 masih ilustratif. Proses validasi selama pilot:
1. **Wawancara willingness-to-pay** dengan 10–15 kepala sekolah/bendahara (teknik Van Westendorp sederhana: "di harga berapa terasa murah/mahal/terlalu mahal?").
2. **Uji 2–3 titik harga** pada kohor trial berbeda (mis. Pro di Rp 799 rb / 999 rb / 1.199 jt per bulan) — ukur konversi trial→paid per titik.
3. **Kunci harga sebelum launch publik** (akhir Fase 1). Sekolah pilot mendapat *grandfathering* 12 bulan.
4. Prinsip: harga flat per sekolah membuat keputusan pembelian sederhana ("lebih murah dari SPP satu siswa"); pastikan ARPU menutup biaya akuisisi dalam ≤ 12 bulan (CAC payback, selaras KPI PRD §3).

### A.5 Target 12 Bulan Pertama (ilustrasi — sesuaikan dengan hasil pilot)

| Kuartal | Sekolah baru | Total sekolah | Trial aktif (rata2) | Konversi trial→paid | Churn/bln | MRR (ilustrasi) |
|---|---|---|---|---|---|---|
| Q1 (pilot) | 5 | 5 | 5 | n/a (gratis) | — | Rp 0 |
| Q2 | 10 | 15 | 12 | 25% | 3% | ±Rp 11 jt |
| Q3 | 20 | 33 | 25 | 28% | 2,5% | ±Rp 24 jt |
| Q4 | 27 | 57 | 30 | 30% | 2% | ±Rp 41 jt |

Asumsi: campuran paket (70% Basic Rp 499 rb, 25% Pro Rp 999 rb, 5% Enterprise Rp 2,5 jt) → ARPU ±Rp 724 rb; harga flat per sekolah (keputusan produk 9 Okt 2026). Angka ini menjadi template OKR tim growth; direvisi tiap kuartal berdasarkan data aktual (rujuk B-04 product analytics di Dok 08).

---

## Bagian B — Checklist Legal (UU PDP No. 27/2022 + PP 17/2025)

> **PP No. 17 Tahun 2025** tentang Tata Kelola Penyelenggaraan Sistem Elektronik dalam Pelindungan Anak **masuk baseline kepatuhan** (P0-07): age assurance proporsional, privacy-high defaults, parental oversight, child-friendly notice, dan kanal laporan/pengaduan anak di produk. DPIA + RoPA + register subprosesor wajib sebelum pilot (US-128).

### B.1 Dokumen Wajib Sebelum Go-live

| # | Dokumen | Isi pokok | Penanggung jawab | Deadline |
|---|---|---|---|---|
| 1 | **Syarat & Ketentuan Layanan** (ToS) | Definisi layanan & paket; hak-kewajiban; pembayaran & dunning (§9.4); suspensi/terminasi; batasan tanggung jawab; penyelesaian sengketa; perubahan ketentuan | Advokat + Product | Sprint 4 (final) |
| 2 | **Kebijakan Privasi** | Data yang dikumpulkan per peran; tujuan & dasar hukum; penyimpanan & keamanan (§17); hak subjek data + cara mengajukan; retensi & penghapusan (§17.6); kontak DPO; histori versi | Advokat + Product | Sprint 4 (final) |
| 3 | **DPA — Perjanjian Pemrosesan Data** | Kerangka di B.2; ditandatangani per sekolah pelanggan (sekolah = pengendali data, SIMS = prosesor) | Advokat | Sprint 4; wajib untuk pilot |
| 4 | **Template persetujuan wali murid** | Kerangka di B.3; untuk data anak (kategori data spesifik UU PDP) | Advokat + Product | Sprint 3 |
| 5 | **Kebijakan Cookie** | Cookie yang dipakai (sesi, analitik, preferensi); cara kelola; dapat digabung ke Kebijakan Privasi bila ringkas | Product | Sprint 4 |
| 6 | **SLA per paket** | Uptime (99/99,5/99,9%), waktu respons support, kredit layanan bila SLA dilanggar; lampiran ToS | Product + Ops | Sprint 4 |
| 7 | **NDA template** (pendukung) | Untuk pilot & reseller sebelum akses data/bisnis | Advokat | Sprint 2 |

Ketentuan: setiap dokumen berversi dan tersimpan (riwayat perubahan dapat diaudit pelanggan, selaras §17.7). Versi Bahasa Indonesia adalah acuan; terjemahan Inggris opsional untuk kebutuhan enterprise.

### B.2 Kerangka DPA (outline — finalisasi oleh advokat)

1. **Para pihak & peran**: Sekolah ("Pengendali Data") dan penyelenggara SIMS ("Prosesor Data") sesuai definisi UU PDP.
2. **Ruang lingkup**: kategori data pribadi yang diproses (biodata siswa/wali/guru, nilai, kehadiran, dokumen); tujuan pemrosesan terbatas pada operasional layanan SIMS; larangan pemrosesan di luar instruksi tertulis pengendali.
3. **Kewajiban prosesor**: menerapkan keamanan §17 PRD (enkripsi, kontrol akses, audit); memastikan SDM terikat kerahasiaan; membantu pengendali memenuhi hak subjek data (akses/koreksi/hapus ≤ 30 hari, selaras §17.7).
4. **Sub-prosesor**: daftar pihak ketiga yang memproses data (payment gateway, WA gateway, cloud infra); perubahan sub-prosesor diberitahukan H-30 dengan hak keberatan pengendali.
5. **Pemberitahuan insiden**: ≤ 72 jam sejak diketahui, mencakup sifat, kategori & perkiraan jumlah data terdampak, langkah mitigasi (selaras §17.5).
6. **Hak audit**: pengendali berhak mengaudit (atau via auditor independen) kepatuhan prosesor 1×/tahun dengan pemberitahuan wajar.
7. **Penghapusan/pengembalian data**: saat kontrak berakhir mengikuti §17.6 (ekspor 14 hari → penghapusan permanen + sertifikat penghapusan); pengecualian retensi legal dicantumkan eksplisit.
8. **Masa berlaku & terminasi**: mengikuti masa langganan; ketentuan survival untuk kewajiban keamanan & kerahasiaan.
9. **Tanggung jawab & ganti rugi**: *[placeholder — dirumuskan advokat sesuai profil risiko]*.

### B.3 Template Persetujuan Wali Murid (kerangka poin)

Formulir persetujuan (diambil saat aktivasi akun PWA — checkbox eksplisit + verifikasi OTP sebagai bukti persetujuan) memuat:
1. Identitas anak dan hubungan wali yang memberi persetujuan.
2. **Daftar data yang dikumpulkan**: biodata, foto, kehadiran, nilai & rapor, catatan BK (bila modul aktif), data perangkat untuk notifikasi.
3. **Tujuan tiap kategori data** (mis. nilai → rapor & informasi orang tua; foto → identitas & kartu pelajar).
4. Masa penyimpanan & kebijakan penghapusan (rujuk §17.6).
5. **Hak wali**: meminta akses, koreksi, penghapusan, dan **menarik persetujuan** kapan pun — serta konsekuensi penarikan (mis. tidak bisa menerima info via PWA).
6. Cara mengajukan hak: via menu Profil PWA atau kontak DPO (nama + email + WA dicantumkan).
7. Pernyataan persetujuan + nama jelas + tanggal (tersimpan sebagai log persetujuan berversi).

### B.4 Timeline Legal

| Sprint | Kegiatan | Output |
|---|---|---|
| Sprint 1–2 | Draft internal ToS, Kebijakan Privasi, kerangka DPA & persetujuan wali (berbasis §17 & Dok 12 ini) | Draf v0.1 |
| Sprint 3 | **Review advokat/konsultan hukum**; revisi; finalisasi SLA & kebijakan cookie | Draf v1.0 legal-approved |
| Sprint 4 | Dokumen dipublikasikan di aplikasi (halaman legal berversi); **DPA ditandatangani dengan sekolah pilot**; template persetujuan wali terintegrasi ke alur aktivasi PWA | Go-live legal clear |
| Berkelanjutan | Review tahunan atau setiap ada perubahan pemrosesan data; notifikasi perubahan ke pelanggan H-30 | Kepatuhan terjaga |

> ⛔ **Blocker eksplisit**: go-live publik (di luar pilot tertutup) **tidak boleh** dilakukan sebelum B.1 #1–#4 final dan DPA siap ditandatangani. Pilot berjalan dengan NDA + DPA versi pilot.

---

## Bagian C — Rencana Konten Bantuan (Help Center)

### C.1 Format & Kanal

| Format | Kegunaan | Kanal |
|---|---|---|
| Artikel panduan langkah-demi-langkah (dengan screenshot) | Tugas utama per peran; dapat dicari | Pusat bantuan dalam aplikasi (per tenant, Bahasa Indonesia) |
| Video ≤ 3 menit | Tugas yang sulit dijelaskan dengan teks (input nilai, susun jadwal) | Kanal video + embed di artikel |
| FAQ | Pertanyaan berulang (OTP tidak masuk, lupa password, kuota) | Pusat bantuan + chatbot sederhana |
| **Onboarding checklist dalam aplikasi** | Panduan interaktif: "selesaikan 5 langkah ini" (aktivasi → import siswa → buat tahun ajaran → input absensi pertama → undang ortu) | Widget di dashboard Admin Sekolah; progres tersimpan |
| Grup support per sekolah (masa pilot) | Respons cepat + sumber bahan FAQ | WA grup → arsip ke KB |

Prinsip: setiap tugas utama di PRD §7 harus punya **maksimal 3 klik dari artikel ke praktik** (artikel tertaut langsung ke halaman aplikasinya).

### C.2 Daftar Prioritas Konten (20 item)

| # | Judul | Peran | Format | Prioritas |
|---|---|---|---|---|
| 1 | Registrasi sekolah & aktivasi trial 30 hari | Admin Sekolah | Artikel + video | P0 |
| 2 | Import data siswa & guru dari Excel (termasuk perbaiki error) | TU/Admin | Artikel + video | P0 |
| 3 | Menyusun tahun ajaran, semester & kalender akademik | Admin | Artikel | P0 |
| 4 | Membuat rombel & menetapkan wali kelas | Admin | Artikel | P0 |
| 5 | Input absensi harian + mode cepat (≤ 2 menit) | Wali Kelas | Video | P0 |
| 6 | Input nilai, bobot komponen & kunci periode | Guru Mapel | Video | P0 |
| 7 | Merilis nilai & mencetak leger/rapor | Wali Kelas | Artikel | P0 |
| 8 | Membuat & mengelola jadwal (termasuk atasi bentrok) | Admin/Kurikulum | Video | P0 |
| 9 | Aktivasi PWA orang tua via OTP | Orang Tua | Video (≤2 mnt) | P0 |
| 10 | Memantau kehadiran & nilai anak di PWA | Orang Tua | Artikel | P0 |
| 11 | Mengelola pengguna & reset password | Admin | Artikel | P1 |
| 12 | Membuat laporan & export berkop sekolah | TU/Kepsek | Artikel | P1 |
| 13 | Membaca dashboard & peringatan dini | Kepsek | Artikel | P1 |
| 14 | Mengelola langganan: lihat invoice, bayar, upgrade paket | Admin | Artikel + video | P1 |
| 15 | Mengajukan & mengelola modul add-on | Admin | Artikel | P1 |
| 16 | Mengelola template notifikasi WA | Admin | Artikel | P1 |
| 17 | Tutup tahun ajaran & kenaikan kelas massal | Admin | Artikel | P1 |
| 18 | Ekspor & backup data sekolah mandiri | Admin | Artikel | P1 |
| 19 | Troubleshooting: OTP tidak masuk, lupa password, sesi kadaluarsa | Semua | FAQ | P0 |
| 20 | Kebijakan privasi & hak data Anda (versi awam) | Ortu/Siswa | Artikel | P1 |

### C.3 Jadwal Produksi

| Kapan | Kegiatan |
|---|---|
| Sprint 6 | Mulai produksi: item P0 untuk peran staf (1–8); tetapkan penanggung jawab konten (CS/content writer) |
| Sprint 7 | Item P0 untuk orang tua/siswa (9–10, 19); onboarding checklist dalam aplikasi selesai dibangun |
| Sprint 8 (UAT pilot) | Seluruh P0 + FAQ terbit; P1 menyusul sebelum launch publik; ukur: % tiket support yang terjawab artikel (target defleksi ≥ 40%) |
| Berkelanjutan | Setiap rilis modul baru wajib disertai artikel + video sebelum rilis (DoD konten) |

---

*Dokumen pendamping: PRD §9 (model bisnis), §14 (roadmap), §17 (keamanan & UU PDP), Dok 08 §5–§7 (gap & ide), Dok 10 (harga pasar — untuk validasi A.4).*
