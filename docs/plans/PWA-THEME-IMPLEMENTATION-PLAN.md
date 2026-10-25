# SIMS SaaS — PWA Theme Implementation Plan

## 0. Referensi Desain Resmi

Implementasi dalam plan ini wajib merujuk pada bundle berikut:

- `docs/plans/reference-design/README.md` — indeks dan aturan penggunaan.
- `docs/plans/reference-design/DESIGN-SYSTEM.md` — kontrak visual dan UX.
- `docs/plans/reference-design/source/pwa/` — template PWA/login terbaru.
- `docs/plans/reference-design/source/assets/` — token dan template dashboard desktop.
- `docs/plans/reference-design/source/laravel/` — contoh pemetaan ke Blade/Vite.
- `docs/plans/reference-design/previews/` — baseline visual yang sudah diverifikasi.

Bundle tersebut adalah source of truth untuk tampilan dan interaksi. Arsitektur, keamanan, autentikasi, multi-tenancy, permission, validasi, audit log, dan data tetap mengikuti implementasi production pada project ini. Mock data dan JavaScript demo tidak boleh dipindahkan menjadi business logic production.

## 1. Tujuan

Mengimplementasikan pengalaman aplikasi sekolah yang modern, responsif, dan installable sebagai PWA untuk siswa, orang tua, guru, serta pegawai sekolah. Implementasi harus mempertahankan arsitektur multi-tenant yang sudah ada, tidak menduplikasi business logic, dan menyediakan feedback loading yang konsisten pada navigasi maupun proses database.

Target utama:

- Tampilan mobile-first yang tetap optimal di tablet dan desktop.
- Login tunggal tanpa pilihan peran; peran dan tenant dideteksi dari kredensial.
- Metode login username/password, nomor WhatsApp + OTP, dan QR/barcode.
- Loading state untuk perpindahan halaman dan seluruh operasi async/database.
- Dashboard siswa lengkap: jadwal, tugas, nilai, kehadiran, dan keuangan.
- Dashboard berbasis peran untuk guru dan pegawai.
- Animasi dan grafik interaktif yang ringan serta aksesibel.
- PWA yang dapat dipasang, aman, mempunyai fallback offline, dan mudah diperbarui.

## 2. Ringkasan Arsitektur Saat Ini

Hasil inspeksi awal menunjukkan project menggunakan:

- Laravel 13 dan PHP 8.3.
- Livewire 4.4 untuk antarmuka reaktif.
- Tailwind CSS 4 dan Vite untuk asset pipeline.
- Arsitektur multi-tenancy dengan konteks sekolah/tenant.
- Halaman PWA dan komponen Blade/Livewire yang sudah mulai dipisahkan dari portal desktop.

Fondasi tersebut cocok untuk implementasi tema tanpa migrasi framework. Strategi utama adalah memperkuat design system, membuat layout PWA bersama, dan memindahkan pengambilan data dashboard ke service/query layer.

## 3. Temuan dan Gap Prioritas

### 3.1 Stabilitas asset

- Referensi stylesheet PWA belum sepenuhnya konsisten dengan input Vite.
- Terdapat referensi JavaScript QR/barcode yang perlu dipastikan benar-benar tersedia dan dimuat hanya pada halaman terkait.
- Asset PWA perlu gagal secara aman agar halaman tidak berhenti pada loading tanpa akhir.

### 3.2 Responsivitas layout

- Lebar konten dibatasi seperti layar ponsel walaupun viewport berupa tablet/desktop.
- Container, grid, spacing, dan bottom navigation belum memiliki breakpoint yang konsisten.
- Safe-area iOS/Android, keyboard virtual, dan orientasi landscape perlu ditangani.

### 3.3 Loading dan navigasi

- Belum ada satu loading manager yang menangani navigasi, request Livewire, submit form, dan error timeout.
- Overlay global berisiko tidak pernah ditutup bila event selesai/error tidak tertangani.
- Belum tersedia skeleton per bagian untuk mempertahankan layout selama data dimuat.

### 3.4 Autentikasi dan resolusi peran

- Pengguna tidak seharusnya memilih siswa/guru/pegawai secara manual.
- Sistem perlu memetakan identitas login ke user, tenant, role, dan halaman tujuan.
- WhatsApp OTP dan QR membutuhkan aturan expiry, rate limit, single-use token, serta audit trail.

### 3.5 Kelengkapan dashboard siswa

- Informasi akademik, nilai, presensi, tugas, jadwal, dan keuangan perlu disatukan melalui kontrak data yang jelas.
- Agregasi statistik tidak boleh dilakukan berulang kali langsung di view/komponen.
- Grafik perlu menyediakan data ringkas dan fallback teks/tabel.

