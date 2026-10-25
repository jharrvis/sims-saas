# SIMS Reference Design — Source of Truth

Folder ini adalah snapshot desain resmi yang harus dijadikan referensi visual dan interaksi saat mengimplementasikan tema SIMS ke aplikasi Laravel di repository ini.

## Wajib Dibaca Sebelum Mengubah UI

Urutan baca untuk developer atau AI coding agent:

1. `../PWA-THEME-IMPLEMENTATION-PLAN.md` — urutan implementasi dan acceptance criteria.
2. `DESIGN-SYSTEM.md` — kontrak visual, responsif, motion, loading, dan aksesibilitas.
3. `source/pwa/index.html` — struktur app shell dan halaman login terbaru.
4. `source/pwa/styles.css` — token dan aturan responsif yang sudah diverifikasi.
5. `source/pwa/app.js` — referensi loading, navigasi, grafik, dan simulasi interaksi.
6. `previews/` — baseline visual desktop, tablet, dan mobile.
7. `source/assets/` dan `source/laravel/` — referensi dashboard desktop dan pola integrasi Laravel.

## Hierarki Keputusan

Jika terdapat konflik, gunakan urutan prioritas berikut:

1. Keamanan, tenant isolation, authorization, dan business rule aplikasi production.
2. Arsitektur Laravel/Livewire yang sudah digunakan oleh project.
3. Plan implementasi pada `../PWA-THEME-IMPLEMENTATION-PLAN.md`.
4. Kontrak desain pada `DESIGN-SYSTEM.md`.
5. Source HTML/CSS/JS pada folder `source/` sebagai referensi tampilan dan perilaku.

Template tidak boleh menggantikan validasi, autentikasi, permission, atau data server. Mock data, delay simulasi, kredensial demo, deteksi role frontend, dan penyimpanan session di `localStorage` hanya untuk demonstrasi.

## Peta Referensi

| Kebutuhan | Referensi utama |
|---|---|
| Login PWA terbaru | `source/pwa/index.html`, `source/pwa/styles.css` |
| Loading/database simulation | `source/pwa/app.js` (`databaseOperation`) |
| Dashboard siswa | `source/pwa/app.js` (`studentHome`, `studentAcademics`, `studentFinance`) |
| Dashboard guru/pegawai | `source/pwa/app.js` (`genericHome`, `genericSection`) |
| Grafik nilai | `source/pwa/app.js` (`gradeChart`, `updateChart`) |
| PWA manifest/offline | `source/pwa/manifest.webmanifest`, `source/pwa/sw.js` |
| Token dashboard desktop | `source/assets/css/design.css` |
| Shell/dashboard desktop | `source/index.html`, `source/assets/js/templates.js` |
| Integrasi Blade/Vite | `source/laravel/README.md`, `source/laravel/resources/` |
| Baseline visual | `previews/*.png`, `source/preview-dashboard.png` |

## Aturan Login Responsif Terbaru

- `< 600px`: satu kolom, gutter 18px, seluruh form memenuhi lebar yang tersedia.
- `600–899px`: satu kolom terpusat dengan lebar maksimum 620px.
- `>= 900px`: dua kolom; pengantar/identity note di kiri dan metode/form login di kanan.
- Kolom form desktop berada pada rentang 380–440px.
- Container login desktop memiliki lebar maksimum 1120px.
- Pada `>= 1180px`, gutter global halaman tidak boleh diterapkan kembali di dalam container login yang sudah memiliki `max-width`; referensi memakai `.auth-screen { padding-inline: 0; }`.
- Jangan mengubah ukuran seluruh aplikasi dengan `zoom` atau `transform: scale()` untuk menyelesaikan responsivitas.

## Baseline yang Sudah Diverifikasi

- `previews/login-mobile-fixed.png` — 390×844.
- `previews/login-tablet-fixed.png` — 768×1024.
- `previews/login-desktop-fixed.png` — 1920×1080.
- Tab Password, input kredensial, simulasi loading, dan redirect dashboard siswa telah diuji.
- Browser console pada baseline: 0 error dan 0 warning.

## Workflow Wajib untuk AI/Developer

1. Baca dokumen dan source reference di atas.
2. Petakan komponen reference ke komponen Blade/Livewire yang sudah ada; hindari membuat implementasi paralel.
3. Pertahankan business logic dan authorization di server.
4. Implementasikan perubahan kecil per fase sesuai plan.
5. Uji minimal pada 390×844, 768×1024, dan 1920×1080.
6. Bandingkan hasil dengan screenshot baseline.
7. Jalankan build/test project dan cek browser console sebelum menyatakan selesai.
