# Integrasi SIMS dengan Laravel (contoh starter)

Ini **scaffold integrasi** ke aplikasi Laravel yang sudah ada, bukan aplikasi Laravel penuh. Tidak ada database/migrasi/auth production dalam ZIP ini. File contoh berada di folder `laravel/`.

## 1 — Copy asset

Copy:
- `../assets/css/design.css` → `resources/css/sims-design.css`
- `../assets/js/data.js` → `resources/js/sims/data.js`
- `../assets/js/templates.js` → `resources/js/sims/templates.js`
- `../assets/js/app.js` → `resources/js/sims/app.js`
- `laravel/resources/views/sims/index.blade.php` → `resources/views/sims/index.blade.php`
- `laravel/resources/css/app.css` → `resources/css/app.css` (merge jika sudah punya CSS)
- `laravel/resources/js/app.js` → `resources/js/app.js` (merge jika sudah punya JS)

Lihat `index.blade.php` untuk markup shell yang disediakan. Untuk pemisahan komponen lebih granular: pecah `<aside>`, `<header>`, `<footer>`, `<div class="modal-backdrop">` menjadi partial Blade dan gunakan `@include`.

## 2 — Install dependencies

```bash
npm install alpinejs tailwindcss @tailwindcss/vite
```

Pastikan `vite.config.js` menggunakan `laravel-vite-plugin` dan `@tailwindcss/vite`. Lihat contoh `laravel/vite.config.js`.

`resources/css/app.css`:
```css
@import 'tailwindcss';
@source '../views/**/*.blade.php';
@source '../js/**/*.js';
@import './sims-design.css';
```

`resources/js/app.js`:
```js
import Alpine from 'alpinejs';
import './sims/data.js';
import './sims/templates.js';
import './sims/app.js';
window.Alpine = Alpine;
Alpine.start();
```

Catatan: skrip SIMS mengakses `window.SIMS` dan memasang listener `alpine:init` sehingga **import harus berurutan sebelum Alpine.start()**.

Di Blade, hapus script Tailwind CDN, Alpine CDN, dan asset JS standalone; ganti dengan:

```blade
@vite(['resources/css/app.css','resources/js/app.js'])
```

> Hindari memasang Alpine lebih dari sekali jika menggunakan Livewire yang membawa Alpine.

## 3 — Routes

Tambahkan di `routes/web.php`:

```php
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::view('/sims', 'sims.index')->name('sims');
});
```

SPA frontend menggunakan hash `#/students`, `#/finance`, dll. Semua URL tetap pada route `/sims`.

Jika ingin clean path /sims/students, gunakan route Laravel + history API atau ubah route SPA ke pathname, dan buat fallback route server secara eksplisit.

## 4 — Backend & data binding

Sekarang data berasal dari `SIMS.rows` (`data.js`) dan CRUD menyimpan ke localStorage. Untuk production buat REST API `/api/sims/students`, `/api/sims/teachers`, dll, dan ubah method `hydrate()`, `persist()`, `openEntityModal()`, `remove()` di `app.js` menjadi HTTP request.

Contoh endpoint backend ada pada `app/Http/Controllers/StudentController.php.example` dan `routes/api.php.example`.

Sangat disarankan:
- Validasi Request per modul, pagination/filter server-side, unique keys dan Eloquent models.
- Auth Laravel dengan role (admin, guru, wali, siswa), policies/gates untuk setiap mutasi.
- Cookie session + CSRF bila menggunakan web routes, atau Sanctum bila API terpisah.
- Soft deletes untuk data sensitif, audit logging dan backup.
- Pengamanan akses berkas siswa/wali dan aturan retensi data sesuai kebijakan sekolah.
- Gunakan Blade `@json`/`Js::from()` untuk memuat data awal, jangan menggabungkan raw input dalam HTML.
- Tetapkan CSP, headers keamanan, upload constraints serta pembatasan rate.

## 5 — Komponen Laravel yang direkomendasikan

Untuk implementasi final, gunakan komponen seperti:

```
resources/views/components/sims/
  button.blade.php
  badge.blade.php
  card.blade.php
  input.blade.php
  modal.blade.php
  data-table.blade.php
resources/views/sims/partials/
  sidebar.blade.php
  header.blade.php
  command-palette.blade.php
```

Tokens CSS sudah disentralisasi dalam `sims-design.css`. Contoh `resources/views/components/sims/button.blade.php` tersedia.

## 6 — Build

```bash
npm run dev      # saat mengembangkan
npm run build    # untuk server production
```

**Jangan gunakan CDN Play Tailwind di production.**
