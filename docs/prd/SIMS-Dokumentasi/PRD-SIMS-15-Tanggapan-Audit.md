# Dok 15 — Tanggapan Audit Total & Rencana Koreksi

> Tanggapan resmi atas `PRD-SIMS-14-Audit-Total-2026.md` (audit eksternal, 9 Okt 2026).
> Metode: setiap temuan diverifikasi ulang terhadap dokumen sumber sebelum diberi verdict.
> Verdict: **VALID** = temuan benar, diakui & diperbaiki · **DITOLAK** = temuan keliru, dibantah dengan bukti · **SEBAGIAN** = benar sebagian.

## 1. Ringkasan Verdict

| ID | Temuan | Verdict | Tindakan |
|---|---|---|---|
| P0-01 | Identitas wali lintas tenant vs satu akun per tenant | **VALID** | Model diganti: identitas global + membership (ADR-0004); G-08 dicabut |
| P0-02 | Tenant isolation fail-open | **VALID** | Scope jadi fail-closed; resolver wajib domain terverifikasi + cek membership; RLS |
| P0-03 | Hapus tenant → user jadi "platform" | **VALID** | `is_platform` eksplisit; `school_id=null` ≠ privilege; guard terpisah |
| P0-04 | Trial/paket/provisioning bercabang | **VALID** | Sisa `starter/growth/scale` & `addDays(14)` dibersihkan di Dok 02/03 |
| P0-05 | Dunning/retensi/purge tidak konsisten | **VALID** | Satu state machine; invoice `nullOnDelete` + snapshot; retensi disatukan |
| P0-06 | Runbook restore belum executable | **SEBAGIAN** | Dibatasi sebagai prosedur darurat + manifest dependensi; undo utama via `import_batch_id` (US-123) |
| P0-07 | Kepatuhan data anak belum operasional | **VALID** | Skema `consent_ledger` + `dsar_cases`; DPIA (US-124/128); PP 17/2025 masuk baseline |
| P1-01 | Username/email unique global | **VALID** | Selesai via ADR-0004 (identitas global memang unique; membership per tenant) |
| P1-02 | Slug role tidak kanonis | **VALID** | Kanon: `snake_case` (`admin_sekolah`); seeder Dok 05 diperbaiki |
| P1-03 | Angka ringkasan drift (122 vs 100 story) | **VALID** | Klaim "122 story" dikoreksi → 100 unik; aturan: angka dihitung CI, bukan manual |
| P1-04 | Backlog 510 pts vs kapasitas 382 | **VALID** | Re-baseline: MVP = Must 445 pts → 12 sprint (24 minggu); Should/Could → Fase 2 |
| P1-05 | Roadmap 12–15 minggu vs 20 minggu | **VALID** | PRD §14 diselaraskan ke 12 sprint / 24 minggu |
| P1-06 | Entitlement 3 sumber kebenaran | **SEBAGIAN** | Klarifikasi katalog/kontrak/materialisasi; `EntitlementService` satu pintu + rekonsiliasi di US-103 |
| P1-07 | Model integrasi & reliability belum ada | **VALID** | Migrasi baru: webhook, payment ledger, idempotency (US-127) |
| P1-08 | Kontrak autentikasi tak lengkap | **VALID** | Spec sesi/refresh/rotasi/reuse-detection ditambahkan (US-126) |
| P1-09 | MFA: opsional vs wajib vs Fase 2 | **VALID** | Diputus: **wajib** untuk peran platform sejak go-live (Sprint 9); FR-904 direvisi |
| P1-10 | Pricing & unit economics | **SEBAGIAN** | Contoh basi Dok 10:179 diperbaiki; overage WA + cost floor Enterprise diakui perlu (Fase 2) |
| P1-11 | Baseline teknologi menua | **VALID** | Baseline baru: **Laravel 13 + PHP 8.3 + Pest 4** (terverifikasi 9 Okt 2026); Filament dihapus total (keputusan pemilik produk) |
| P1-12 | API tanpa source of truth machine-readable | **VALID** | OpenAPI 3.1 sebagai kontrak kanonis (US-125) |
| P1-13 | Persistence belum menutup janji MVP | **VALID** | Migrasi: consent, DSAR, import batch, WebAuthn (US-124) |
| P2-01–P2-09 | Saran medium | **DITERIMA** | Masuk DoD/acceptance criteria; sebagian sudah tercakup (P2-05, P2-06) |

