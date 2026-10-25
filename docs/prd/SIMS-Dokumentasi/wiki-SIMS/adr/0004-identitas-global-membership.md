# ADR-0004: Identitas Global + Membership per Tenant

- **Status:** Accepted
- **Tanggal:** 2026-10-09
- **Pengusul:** Tech Lead
- **Menggantikan:** G-08 (satu akun per tenant) — dicabut karena bertentangan dengan FR-203

## Konteks

G-08 menetapkan satu akun per tenant, tetapi FR-203 + US-070 mensyaratkan satu akun wali melihat anak lintas tenant (P0-01). Skema `users.school_id` tunggal tidak bisa memenuhi keduanya. Selain itu, konvensi `school_id = null` = operator platform membuat hapus tenant berpotensi mencetak akun ber-bentuk-identik dengan akun privilege (P0-03), dan `username`/`email` unique global bertentangan dengan akun ganda (P1-01).

## Keputusan

- `users` = **identitas global**: satu baris per orang, TANPA `school_id`. Kredensial, kontak terverifikasi, MFA.
- `memberships` (`user_id`, `school_id`, `status`): keanggotaan per tenant. Role memakai Spatie Teams per `school_id`.
- Token Sanctum membawa **tepat satu** `school_id` membership aktif. Dashboard lintas tenant = agregasi server-side per membership terverifikasi (token multi-tenant dilarang).
- Operator platform = `users.is_platform = true` + guard `platform` terpisah. **Dilarang** menyimpulkan privilege dari `school_id` null — kolom itu sudah tidak ada.
- Guardian domain (`guardians`) tetap per-tenant, terhubung ke identitas via `guardians.user_id` nullable.

## Alternatif yang dipertimbangkan

1. Pertahankan satu akun per tenant, hapus FR-203 lintas tenant — ditolak: UX orang tua lebih penting; identitas berbasis no. WA memang natural global.
2. Composite unique `(school_id, username)` — ditolak: tidak menyelesaikan token lintas tenant & inkonsistensi lifecycle.

## Konsekuensi

- (+) Satu login untuk semua peran/tenant; lifecycle tenant ≠ lifecycle identitas.
- (+) Menghapus satu sekolah tidak pernah menyentuh identitas.
- (−) Migrasi skema + token model; switcher tenant wajib di UI.
- Tindak lanjut: US-126 (sesi/refresh), fail-closed scope (P0-02).

## Referensi

- Dok 15 (P0-01, P0-03, P1-01) · Dok 02 §8.14 · Dok 03 §2.9
