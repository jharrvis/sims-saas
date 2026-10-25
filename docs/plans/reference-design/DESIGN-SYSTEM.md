# SIMS PWA Design System Contract

Dokumen ini adalah kontrak implementasi. Nilai detail tetap bersumber dari `source/pwa/styles.css` dan `source/assets/css/design.css`.

## Arah Visual

- Modern, profesional, bersih, dan ramah untuk lingkungan sekolah.
- Mobile-first tanpa menampilkan aplikasi sebagai kanvas ponsel sempit pada tablet/desktop.
- Hierarki dibangun dengan ukuran, spacing, weight, dan surface; bukan warna saja.
- Gunakan ikon SVG dengan stroke konsisten. Jangan menggunakan emoji sebagai ikon struktural.

## Token Inti

| Token | Nilai referensi | Penggunaan |
|---|---:|---|
| Primary | `#4f46e5` | CTA, active state, link utama |
| Primary dark | `#3730a3` | state gelap/teks aksen |
| Primary soft | `#eef2ff` | background aksen |
| Ink | `#172033` | teks utama |
| Muted | `#68738a` | teks sekunder |
| Surface | `#ffffff` | card/form |
| Canvas | `#f5f7fb` | background aplikasi |
| Line | `#e8ebf2` | border/divider |
| Success | `#16805b` | status berhasil/online |
| Warning | `#b56207` | status perlu perhatian |
| Danger | `#d14343` | error/destructive |
| Radius utama | `20px` | card/surface besar |
| Shadow kecil | `0 2px 8px rgba(27,39,74,.06)` | card biasa |
| Shadow medium | `0 12px 32px rgba(27,39,74,.11)` | card fokus/login |

Gunakan semantic CSS variables atau theme tokens. Hindari menyalin hex berulang kali ke setiap Blade component.

## Typography dan Spacing

- Font: `Inter`, `ui-sans-serif`, system font fallback.
- Body mobile minimal 16px untuk field/input utama dan keterbacaan.
- Heading memakai weight 700–850 dan line-height rapat.
- Body memakai line-height 1.5–1.7.
- Spacing mengikuti ritme 4/8px: 4, 8, 12, 16, 20, 24, 32, 48, 64.
- Panjang baris teks desktop ideal 60–75 karakter.

## Layout Responsif

### Login

- Mobile dan tablet: satu kolom terpusat.
- Desktop mulai 900px: grid dua kolom `minmax(0, 1fr) minmax(380px, 440px)`.
- Gap desktop adaptif 64–128px.
- Lebar maksimum keseluruhan 1120px.
- Jangan menumpuk padding berbasis viewport di dalam container yang sudah dibatasi `max-width`.

### App Shell

- Lebar shell 100% dari viewport.
- Mobile menggunakan bottom navigation maksimal lima tujuan utama.
- Tablet/dashboard boleh memakai grid adaptif sesuai kepadatan konten.
- Desktop dapat menggunakan sidebar/navigation rail; konten tetap memiliki readable max-width.
- Fixed/sticky navigation harus mempunyai content inset dan safe-area yang sesuai.

## Komponen

- Input dan button: tinggi minimum 44px; referensi utama 52px.
- Card: border halus, radius konsisten, shadow tidak berlebihan.
- Satu primary CTA per konteks/form.
- Status selalu memakai teks/ikon selain warna.
- Loading tombol hanya memblokir target yang sedang diproses.
- Empty/error state harus memberikan penjelasan dan recovery action.

## Motion dan Loading

- Micro-interaction 150–300ms.
- Animasi memakai `transform` dan `opacity`, bukan width/height/top/left.
- Skeleton digunakan untuk pemuatan halaman/konten lebih dari sekitar 300ms.
- Loading database memiliki tahapan yang spesifik, tetapi delay buatan hanya boleh aktif pada local/demo.
- Semua pending state harus selesai pada success, validation error, exception, timeout, cancel, atau offline.
- Hormati `prefers-reduced-motion`.

## Grafik

- Tren waktu: line/area chart.
- Perbandingan kategori: bar chart.
- Proporsi: donut hanya untuk maksimal lima kategori.
- Sediakan tooltip/tap detail, legenda, label unit, dan ringkasan teks/tabel.
- Jangan mengandalkan pasangan merah/hijau saja.
- Grafik harus reflow pada mobile dan tidak menyebabkan horizontal scroll.

## Aksesibilitas

- Kontras teks normal minimal 4.5:1.
- Focus ring tidak boleh dihapus.
- Tab order mengikuti urutan visual.
- Semua input memiliki label yang terlihat.
- Icon-only button memiliki accessible name.
- Target sentuh minimal 44×44px.
- Route change memindahkan fokus ke konten utama secara wajar.
- Gunakan `aria-live` untuk status proses tanpa mencuri fokus.

## Hal yang Tidak Boleh Disalin ke Production

- Kredensial dan OTP demo.
- Role detection dari suffix nomor/kode pada frontend.
- Session/token sensitif di `localStorage`.
- Artificial database delay tanpa environment guard.
- Tailwind Play CDN.
- Data akademik/keuangan mock sebagai fallback production.