**Keputusan pemilik produk yang dicatat:** Filament **tidak dipakai** — UI custom mengikuti design system yang akan disediakan. Seluruh referensi Filament dihapus dari dokumen (§6).

---

## 2. Bantahan & Konfirmasi per Temuan

### P0-01 — VALID. Kontradiksi G-08 vs FR-203 memang nyata.

Bukti terverifikasi: G-08 (PRD:98) menetapkan "satu akun per tenant... begitu pula wali murid yang anaknya di sekolah berbeda", sementara FR-203 (PRD:213) + US-070 mensyaratkan satu akun wali melihat anak lintas tenant. Catatan "sudah ditangani via relasi wali→anak per tenant" di G-08 tidak menyelesaikan apa-apa — relasi domain ≠ identitas login.

**Keputusan:** FR-203 dipertahankan (UX yang benar untuk orang tua), G-08 **dicabut** dan diganti **ADR-0004**: identitas global (`users` tanpa `school_id`) + `memberships` (user × school × status). Token Sanctum membawa **satu membership aktif**; dashboard lintas tenant diagregasi server-side dengan memeriksa tiap membership + relasi wali-anak satu per satu. Contoh `school_ids[]` di token (Dok 03:107) dihapus.

### P0-02 — VALID. Scope fail-open terkonfirmasi di kode contoh.

`SchoolScope::apply()` hanya memfilter bila `currentSchoolId` ter-binding; tanpa binding query jalan tanpa filter. Ini bukan sekadar "niat baik yang belum diuji" — ini pola yang salah di level contoh. **Diperbaiki:** scope melempar `TenantContextMissing` bila konteks tidak ada (fail-closed); akses lintas tenant hanya via helper eksplisit `withoutTenantScope()` yang wajib berpasangan dengan policy + audit log. `ResolveTenant` diperketat: hanya domain `terverifikasi=true` + `aktif`, dan setelah auth wajib validasi membership aktif user terhadap tenant hasil resolusi. RLS PostgreSQL ditambahkan sebagai pertahanan lapis kedua (Sprint 2).

### P0-03 — VALID. `nullOnDelete` + `null` = platform adalah bom waktu.

Kombinasi `users.school_id->nullable()->nullOnDelete()` dengan konvensi "`null` = superadmin pusat" berarti menghapus satu sekolah dapat mencetak akun ber-bentuk-identik dengan operator platform. Guard role mungkin menahan, tapi invariant sudah rusak. **Diperbaiki:** kolom `is_platform` boolean eksplisit (default false, hanya diubah via console/command teraudit); guard `platform` terpisah dari guard `tenant`; hard-delete sekolah dilarang sebelum seluruh membership dicabut.

### P0-04 — VALID. Sisa-sisa era `starter/growth/scale` memang masih ada.

Terverifikasi: seeder `addDays(14)` berlabel "Trial 30 hari" (Dok 02), `paket: starter/growth/scale` + default 14 hari (Dok 03 §10.2), contoh invoice "Paket Growth Rp1,5 jt" (Dok 03:1110,1146). Ini sisa dari sebelum keputusan flat pricing — luput saat migrasi massal kemarin. **Dibersihkan sekarang.** Ke depan: katalog paket berversi sebagai source of truth + contract test (US-125 mencakup enum plan di OpenAPI).

### P0-05 — VALID. Tiga versi kebenaran retensi memang hidup berdampingan.

PRD §9.4 (H+90: hapus hanya setelah konfirmasi tertulis) vs PRD §17.6 (read-only 14 hari → purge T+30) vs Dok 03 (retensi 90 hari). Plus `invoices.school_id cascadeOnDelete` yang menghapus bukti fiskal 10 tahun bersama tenant. **Disatukan sekarang:** satu state machine `trialing → active → past_due → read_only → cancelled → purge_scheduled → purged (+ legal_hold)`; siklus baku: cancel → read-only 14 hari (ekspor/reaktivasi) → soft-delete → purge T+30; invoice & payment records `nullOnDelete` + kolom snapshot (`nama_sekolah`, `npsn`) sehingga arsip fiskal selamat dari purge tenant. H+90 di §9.4 direvisi menjadi pemicu `cancelled`, bukan aturan hapus tersendiri.

