# ADR-0002: Shared-Schema Multi-Tenant (`school_id`)

- **Status:** Accepted
- **Tanggal:** 2026-10-09
- **Pengusul:** Tech Lead

## Konteks

SaaS dengan banyak sekolah pelanggan. Butuh isolasi data kuat dengan biaya infra terendah dan onboarding termurah (1 baris data, bukan 1 server).

## Keputusan

Satu database, satu skema, kolom `school_id` di semua tabel operasional + `BelongsToSchool` global scope + policy + validasi request (3 lapis isolasi). Jalur evolusi ke separate-DB per tenant disiapkan untuk tier Enterprise tanpa mengubah domain logic.

## Alternatif yang dipertimbangkan

1. Separate database per tenant sejak awal — ditolak: biaya & kompleksitas migrasi/backup N DB; onboarding mahal.
2. Separate schema per tenant (PostgreSQL) — ditolak: tooling migrasi lebih rumit, keuntungan isolasi tidak signifikan vs shared-schema + scope.

## Konsekuensi

- (+) Laporan lintas-tenant mudah, biaya infra minimal.
- (−) Restore per-tenant sulit → mitigasi: strategi restore di [[runbooks/restore-per-tenant|runbook restore]] (PRD §17.5).
- (−) Wajib test negatif isolasi tenant untuk setiap fitur (DoD).

## Referensi

- PRD §6.2 · Dok 02 §4
