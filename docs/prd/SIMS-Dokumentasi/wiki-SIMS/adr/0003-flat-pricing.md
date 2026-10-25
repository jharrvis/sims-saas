# ADR-0003: Harga Flat per Sekolah (bukan per Siswa)

- **Status:** Accepted
- **Tanggal:** 2026-10-09
- **Pengusul:** Product Owner

## Konteks

Model awal per-siswa-aktif membingungkan pembeli sekolah Indonesia dan "menghukum" adopsi penuh (makin banyak siswa didaftarkan, makin mahal). Riset pasar (Dok 10) menunjukkan kompetitor lokal memakai flat.

## Keputusan

Harga flat per sekolah per bulan: Basic Rp 499rb (inti + à la carte), Pro Rp 999rb (all-inclusive 16 modul), Enterprise kustom. Kuota siswa per tier tetap ada sebagai batas teknis (hybrid via kuota, bukan via harga).

## Alternatif yang dipertimbangkan

1. Per siswa aktif (Rp 2.500–4.000) — ditolak: friksi jual tinggi, insentif adopsi buruk.
2. Flat murni tanpa kuota — ditolak: tanpa batas, abuse & cost overrun.

## Konsekuensi

- (+) Kalimat jual sederhana: "lebih murah dari SPP satu siswa".
- (+) ARPU naik via upsell Pro & add-on (land-and-expand).
- (−) Sekolah sangat kecil (<100 siswa) butuh tier Rintisan Rp 299rb (diputuskan ikut).
- Dampak teknis: `plans.harga_bulanan` flat; kuota di `plans.max_*`; enforcement di `QuotaService`.

## Referensi

- PRD §9 · Dok 10 · Dok 02 §8/`PlanSeeder`