### P0-06 — SEBAGIAN. Kritiknya tepat, tapi status runbook tidak pernah diklaim final.

Yang benar dari audit: (1) pivot `guardian_student`/`class_student` memang tidak punya `school_id` langsung — query `WHERE school_id = X` per tabel tidak cukup; (2) belum ada ID remapping/sequence handling; (3) restore PITR dapat menimpa write sah pasca-backup. Yang perlu diluruskan: runbook tidak pernah diklaim "teruji" (kolom `Terakhir diuji` memang kosong — itu kejujuran, bukan klaim palsu), dan dokumen sudah mencatat `import_batch_id` sebagai mekanisme undo impor. **Tindakan:** runbook dibatasi eksplisit sebagai *prosedur darurat yang mensyaratkan manifest dependensi + drill*; mekanisme undo utama untuk kasus sehari-hari (salah impor) adalah compensating rollback via `import_batch_id` (**US-123**, Must). Drill kuartalan tetap wajib dengan bukti RPO/RTO.

### P0-07 — VALID. Dokumen legal ada, mesinnya belum.

Benar: tidak ada tabel `consent_ledger`, tidak ada workflow DSAR, tidak ada DPIA, dan PP 17/2025 belum masuk baseline. Pembelaan kecil: Dok 12 memang memposisikan ini sebagai "checklist pra-pilot", bukan klaim sudah-selesai. Tapi audit benar bahwa tanpa skema, ini janji kosong. **Tindakan:** migrasi `consent_ledger` (immutable: policy version, purpose, actor, child, guardian relation, timestamp, evidence, withdrawal) + `dsar_cases` (US-124, Must); DPIA + RoPA + register subprosesor (US-128, Must, sebelum pilot); PP 17/2025 ditambahkan ke baseline kepatuhan Dok 12.

### P1-01 — VALID, selesai via ADR-0004.

Dengan identitas global, `username`/`email` unique global memang benar — dan membership per tenant menyelesaikan kebutuhan "satu orang, banyak sekolah". Tidak perlu composite key.

### P1-02 — VALID. Seeder Dok 05 memakai kebab-case, dokumen lain snake_case.

**Dikanonkan ke `snake_case`** (`admin_sekolah`, `platform_admin`, `wali_kelas`, `orang_tua`) — mengikuti konvensi permission `{modul}.{resource}.{aksi}` yang juga snake. Seeder diperbaiki + authorization test executable.

### P1-03 — VALID. "122 user stories" adalah angka ID maksimum, bukan jumlah story.

Hitungan aktual: **100 ID unik** (22 ID kosong karena penggabungan). Klaim di indeks diperbaiki. Aturan baru (SOP §14): angka ringkasan (story, permission, endpoint) **dihasilkan parser CI**, dilarang update manual.

### P1-04 — VALID. 510 vs 382 = over 33,5%. Ini kesalahan saya saat menambah story tanpa re-baseline.

**Re-baseline (diputus sekarang):** MVP = story **Must** saja = 432 pts + 2 story Must baru (US-124: 8, US-128: 5) = **445 pts → 12 sprint @ ~37 pts** (kapasitas 38,2/sprint; total 458). Story **Should (73) + Could (5)** = 78 pts dipindah ke **Fase 2 (pasca-pilot)**. Sprint plan 10 → 12 sprint; pemetaan detail story→sprint direvisi di sprint planning (dicatat sebagai agenda wajib, bukan dikarang di dokumen).

### P1-05 — VALID. Fase 0 (4–5 mg) + Fase 1 (8–10 mg) ≠ 10 sprint (20 mg).

**Diselaraskan:** Fase 0 = Sprint 1–2 (minggu 1–4, fondasi SaaS); Fase 1 = Sprint 3–12 (minggu 5–24, MVP akademik + pilot). Total **24 minggu** sampai pilot stabil — konsisten dengan rekomendasi audit (20–26 minggu).