### 3.6 Dashboard guru dan pegawai

- Perlu konfigurasi menu/capability berbasis role dan permission, bukan layout terpisah untuk setiap jabatan.
- Guru membutuhkan jadwal mengajar, presensi, penilaian, tugas, dan ringkasan kelas.
- Pegawai membutuhkan quick actions dan modul sesuai izin, misalnya TU, keuangan, perpustakaan, atau operator.

## 4. Prinsip Implementasi

1. **Mobile-first, fluid on larger screens** — lebar aplikasi mengikuti viewport; batas maksimum hanya diterapkan pada bagian konten yang membutuhkan keterbacaan.
2. **Satu design system** — token warna, tipografi, radius, shadow, spacing, motion, dan states digunakan lintas role.
3. **Progressive enhancement** — fungsi inti tetap dapat digunakan jika animasi, kamera, atau service worker tidak tersedia.
4. **Server-authoritative** — role, tenant, saldo, nilai, dan izin ditentukan server; klien hanya menampilkan.
5. **Accessible by default** — target sentuh minimum 44px, focus state, kontras memadai, semantic markup, dan dukungan `prefers-reduced-motion`.
6. **Loading harus memiliki jalan keluar** — setiap loading selesai pada success, validation error, exception, timeout, atau navigasi dibatalkan.

## 5. Tahapan Implementasi

## Fase 0 — Baseline dan Pengamanan

Tujuan: mendapatkan baseline yang dapat diuji sebelum perubahan visual besar.

- Jalankan test suite, build asset, dan cek route utama PWA.
- Catat screenshot pada viewport 360, 390, 768, 1024, dan desktop.
- Identifikasi komponen/route PWA aktif serta data dummy yang masih digunakan.
- Tambahkan feature flag/config untuk rollout PWA baru bila aplikasi telah digunakan di produksi.

Output:

- Baseline test dan build terdokumentasi.
- Matriks route × role × permission.
- Daftar regresi yang sudah ada sebelum implementasi.

## Fase 1 — Perbaikan Asset dan Infinite Loading

Tujuan: aplikasi selalu dapat masuk ke state siap atau error yang jelas.

- Sinkronkan seluruh entry CSS/JS PWA pada `vite.config.js` dan pemanggilan `@vite`.
- Pastikan modul QR/barcode tersedia dan lazy-loaded.
- Buat loading manager dengan counter request, timeout guard, dan cleanup event.
- Tangani event navigasi Livewire: start, success, error, offline, dan cancel.
- Sediakan error state dengan tombol coba lagi.
- Hindari overlay blocking saat halaman sudah memiliki konten yang dapat digunakan.

Acceptance criteria:

- Tidak ada loading global yang bertahan setelah request selesai/gagal.
- Missing asset menghasilkan error terukur, bukan halaman kosong.
- Navigasi back/forward dan koneksi lambat tetap dapat dipulihkan.

## Fase 2 — Design System dan Layout Responsif

Tujuan: membangun fondasi UI modern yang konsisten.

- Definisikan CSS variables/token: brand, surface, text, semantic colors, spacing, radius, elevation, dan motion.
- Buat app shell bersama: top bar, content area, bottom navigation/mobile rail, toast, modal, dan offline banner.
- Hilangkan fixed mobile canvas; gunakan lebar fluid 100%.
- Terapkan breakpoint:
  - Mobile `< 600px`: login satu kolom penuh dengan gutter 18px dan bottom navigation untuk app shell.
  - Tablet `600–899px`: login tetap satu kolom terpusat dengan lebar maksimum 620px; dashboard dapat memakai grid adaptif sesuai konten.
  - Desktop `>= 900px`: login dua kolom dengan area pengantar di kiri dan form 380–440px di kanan; dashboard dapat memakai sidebar/navigation rail.
  - Wide desktop `>= 1180px`: container login maksimum 1120px dan tidak menerima kembali padding viewport dari aturan `.screen` global.
- Tambahkan safe-area inset dan padding bawah dinamis agar konten tidak tertutup navigasi.
- Buat komponen UI reusable: card, stat, badge, empty state, skeleton, button, input, list item, dan section header.
- Port struktur `auth-intro` dan `auth-panel` dari `reference-design/source/pwa/index.html`; jangan menyelesaikan responsivitas dengan `zoom` atau `transform: scale()`.

Acceptance criteria:

