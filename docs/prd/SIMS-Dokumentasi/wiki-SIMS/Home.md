# SIMS — Wiki Developer

> Buka folder ini sebagai **vault** di aplikasi [Obsidian](https://obsidian.md) (gratis) untuk navigasi graf & wikilink. Semua file juga terbaca sebagai markdown biasa.

## Peta Isi

- [[Glosarium]] — satu istilah = satu makna (acuan penamaan kode)
- [[Tech-Debt]] — register utang teknis
- **Keputusan:** [[adr/0001-monolit-modular-laravel|ADR-0001]] · [[adr/0002-shared-schema-multi-tenant|ADR-0002]] · [[adr/0003-flat-pricing|ADR-0003]] · [[adr/0004-identitas-global-membership|ADR-0004]] · [[adr/0005-baseline-laravel-13|ADR-0005]] · [[adr/0000-template|Template ADR]]
- **Usulan perubahan besar:** [[rfc/0000-template|Template RFC]]
- **Runbook:** [[runbooks/restore-per-tenant|Restore per-tenant]] · [[runbooks/0000-template|Template Runbook]]
- **Diagram alur (Graphify):** [[diagrams/_README|Tentang diagram]]
- **Onboarding:** [[onboarding/checklist-developer-baru|Checklist developer baru]]

## Aturan main (ringkas)

1. Satu halaman = satu topik, maksimal ~300 baris.
2. Rujuk halaman lain dengan `[[wikilink]]`, bukan path.
3. Keputusan desain yang sulit dibatalkan → tulis [[adr/0000-template|ADR]] baru.
4. Diagram hanya dalam Mermaid dan **dihasilkan dari kode** (lihat [[diagrams/_README|Graphify]]) — dilarang menempel gambar manual.

---
*SOP lengkap: [[../PRD-SIMS-13-SOP-Engineering|SOP Engineering]] (Dok 13).*
