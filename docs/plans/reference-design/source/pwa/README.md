# SIMS Mobile PWA

Template PWA mobile-first untuk siswa, guru, dan pegawai sekolah. Jalankan dari HTTP/HTTPS; service worker dan kamera tidak bekerja penuh melalui `file://`.

```powershell
python -m http.server 8000
```

Buka `http://localhost:8000/pwa/`. Peran tidak dipilih pada halaman login: template mencocokkan kredensial lalu membuka akses siswa, guru, atau pegawai secara otomatis.

## Akun simulasi

| Akses | Username | Password |
|---|---|---|
| Siswa | `nadia` atau `240017` | `siswa123` |
| Guru | `rina` atau `19880412` | `guru123` |
| Pegawai | `operator` atau `19910523` | `admin123` |

Kode OTP simulasi adalah `123456`. Nomor WhatsApp berakhiran `01` disimulasikan sebagai guru, `02` sebagai pegawai, dan nomor lain sebagai siswa. Kode kartu dengan awalan `T-` dikenali sebagai guru, `P-` sebagai pegawai, sedangkan kode lain sebagai siswa.

Navigasi menampilkan skeleton saat perpindahan halaman. Operasi autentikasi, pemuatan profil, pembayaran, pengaturan, dan logout menggunakan simulasi proses database bertahap. Dashboard siswa mencakup ringkasan performa, presensi, jadwal, tugas, grafik nilai interaktif, tagihan, dan riwayat pembayaran.

## Endpoint production yang disarankan

- `POST /api/auth/otp/request` — normalisasi nomor, rate limit, lalu kirim template WhatsApp resmi.
- `POST /api/auth/otp/verify` — verifikasi OTP yang di-hash, sekali pakai, dan memiliki masa berlaku.
- `POST /api/auth/barcode/verify` — verifikasi token QR/barcode bertanda tangan dan berumur pendek.
- `POST /api/auth/login` — username/password dengan rate limit dan rotasi session.
- `POST /api/auth/logout` — cabut session/token perangkat.
- `GET /api/mobile/me` — profil, peran, permission, dan menu mobile.

Jangan menyimpan OTP, password, atau access token sensitif di `localStorage`. Gunakan cookie session `HttpOnly`, `Secure`, `SameSite`, CSRF protection, Laravel policies, audit log, device/session management, serta rate limiting per akun, IP, dan nomor WhatsApp. Integrasi WhatsApp membutuhkan WhatsApp Business Platform atau BSP resmi dan template pesan yang disetujui.

## Integrasi Laravel

Salin folder ini ke `public/pwa` untuk prototipe cepat, atau pindahkan CSS/JS ke Vite dan jadikan shell sebagai Blade view. Ganti `accounts`, deteksi nomor/kartu, dan fungsi `databaseOperation()` pada `app.js` dengan request ke endpoint autentikasi serta data Laravel.