### P1-06 — SEBAGIAN. Tiga tabel memang menyimpan sinyal entitlement, tapi bukan tiga "kebenaran" yang bertentangan.

Klarifikasi desain: `modules`/`plans` = **katalog** (apa yang dijual), `subscription_items` = **kontrak** (apa yang dibeli), `tenant_modules` = **materialisasi** (apa yang aktif sekarang, hasil kalkulasi). Yang benar dari audit: tanpa satu pintu, ketiganya bisa drift. **Tindakan:** `EntitlementService::isActive($school, $module)` sebagai satu-satunya penentu (Dok 02 §9.5); job rekonsiliasi idempotent ditambahkan sebagai acceptance criteria US-103.

### P1-07 — VALID. Webhook & payment dijanjikan di API tapi tidak ada tabelnya.

**Ditambahkan:** migrasi `webhook_subscriptions`, `webhook_deliveries` (berfungsi sebagai outbox + retry ledger), `payment_transactions` (ledger pembayaran), `idempotency_keys` (uniqueness per provider/event) — **US-127**.

### P1-08 — VALID. Refresh token disebut tapi tidak dispesifikasi.

**Ditambahkan ke Dok 03:** model sesi/device (`user_sessions`: device fingerprint, IP, last active), refresh rotation dengan **reuse detection** (reuse = revoke seluruh token family + alert), endpoint `POST /auth/refresh`, `GET /auth/sessions`, `DELETE /auth/sessions/{id}`, revoke chain — **US-126**.

### P1-09 — VALID. Tiga pernyataan MFA saling bertentangan (FR-904 opsional, §17.2 wajib platform, backlog Fase 2).

**Diputus sekarang:** 2FA/TOTP **wajib untuk seluruh peran platform sejak go-live** (masuk Sprint 9, Must — bukan Fase 2); **dianjurkan kuat** untuk admin sekolah. FR-904 direvisi; backlog diperbarui.

### P1-10 — SEBAGIAN. Satu temuan tepat, sisanya catatan yang fair.

Yang **tepat**: Dok 10:179 masih menulis "Pro + LMS Rp 349 rb + Perpus Rp 249 rb" — basi dari era pra-all-inclusive, **diperbaiki** (Pro = semua modul). Yang **fair tapi ditunda ke Fase 2**: overage pricing WA di atas kuota Pro (3.000 msg) dan cost floor kontrak Enterprise (dedicated DB + CSM tidak bisa dijustifikasi angka shared-infra Rp 89 rb). Argumen saya: untuk MVP, kuota + fair-use sudah cukup sebagai guardrail; model overage & ACV minimum Enterprise dirancang sebelum kontrak Enterprise pertama (dicatat di Dok 10 sebagai agenda pra-sales Enterprise, bukan MVP).

### P1-11 — VALID (terverifikasi via sumber resmi, 9 Okt 2026).

Laravel 12: bug-fix berakhir 13 Agu 2026, security-fix berakhir 24 Feb 2027 — go-live kita (Q1 2027) menabrak EOL. Laravel 13 (rilis 17 Mar 2026, PHP 8.3+, bug-fix s/d Q3 2027, security s/d Mar 2028) adalah baseline yang benar; upgrade 12→13 dilaporkan minim breaking changes. Pest 2 → **Pest 4**. Catatan Filament dari audit **moot**: pemilik produk memutuskan tidak memakai Filament — UI custom di atas Blade + Livewire 3 + Tailwind mengikuti design system yang akan disediakan.

### P1-12 — VALID. Tabel + contoh JSON bukan kontrak yang bisa diuji mesin.

**US-125 (Must, Sprint 2):** OpenAPI 3.1 sebagai kontrak kanonis — generate docs, validator, typed client PWA, mock server, dan contract test di CI. Dok 03 tetap menjadi spec manusiawi; yang mengikat adalah file OpenAPI.

### P1-13 — VALID. Beberapa janji MVP tidak punya tabel.