- Tidak ada area abu-abu kosong akibat pembatas lebar layout pada tablet.
- Tidak ada horizontal overflow pada viewport 320–1440px.
- Elemen navigasi aktif, hover, focus, disabled, loading, dan error konsisten.
- Login sesuai baseline `reference-design/previews/login-mobile-fixed.png`, `login-tablet-fixed.png`, dan `login-desktop-fixed.png`.

## Fase 3 — Loading State dan Simulasi Proses Database

Tujuan: memberi feedback cepat dan realistis pada setiap interaksi.

- Navigasi halaman: progress bar tipis + skeleton konten.
- Submit/save/delete: loading pada tombol yang dipicu, field terkunci seperlunya, dan label proses spesifik.
- Tabel/list: skeleton rows, filter debounce, dan empty/error state.
- Gunakan `wire:loading`, `wire:target`, event navigasi Livewire, dan request correlation bila diperlukan.
- Buat mode simulasi hanya untuk local/demo melalui config environment, misalnya `PWA_SIMULATED_LATENCY_MS`.
- Jangan menambahkan delay buatan di production.
- Cegah double submit dan tampilkan toast hasil operasi.

Acceptance criteria:

- Setiap operasi database mempunyai pending, success, validation, dan error state.
- Simulasi delay dapat dinyalakan/dimatikan tanpa mengubah source code.
- Tombol lain tidak ikut loading jika bukan target request.

## Fase 4 — Login Tunggal dan Deteksi Otomatis Pengguna

Tujuan: menghapus pilihan jenis pengguna dari layar login.

Alur utama:

1. Pengguna memilih metode login: kredensial, WhatsApp OTP, atau scan QR.
2. Backend memvalidasi identitas dan tenant.
3. Backend mengambil role/permission aktif.
4. Jika satu konteks ditemukan, pengguna langsung diarahkan ke dashboard yang sesuai.
5. Jika satu identitas sah memiliki beberapa tenant/konteks, tampilkan context switcher setelah autentikasi, bukan pilihan role sebelum login.

Pekerjaan backend:

- Buat `LoginIdentityResolver` untuk normalisasi username/nomor WA/QR token.
- Buat `PostLoginRedirector` berdasarkan role dan capability.
- OTP: expiry singkat, hash token, attempt limit, resend cooldown, single use, dan audit log.
- QR: signed opaque token, expiry, nonce, revoke, single use, dan verifikasi tenant.
- Rate limiting per IP dan per identity.
- Pesan error tidak boleh membocorkan apakah akun/nomor tertentu terdaftar.

Acceptance criteria:

- Tidak ada selector siswa/guru/pegawai pada login.
- Role tidak pernah dipercaya dari input frontend.
- Semua metode login berakhir pada resolver dan redirector yang sama.

## Fase 5 — Data Layer Dashboard Siswa

Tujuan: menyediakan satu sumber data yang efisien untuk seluruh widget siswa.

- Buat `StudentDashboardService` atau query objects terpisah.
- Contract data minimal:
  - profil dan kelas aktif;
  - jadwal hari ini/berikutnya;
  - tugas prioritas dan status;
  - rata-rata nilai dan tren per semester/mapel;
  - kehadiran dan rincian status;
  - tagihan aktif, jatuh tempo, pembayaran terakhir, dan riwayat;
  - pengumuman/notifikasi.
- Gunakan eager loading, select kolom yang diperlukan, pagination, agregasi SQL, dan cache per tenant/siswa bila aman.
- Invalidate cache ketika nilai, presensi, tagihan, atau pembayaran berubah.
- Semua query wajib terikat tenant dan siswa yang sedang terautentikasi.

Acceptance criteria:

- Dashboard tidak melakukan query dari Blade view.
- Tidak ada N+1 query pada widget utama.
- Data kosong mempunyai empty state yang benar, bukan nilai palsu.

## Fase 6 — Akademik Siswa dan Grafik Interaktif

Tujuan: memberikan informasi akademik lengkap tetapi mudah dipahami.

- Ringkasan semester: rata-rata, ranking bila kebijakan sekolah mengizinkan, kehadiran, dan perubahan dari periode sebelumnya.
- Grafik tren nilai per semester/mapel dengan tooltip, filter, dan legenda.
- Daftar nilai: mapel, komponen, bobot, nilai, predikat, dan catatan.
- Kalender/jadwal, tugas, detail tugas, status pengumpulan, dan deadline.
- Detail presensi bulanan.
- Lazy-render grafik setelah container terlihat.
- Sediakan ringkasan teks/tabel sebagai fallback aksesibilitas.
- Hormati `prefers-reduced-motion`.

Acceptance criteria:

