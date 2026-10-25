# SIMS Design System — School Management Dashboard

HTML SPA template untuk Sistem Informasi Manajemen Sekolah, dirancang agar mudah di-port ke Laravel.

**Stack:** Tailwind CSS v4 (Play CDN untuk preview), Alpine.js v3, JavaScript, HTML5, CSS Design Tokens, inline SVG icons. **Tidak menggunakan React/Vue atau jQuery.**

## Preview langsung

1. Ekstrak ZIP.
2. Buka `index.html` di Chrome/Edge dengan koneksi internet (Tailwind/Alpine diambil dari CDN).
3. Untuk pengujian SPA yang lebih stabil, jalankan `python -m http.server 8000` dari folder ini dan buka `http://localhost:8000`.

## Modul yang sudah disediakan

- Dashboard berisi KPI, chart kehadiran, distribusi siswa, nilai rata-rata, jadwal, pengumuman, keuangan, dan siswa terbaru.
- Panel Platform.
- Data Siswa, Wali, Guru, Kelas, Mata Pelajaran.
- Jadwal Pelajaran, Absensi Siswa, Data Nilai, Komponen Nilai.
- Kesiswaan, Keuangan, Sarana & Layanan.
- Kalender Akademik, Pengumuman, Laporan, Pengaturan.
- Design System (warna, typography, buttons, status badges, forms, input types, toggle, radio, checkbox, tabs, modal, toasts, accordion, avatar, loading skeleton, empty state, data table).
- Panduan integrasi Laravel.
- Halaman login terpisah (`login.html`).

## Fitur interaktif

- Navigasi SPA berbasis hash URL dan Alpine.js tanpa reload.
- Sidebar collapsible, mobile overlay; theme toggle light/dark + preferensi lokal.
- Search / command palette `Ctrl + K`.
- Pencarian, filter, checkbox bulk select, pagination, tambah/edit/hapus data di modul master.
- Data mock CRUD tersimpan di `localStorage`, bukan di database.
- Modal, toast, shortcut, pengumuman, kalender dan absensi demo.
- CSV export kompatibel Excel (UTF-8 BOM), print menggunakan dialog browser.

> **Penting:** Demo data dan interaksi frontend bukan aplikasi sekolah production. Validasi, autentikasi, RBAC, audit log, CSRF, transaksi, email, penyimpanan data, dan data privacy harus ditambahkan di Laravel backend.

## Struktur

```
index.html             aplikasi SPA standalone
login.html             halaman login standalone (UI only)
assets/css/design.css  CSS theme tokens, components, responsive
assets/js/data.js      menu, icons, konfigurasi tabel, mock data
assets/js/templates.js halaman dan komponen reusable
assets/js/app.js       Alpine SPA, CRUD demo, UI state
laravel/               contoh starter integrasi Blade + Vite
pwa/                   template mobile PWA siswa, guru, dan pegawai
```

## SIMS Mobile PWA

Jalankan proyek melalui HTTP, lalu buka `/pwa/` untuk mencoba versi mobile installable. PWA menyediakan beranda sesuai peran, mode offline, serta template login WhatsApp OTP, scan QR/barcode, dan username/password. Kode OTP demo adalah `123456`.

Implementasi production memerlukan endpoint autentikasi Laravel dan provider WhatsApp Business resmi. Detail kontrak endpoint dan aturan keamanannya tersedia di `pwa/README.md`.

## Cara menambah modul

1. Tambahkan item navigasi di `SIMS.menuGroups` (`assets/js/data.js`).
2. Untuk modul tabel CRUD standar, tambahkan konfigurasi ke `SIMS.entityConfigs` dan mock data ke `SIMS.rows`.
3. Untuk modul kustom, tambahkan fungsi template di `assets/js/templates.js` dan daftarkan di map `render()` pada `assets/js/app.js`.
4. Ganti mock state pada `Alpine.store('records')` dengan response API Laravel.

## Petunjuk Laravel

Baca `laravel/README.md` untuk instalasi, Vite build production, Blade, routes, dan contoh API controller. Tailwind Play CDN hanya untuk prototyping sesuai dokumentasi resmi Tailwind; jangan dipakai di production.