**Ditambahkan:** `consent_ledger`, `dsar_cases`, `import_batches`, `webauthn_credentials` (US-124). `notification_templates`: ada story (US-107) tapi belum ada migrasi — ditambahkan. `product_analytics_events`: ditandai **pasca-MVP** (cukup event log generik di MVP).

### P2-01–P2-09 — DITERIMA sebagai acceptance criteria / agenda.

Catatan: P2-05 (audit log minim PII) dan P2-06 (uji beban kalender sekolah) sebagian sudah ada di PRD §17/NFR — dipertegas menjadi gate. P2-07 (business continuity/offline) masuk Fase 2. P2-09 (WCAG 2.2 AA, low-bandwidth) menjadi acceptance criteria wireframe setelah design system diterima.

---

## 3. Yang Diperbaiki Langsung di Dokumen (turn ini)

1. **Filament dihapus** dari seluruh dokumen → diganti "UI custom (Blade + Livewire 3 + Tailwind) mengikuti design system".
2. **Baseline: Laravel 13, PHP 8.3+, Pest 4** (PRD §6.1, Dok 04 US-001, Dok 13).
3. **ADR-0004**: identitas global + membership (menggantikan G-08); **ADR-0005**: baseline Laravel 13.
4. **Dok 02**: tabel `users` tanpa `school_id` + `is_platform`; migrasi `memberships`; `SchoolScope` fail-closed; `ResolveTenant` (domain terverifikasi + cek membership); invoice `nullOnDelete` + snapshot; migrasi baru 000035–000042 (webhook, payment, idempotency, consent, DSAR, import batch, WebAuthn, notification templates); role slug snake_case.
5. **Dok 03**: token = 1 membership aktif; contoh `school_ids[]` dihapus; `paket: basic/pro/enterprise`; trial default 30; contoh Growth → Pro; retensi diselaraskan §17.6; spec sesi/refresh (US-126).
6. **PRD**: G-08 dicabut; §9.4 H+90 → pemicu `cancelled`; §14 roadmap → 12 sprint/24 minggu; FR-904 → MFA wajib platform; §6.1 tech stack baru; state machine retensi eksplisit di §17.6.
7. **Dok 04**: re-baseline (Must 445 pts / 12 sprint; Should+Could → Fase 2); story baru US-123–US-128; MFA ke Sprint 9.
8. **Dok 05**: seeder role snake_case; guard `platform` vs `tenant`.
9. **Dok 10**: contoh basi diperbaiki + agenda overage/cost floor.
10. **Dok 12**: PP 17/2025 masuk baseline; DPIA/US-128 sebagai gate pilot.
11. **Dok 13**: §2.5 tanpa Filament; aturan angka-otomatis-CI.

## 4. Rencana Koreksi Berurutan (adopsi dari audit §8, disesuaikan)

**0–5 hari (keputusan):** D-01 s/d D-08 — D-01 (identitas) ✅ diputus via ADR-0004; D-05 (baseline) ✅ Laravel 13; D-06 (scope) ✅ re-baseline; D-03/D-04 ✅ state machine & katalog; D-07/D-08 dijadwalkan pre-pilot (US-124/128, drill restore).

**Minggu 1–2 (kontrak kanonis):** ADR-0004/0005 ✅; OpenAPI 3.1 (US-125); permission registry executable; retention matrix; traceability (P2-02, tooling CI).

**Minggu 3–6 (fondasi terbukti):** fail-closed + RLS + negative isolation tests; outbox/idempotency (US-127); consent + DSAR (US-124); sesi/refresh (US-126); restore drill pertama.

**Sebelum pilot data nyata:** semua P0 closed dengan evidence; isolation test per route; drill restore memenuhi RPO/RTO; DPA + DPIA + consent/DSAR siap; MFA platform aktif; load test kalender sekolah; unit economics mencakup variable cost penuh.

## 5. Batasan Tanggapan Ini

Audit menilai rancangan — dan tanggapan ini juga masih di level rancangan. Pembuktian sesungguhnya (isolation test, drill restore, contract test) terjadi saat implementasi. Dokumen ini memastikan tidak ada lagi **dua kebenaran** untuk satu hal sebelum Sprint 1 dimulai.