- Grafik dapat digunakan dengan sentuhan dan keyboard.
- Filter grafik tidak memuat ulang seluruh halaman.
- Animasi tidak menghambat interaksi atau menyebabkan layout shift.

## Fase 7 — Keuangan Siswa

Tujuan: menjadikan data tagihan dan pembayaran transparan serta mudah diakses.

- Kartu total tagihan aktif, sudah dibayar, menunggu verifikasi, dan jatuh tempo terdekat.
- Daftar tagihan dengan status, periode, nominal, diskon/denda bila ada.
- Detail tagihan dan breakdown komponen.
- Riwayat pembayaran serta bukti pembayaran yang dapat dilihat/diunduh sesuai izin.
- Filter periode/status dan pagination/infinite list yang aman.
- CTA pembayaran hanya ditampilkan jika payment workflow benar-benar tersedia.
- Nominal dan status selalu berasal dari backend, bukan kalkulasi frontend.

Acceptance criteria:

- Total ringkasan cocok dengan detail transaksi.
- Data keuangan tidak dapat diakses lintas siswa/tenant.
- State loading, kosong, gagal, dan retry tersedia.

## Fase 8 — Orang Tua, Guru, dan Pegawai

Tujuan: memakai app shell yang sama dengan modul sesuai capability.

Orang tua/wali:

- Child switcher bila memiliki lebih dari satu siswa.
- Ringkasan akademik, presensi, tagihan, dan pengumuman per anak.

Guru:

- Jadwal mengajar hari ini.
- Kelas aktif dan shortcut presensi.
- Tugas/penilaian yang perlu diperiksa.
- Input nilai dengan autosave/explicit save yang jelas dan anti-double-submit.
- Ringkasan performa kelas sesuai permission.

Pegawai:

- Dashboard berbasis permission/capability.
- Quick actions untuk modul TU, keuangan, perpustakaan, operator, atau modul lain yang tersedia.
- Jangan hard-code menu berdasarkan nama jabatan; gunakan permission map.

Acceptance criteria:

- Pengguna hanya melihat dan membuka menu yang diizinkan.
- Pergantian role/konteks memperbarui menu dan data secara aman.
- Layout tetap sama; modul yang berubah mengikuti capability.

## Fase 9 — PWA, Offline, dan Installability

Tujuan: menjadikan aplikasi dapat dipasang tanpa mengorbankan keamanan data.

- Lengkapi manifest: `name`, `short_name`, icons 192/512, maskable icon, theme/background color, start URL, scope, dan display mode.
- Service worker menggunakan strategi:
  - cache-first untuk asset fingerprinted;
  - network-first untuk halaman shell yang aman;
  - network-only untuk autentikasi dan data sensitif/transaksional.
- Offline fallback hanya menampilkan shell, informasi terakhir yang aman, dan status koneksi.
- Jangan menyimpan OTP, token QR, respons keuangan sensitif, atau data pribadi di cache publik.
- Tambahkan update prompt ketika service worker baru siap.
- Tambahkan install prompt yang tidak mengganggu.

Acceptance criteria:

- Lulus pemeriksaan installability browser.
- Login dan mutasi data tidak pernah dilayani dari cache.
- Setelah deploy versi baru, pengguna dapat memperbarui aplikasi tanpa infinite refresh.

## Fase 10 — Quality, Performance, dan Rollout

- Feature tests untuk resolver login, redirect per role, tenant isolation, OTP, QR, dan akses data siswa.
- Livewire/component tests untuk loading target, validation, pagination, filter, dan error state.
- Browser tests untuk login, navigasi, save, offline/online, install prompt, dan pergantian konteks.
- Visual regression pada viewport mobile, tablet, dan desktop.
- Audit query count pada dashboard.
- Audit Lighthouse untuk performance, accessibility, best practices, dan PWA.
- Rollout bertahap per tenant/role melalui feature flag.
- Logging client error dan metrik kegagalan request untuk mendeteksi infinite loading.

## 6. Struktur Komponen yang Disarankan

```text
resources/
├── css/
│   └── pwa/
│       ├── tokens.css
│       ├── base.css
│       ├── components.css
│       └── utilities.css
├── js/
│   └── pwa/
│       ├── bootstrap.js
│       ├── loading-manager.js
│       ├── charts.js
│       ├── qr-scanner.js
│       └── service-worker-registration.js
└── views/
    ├── components/pwa/
    │   ├── app-shell.blade.php
    │   ├── app-header.blade.php
    │   ├── bottom-navigation.blade.php
    │   ├── loading-overlay.blade.php
    │   ├── skeleton.blade.php
    │   ├── stat-card.blade.php
    │   ├── chart-card.blade.php
    │   ├── empty-state.blade.php
    │   └── offline-banner.blade.php
    └── pwa/
        ├── auth/
        ├── student/
        ├── parent/
        ├── teacher/
        └── staff/
```

