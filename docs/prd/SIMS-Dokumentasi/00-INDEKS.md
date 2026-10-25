# SIMS — Paket Dokumentasi Lengkap

Sistem Informasi Manajemen Sekolah (SIMS) — SaaS multi-tenant untuk sekolah-sekolah di Indonesia.
Tech stack: Laravel 13 · PHP 8.3 · UI custom (Blade + Livewire 3 + Tailwind, mengikuti design system) · PostgreSQL · Redis · PWA

## Urutan baca yang disarankan

| # | File | Isi |
|---|---|---|
| 1 | `PRD-SIMS-Multi-Sekolah.md` | **Dokumen induk**: visi, fitur (13 modul inti), arsitektur SaaS multi-tenant, katalog 16 modul add-on, model bisnis (Basic 499rb / Pro 999rb all-inclusive / Enterprise), keamanan & kepatuhan (UU PDP) |
| 2 | `PRD-SIMS-02-Skema-Database.md` | 44 migrasi Laravel 13 + konvensi modul + seeder |
| 3 | `PRD-SIMS-03-Spesifikasi-API.md` | 33+ endpoint API v1 (PWA, billing, platform) + kebijakan depresiasi |
| 4 | `PRD-SIMS-04-Backlog-Sprint.md` | 106 user stories unik, sprint plan 12 sprint (re-baseline pasca-audit) |
| 5 | `PRD-SIMS-05-Matriks-RBAC.md` | 106 permission × 13 peran (2 guard: platform & tenant) |
| 6 | `PRD-SIMS-06-Estimasi-Biaya-Infrastruktur.md` | Estimasi biaya infra IDR per tier + unit economics |
| 7 | `PRD-SIMS-07-Wireframe.html` | Wireframe grayscale 17 layar — **buka di browser** |
| 8 | `PRD-SIMS-08-Audit-dan-Rekomendasi.md` | Hasil audit dokumentasi, gap & ide |
| 9 | `PRD-SIMS-09-Strategi-Autentikasi.md` | Strategi login: WA OTP, passkey, QR siswa, SSO |
| 10 | `PRD-SIMS-10-Paket-dan-Harga.md` | Riset harga pasar Indonesia + matriks fitur per tier |
| 11 | `PRD-SIMS-11-Katalog-Integrasi.md` | Dapodik, EMIS, ARKAS, ANBK, PPDB daerah, payment gateway |
| 12 | `PRD-SIMS-12-Go-to-Market-dan-Legal.md` | Program pilot, sales motion, checklist legal UU PDP + PP 17/2025 |
| 13 | `PRD-SIMS-13-SOP-Engineering.md` | SOP: standar kode, review, Git, changelog, dokumentasi |
| 14 | `wiki-SIMS/` | Vault Obsidian: Home, Glosarium, ADR (5 keputusan), template RFC/Runbook, runbook restore, checklist onboarding |
| 15 | `PRD-SIMS-15-Tanggapan-Audit.md` | Tanggapan resmi atas audit eksternal: verdict per temuan + rencana koreksi |

## Keputusan produk yang sudah ditetapkan

- Model bisnis: **SaaS multi-tenant publik** (tiap sekolah = tenant pelanggan)
- Harga: **flat per sekolah** — Basic Rp 499rb/bln + à la carte, Pro Rp 999rb/bln all-inclusive, Enterprise kustom
- Arsitektur: **satu aplikasi** (bukan per-sekolah), shared-schema `school_id`, custom domain via CNAME didukung
- Identitas: **global + membership per tenant** (ADR-0004); token = 1 membership aktif
- Isolasi tenant: **fail-closed** (scope melempar exception tanpa konteks)
- Login: WA OTP (utama), passkey Fase 2, QR untuk siswa tanpa HP; 2FA wajib peran platform
- Trial: 30 hari setara Pro dengan batas eksplisit (100 siswa, tanpa broadcast/API)
- UI: **tanpa Filament** — custom mengikuti design system (disediakan terpisah)

---
*Dibuat 9 Oktober 2026 · Dokumen hidup — perubahan dicatat via changelog masing-masing dokumen.*
