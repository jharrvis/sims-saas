# Panduan Deploy Production — HestiaCP

> Keputusan: deploy di HestiaCP; secret diisi manual saat deploy (12 Okt 2026).

## 1. Prasyarat Server

- HestiaCP terinstal, dengan **PHP 8.3** tersedia (Server → Configure → PHP).
- Ekstensi PHP: `mbstring`, `xml`, `sqlite3`, `curl`, `zip`, `bcmath`, `intl`, `mysqlnd`.
- Composer terinstal untuk user hosting.
- MySQL/MariaDB.

## 2. Siapkan Domain & Database (Panel HestiaCP)

1. **Web → Add Web Domain** — mis. `simsapp.my.id`.
   - PHP version: **8.3**.
   - Aktifkan **SSL** (Let's Encrypt) setelah DNS mengarah ke server.
   - **Custom Document Root**: arahkan ke folder `public` Laravel —
     di HestiaCP: Web → Edit Domain → Advanced options → Custom document root,
     isi `app/public` (relatif terhadap folder domain).
   - Catatan: folder tujuan harus sudah ada saat di-set (HestiaCP menolak bila
     tidak ada) — jadi lakukan langkah ini **setelah clone di langkah 3**.
2. **DB → Add Database** — catat nama DB, user, password.

## 3. Deploy Kode

Via SSH sebagai user HestiaCP:

```bash
cd ~/web/simsapp.my.id
git clone https://github.com/jharrvis/sims-saas.git app
cd app
```

> Repo ini **private** — clone HTTPS polos akan ditolak GitHub. Pakai salah satu:
> - Fine-grained PAT (hanya permission Contents: Read di repo ini):
>   `git clone https://<TOKEN>@github.com/jharrvis/sims-saas.git app`
> - atau SSH deploy key (Settings repo → Deploy keys), lalu
>   `git clone git@github.com:jharrvis/sims-saas.git app`.
> Jangan taruh token di riwayat shell bersama (`export` + variabel bila perlu).

> **Layout HestiaCP (PENTING):** PHP-FPM HestiaCP membatasi `open_basedir`
> ke docroot (`public_html/public`), sehingga `public/index.php` standar
> tidak bisa me-require `../vendor` & `../bootstrap` (500 kosong, tanpa log).
> Layout yang dipakai: **kode aplikasi di `private/app/`**, docroot tetap
> `public_html/public`. Aplikasi mendukung ini via env var:
> - `APP_BASE_PATH` → `/home/simsapp/web/simsapp.my.id/private/app`
>   (dibaca `public/index.php`; harus environment variable *asli* dari
>   web server — `.env` belum ter-load saat itu — jadi diset via `SetEnv`
>   di `public/.htaccess`, bukan di `.env`)
> - `PUBLIC_PATH` → `/home/simsapp/web/simsapp.my.id/public_html/public`
>   (dibaca `bootstrap/app.php` via `usePublicPath()`)
>
> Tambahkan di akhir `public_html/public/.htaccess` (server-only):
> ```apache
> SetEnv APP_BASE_PATH /home/simsapp/web/simsapp.my.id/private/app
> SetEnv PUBLIC_PATH /home/simsapp/web/simsapp.my.id/public_html/public
> ```
> Cron memakai path baru: `cd /home/simsapp/web/simsapp.my.id/private/app`.

> **Penting — file yang belum ada di remote GitHub** (keterbatasan API):
> upload manual via web GitHub sebelum clone:
> - `composer.lock` (klik Add file → Upload files di root repo)
> - `.github/workflows/ci.yml`
>
> Tanpa `composer.lock`, `composer install` akan me-resolve dependency baru
> (berisiko versi berbeda dari yang diuji).

## 4. Konfigurasi `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Isi (secret diberikan saat deploy):

| Key | Keterangan |
|---|---|
| `APP_URL` | `https://simsapp.my.id` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | dari panel HestiaCP |
| `WA_DRIVER` | `fonnte` |
| `FONNTE_TOKEN` | token perangkat Fonnte |
| `MIDTRANS_MERCHANT_ID` / `MIDTRANS_CLIENT_KEY` / `MIDTRANS_SERVER_KEY` | dashboard Midtrans |
| `MIDTRANS_IS_PRODUCTION` | `true` (setelah uji sandbox) |
| `PUSH_VAPID_PUBLIC_KEY` / `PUSH_VAPID_PRIVATE_KEY` | generate baru untuk production |
| `PUSH_VAPID_SUBJECT` | `mailto:ops@simsapp.my.id` |
| `QUEUE_CONNECTION` | `database` |

## 5. Instalasi & Migrasi

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Pastikan writable: `storage/`, `bootstrap/cache/` (owner = user HestiaCP).

Buat akun operator platform pertama:

```bash
php artisan platform:make-admin 62812xxxxxxx --name="Operator"
```

## 6. Cron Jobs (Panel HestiaCP → User → Cron Jobs)

Jalankan setiap menit (ganti `user` dan domain sesuai):

```
* * * * * cd /home/user/web/simsapp.my.id/app && php artisan schedule:run >> /dev/null 2>&1
* * * * * cd /home/user/web/simsapp.my.id/app && php artisan queue:work --stop-when-empty --max-time=50 --tries=3 >> /dev/null 2>&1
```

- Baris 1: scheduler Laravel (broadcast terjadwal, dunning langganan harian).
- Baris 2: queue worker tanpa supervisor — HestiaCP tidak menyediakan
  supervisor, jadi worker dijalankan per menit dan berhenti saat antrean kosong.
  Cukup untuk tahap awal; pindah ke supervisor/daemon bila volume naik.

## 7. Midtrans

1. Di dashboard Midtrans → Settings → Notification URL, isi:
   `https://simsapp.my.id/api/v1/webhooks/midtrans`
2. Uji dengan **sandbox** dulu (`MIDTRANS_IS_PRODUCTION=false`):
   daftar trial → terbitkan tagihan → bayar via Snap → pastikan status Lunas.
3. Setelah lolos, aktifkan production.

## 8. Verifikasi Production

- [ ] `https://simsapp.my.id/daftar` — pendaftaran trial berhasil
- [ ] Login WA OTP — kode benar-benar terkirim via Fonnte
- [ ] `https://simsapp.my.id/platform` — panel operator (akun `platform:make-admin`)
- [ ] PWA: subscribe push di Profil → kirim pengumuman → notifikasi masuk
- [ ] Bayar tagihan Midtrans sandbox → webhook → status Lunas
- [ ] `php artisan schedule:list` — job harian terdaftar
- [ ] Log: `storage/logs/laravel.log` bersih dari error

## 9. Troubleshooting

| Gejala | Periksa |
|---|---|
| 500 saat buka situs | `storage/logs/laravel.log`; permission `storage/` & `bootstrap/cache/` |
| OTP tidak terkirim | `WA_DRIVER=fonnte`, `FONNTE_TOKEN` terisi; cek log `[WA-OTP]`/`fonnte:` |
| Job antrean menumpuk | cron `queue:work` aktif; `QUEUE_CONNECTION=database`; tabel `jobs` ada |
| Dunning tidak jalan | cron `schedule:run` tiap menit; `php artisan schedule:list` |
| Webhook Midtrans 403 | signature cocok dengan `MIDTRANS_SERVER_KEY` yang benar |
| Aset 404 (CSS/JS) | jalankan `npm run build` lalu upload folder `public/build`, atau build di server bila Node tersedia |