Nama dan lokasi final harus mengikuti struktur aktual project setelah refactor minimal; struktur di atas adalah arah, bukan kewajiban memindahkan semua file sekaligus.

## 7. File/Area yang Kemungkinan Terdampak

- `vite.config.js` dan entry asset PWA.
- Layout Blade PWA dan komponen navigasi.
- CSS/JavaScript PWA.
- Komponen Livewire untuk login dan dashboard.
- Route/middleware autentikasi PWA.
- Service/query layer untuk dashboard siswa.
- Model scopes/policies terkait tenant dan role.
- Manifest, icons, service worker, dan registration script.
- Test feature, component, browser, serta dokumentasi environment.

Sebelum perubahan, nama file aktual wajib dikonfirmasi kembali agar tidak membuat paralel implementation yang tidak diperlukan.

## 8. Strategi Pengujian

### Otomatis

- Unit: normalisasi nomor WA, resolver identitas, redirect role, formatter data grafik.
- Feature: login tiga metode, tenant isolation, permission, akademik, dan keuangan.
- Livewire: loading target, form validation, retry, pagination, dan filter.
- Browser/E2E: happy path serta kegagalan jaringan/database.

### Manual

- Viewport: 320×568, 360×800, 390×844, 768×1024, 1024×1366, dan 1440px.
- Baseline wajib: 390×844, 768×1024, dan 1920×1080 dibandingkan dengan `docs/plans/reference-design/previews/`.
- Perangkat input: touch, mouse, keyboard.
- Kondisi jaringan: fast, slow 3G, offline, reconnect.
- Mode: standalone PWA, browser tab, portrait, landscape.
- Role: siswa, wali, guru, dan setiap kelompok permission pegawai.

## 9. Urutan Pull Request yang Disarankan

1. **PR-1: Asset + loading hotfix** — perbaiki infinite loading dan asset yang hilang.
2. **PR-2: Design tokens + responsive app shell** — fondasi visual dan lebar tablet/desktop.
3. **PR-3: Unified login resolver** — deteksi role otomatis dan redirect.
4. **PR-4: Student dashboard data service** — kontrak data, optimasi query, dan cache.
5. **PR-5: Student academics + charts** — nilai, presensi, jadwal, tugas.
6. **PR-6: Student finance** — tagihan dan riwayat pembayaran.
7. **PR-7: Parent/teacher/staff dashboards** — capability-driven navigation.
8. **PR-8: PWA offline/update hardening** — manifest, service worker, install/update flow.
9. **PR-9: Performance, accessibility, and rollout** — audit, observability, feature flag.

Setiap PR harus dapat dirilis mandiri, mempunyai test yang relevan, dan tidak mencampur refactor besar yang tidak terkait.

## 10. Definition of Done

Implementasi dianggap selesai jika:

- UI memenuhi lebar viewport pada mobile, tablet, dan desktop tanpa overflow.
- Login tidak meminta pengguna memilih role dan redirect ditentukan server.
- Username/password, WhatsApp OTP, dan QR mempunyai aturan keamanan dan error state yang konsisten.
- Setiap navigasi dan mutasi database memiliki loading, success, validation, error, timeout, dan retry yang tepat.
- Siswa dapat melihat jadwal, tugas, nilai, presensi, tagihan, dan riwayat pembayaran sesuai data aktual.
- Guru dan pegawai melihat dashboard serta menu sesuai permission.
- Grafik interaktif responsif, aksesibel, dan tetap informatif tanpa animasi.
- PWA installable, mempunyai offline fallback aman, dan update flow yang tidak macet.
- Test otomatis utama lulus dan tidak terdapat N+1 query signifikan pada dashboard.
- Tidak terjadi kebocoran data lintas tenant atau lintas siswa.

## 11. Prioritas Eksekusi Terdekat

Urutan paling aman untuk mulai dikerjakan:

1. Selesaikan penyebab infinite loading dan verifikasi asset pipeline.
2. Implementasikan app shell responsif dan design tokens.
3. Tambahkan loading manager serta skeleton reusable.
4. Implementasikan login resolver otomatis.
5. Bangun data service dashboard siswa sebelum memperluas tampilan akademik/keuangan.

Urutan ini menutup bug yang menghambat penggunaan terlebih dahulu, kemudian menyediakan fondasi agar fitur berikutnya tidak menghasilkan duplikasi dan inkonsistensi.
