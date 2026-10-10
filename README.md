# SIMS — SaaS Multi-Tenant Sistem Informasi Manajemen Sekolah

Aplikasi Laravel 13 (PHP 8.3+) multi-tenant untuk manajemen sekolah: satu aplikasi
terpusat, setiap sekolah adalah tenant pelanggan independen (shared-schema dengan `school_id`).

## Stack

- Laravel 13 + PHP 8.3 + Pest 4
- Blade + Livewire 3 + Tailwind CSS v4 (tanpa Filament — UI custom mengikuti design system)
- Sanctum (API), Spatie Permission (RBAC `{modul}.{resource}.{aksi}`, teams per tenant)
- PostgreSQL 16 + Redis (produksi/dev via Docker), SQLite (test)

## Mulai cepat

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install && npm run dev
php artisan serve
```

Dengan Docker:

```bash
docker compose up --build
```

## Fondasi tenant (fail-closed)

- `App\Tenancy\TenantContext` — singleton konteks tenant aktif.
- `App\Scopes\SchoolScope` — global scope; query model tenant **tanpa konteks melempar
  `TenantContextMissing`**, bukan berjalan tanpa filter.
- `App\Concerns\BelongsToSchool` — trait untuk semua model operasional tenant.
- `App\Http\Middleware\ResolveTenant` — resolusi tenant dari domain (hanya domain
  terverifikasi & aktif), validasi membership pasca-auth, guard status langganan.
- Identitas global + membership (`ADR-0004`): `users` tanpa `school_id`;
  operator platform memakai flag eksplisit `is_platform`.

## Struktur domain

`app/Domains/` berisi 18 domain modul (Identity, Tenancy, Billing, Akademik, …).
Lihat `wiki-SIMS/` pada dokumentasi untuk ADR dan glosarium.

## Quality gates (CI)

Setiap push/PR menjalankan: Pint (`--test`), Larastan level 6, Pest, `composer audit`.

## Dokumentasi & prototipe

- `design/` — prototipe HTML design system v2 (referensi visual, Tailwind CDN).
- Dokumentasi PRD lengkap: `~/workspace/your_files/PRD-SIMS-*.md`.
