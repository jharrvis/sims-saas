# ADR-0005: Baseline Laravel 13 + PHP 8.3 + Pest 4

- **Status:** Accepted
- **Tanggal:** 2026-10-09
- **Pengusul:** Tech Lead

## Konteks

Baseline awal (Laravel 12, Pest 2) menua sebelum go-live (P1-11, terverifikasi 9 Okt 2026): Laravel 12 bug-fix berakhir 13 Agu 2026, security-fix berakhir 24 Feb 2027 — bertepatan dengan target go-live Q1 2027. Pemilik produk juga memutuskan **tidak memakai Filament** (UI custom mengikuti design system).

## Keputusan

- **Laravel 13** (rilis 17 Mar 2026; bug-fix s/d Q3 2027, security s/d Mar 2028), **PHP 8.3+**, **Pest 4**.
- UI: Blade + Livewire 3 + Tailwind CSS mengikuti design system (disediakan terpisah) — tanpa Filament.
- Upgrade 12→13 dilaporkan minim breaking changes; tetap wajib compatibility spike di Sprint 1 (US-001 mencakup verifikasi plugin: Spatie Permission Teams, Sanctum, dsb.).

## Alternatif yang dipertimbangkan

1. Tetap Laravel 12 — ditolak: EOL security bertepatan go-live; upgrade darurat pasca-launch lebih mahal.
2. Tunggu Laravel 14 (Q1 2027) — ditolak: jadwal rilis tidak pasti; 13 memberi jendela aman 12+ bulan.

## Konsekuensi

- (+) Seluruh masa pengembangan dalam jendela dukungan penuh.
- (−) Plugin harus diverifikasi kompatibel Laravel 13 di Sprint 1.

## Referensi

- Dok 15 (P1-11) · PRD §6.1
