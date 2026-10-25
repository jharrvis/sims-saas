# ADR-0001: Monolit Modular Laravel (bukan Microservices)

- **Status:** Accepted
- **Tanggal:** 2026-10-09
- **Pengusul:** Tech Lead

## Konteks

Tim awal 5 orang perlu deliver MVP 10 sprint. Microservices menambah overhead DevOps, kontrak API antar-service, dan debugging terdistribusi yang tidak sebanding dengan skala awal (±50 sekolah).

## Keputusan

Satu aplikasi Laravel 12 **monolit modular**: logika dipisah per domain (`app/Domains/...`), modul add-on sebagai unit terpisah dengan kontrak `ModuleRegistrar` (lihat Dok 02 §9). Service layer tipis di controller.

## Alternatif yang dipertimbangkan

1. Microservices per domain — ditolak: overhead operasional > manfaat di skala ini.
2. Modular monolith via package terpisah — ditolak untuk MVP: menambah kompleksitas versioning; dipertimbangkan ulang bila tim > 20 orang.

## Konsekuensi

- (+) Deploy 1x, debug mudah, onboarding cepat.
- (−) Harus disiplin menjaga batas modul (dijaga `deptrac` di CI).
- Tindak lanjut: aturan dependensi antar modul di `deptrac.yaml` (SOP §10).

## Referensi

- PRD §6.1, §6.5 · Dok 02 §9
