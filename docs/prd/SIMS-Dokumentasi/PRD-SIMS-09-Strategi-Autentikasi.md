# Dok 09 — Strategi Autentikasi & UX Login

| Atribut | Keterangan |
|---|---|
| Versi | 1.0 |
| Tanggal | 9 Oktober 2026 |
| Mengacu | PRD §5 (peran), §7.9 (pengelolaan pengguna), §7.13 (PWA), §17 (keamanan); Dok 03 (API); Dok 08 gap G-01/G-02 |
| Status | Draft untuk review |

Dokumen ini menjawab pertanyaan paling krusial untuk adopsi di Indonesia: **bagaimana semua peran bisa masuk dengan mulus dan minim kendala** — dari orang tua yang hanya punya HP Android + WhatsApp, siswa SD tanpa HP, sampai admin platform.

---

## 1. Prinsip Desain

1. **Tanpa password bila memungkinkan.** Password adalah sumber utama kendala (lupa, typo di HP kecil, ditulis di kertas). Prioritaskan metode yang tidak menuntut pengguna mengingat apa pun.
2. **Ikuti alat yang sudah dimiliki pengguna.** Di Indonesia itu = nomor WhatsApp, bukan email. Jangan memaksa alur berbasis email untuk orang tua/siswa.
3. **Phishing-resistant untuk akses sensitif.** Data anak + data keuangan sekolah menuntut metode yang tidak bisa di-phishing (passkey) untuk peran berisiko tinggi.
4. **Inklusif untuk anak & non-teknis.** Siswa SD/SMP tanpa HP tetap terlayani (profil anak di HP ortu, kartu QR di sekolah). Bahasa sederhana, langkah maksimal 3 ketuk.
5. **Perangkat bersama adalah normal.** HP dipakai bergantian dalam keluarga; komputer TU dipakai bergantian antar staf. Desain sesi dan logout harus mengasumsikan ini.
6. **Degradasi anggun (graceful fallback).** Setiap metode utama punya cadangan yang tetap aman — tidak ada jalan buntu.

---

## 2. Matriks Metode Login per Peran

Legenda: ★ = metode utama | ○ = alternatif yang didukung | — = tidak cocok

| Peran | WA OTP | Passkey (biometrik) | Username + Password | SSO Google/Microsoft | Magic Link WA | QR Login |
|---|---|---|---|---|---|---|
| Siswa (punya HP/WA) | ★ tanpa password, HP milik sendiri | ○ bila perangkat mendukung | — mudah lupa, ditulis sembarangan | — | ○ | ○ di perangkat sekolah |
| Siswa (tanpa HP, umumnya SD/SMP) | — tidak punya WA sendiri | — | — | — | — | ★ kartu QR di sekolah |
| Orang Tua/Wali | ★ tanpa password; WA selalu ada | ★/○ deteksi otomatis, jadikan utama bila didukung | ○ cadangan | — | ○ sekali ketuk dari chat | — |
| Guru / Tendik / Wali Kelas / BK | ○ untuk recovery dan verifikasi langkah sensitif | ★ (Fase 2) anti-phishing pengelola data anak | ★ (Fase 1) fallback universal | ○ bila sekolah pakai Workspace for Education | — | — |
| Kepsek / Admin Sekolah / Bendahara | ○ | ★ (Fase 2) | ★ (Fase 1) + 2FA untuk admin | ○ | — | — |
| Platform Admin / Support / Finance | — | ★ wajib + password (2FA) | ★ wajib (2FA TOTP) | ○ | — | — |
| Admin Organisasi | ○ | ○ | ★ | ○ | — | — |

**Bacaan cepat matriks ini:**
- **Orang tua dan siswa ber-HP → WA OTP** sebagai default PWA: tanpa password, tanpa email, tanpa install.
- **Siswa tanpa HP → dua jalur**: di rumah memakai **mode profil anak** di PWA orang tua (tanpa login ulang); di sekolah memakai **kartu QR** di perangkat bersama.
- **Staf → password dulu (Fase 1), passkey kemudian (Fase 2)** sebagai peningkatan keamanan tanpa menambah gesekan.
- **PIN sederhana dievaluasi dan ditolak** untuk semua peran: mudah ditebak/diintip di perangkat bersama dan tidak phishing-resistant — QR login + profil anak sudah menutup kasus pakainya dengan lebih aman.

---

## 3. Detail Tiap Metode

### 3.1 WA OTP (metode utama PWA: orang tua & siswa ber-HP)

**Alur (3 langkah):**
1. Pengguna memasukkan nomor WA → server membuat kode 6 digit acak kriptografis, disimpan sebagai hash + expiry.
2. Kode dikirim via template WhatsApp **terotorisasi Meta** (kategori *authentication*, dengan tombol salin kode sekali ketuk).
3. Pengguna memasukkan kode → server verifikasi, **langsung hanguskan kode** (satu kali pakai), terbitkan token sesi.

**Parameter keamanan (selaras praktik industri):**
- Panjang kode: **6 digit** (4 digit terlalu mudah ditebak: 1 banding 10.000).
- Expiry: **5 menit** (praktik umum 3–5 menit untuk konsumen; di bawah 60 detik menyebabkan banyak kegagalan).
- Cooldown kirim ulang: **60 detik** antar permintaan.
- Rate limit kirim: **maks. 3x per 15 menit, maks. 5x per hari per nomor** — mencegah *OTP bombing* dan pemborosan biaya kirim.
- Rate limit verifikasi: **maks. 5x salah per kode** → kunci 15 menit (selaras FR-903).
- Satu nomor hanya terikat ke akun yang nomornya terdaftar (validasi di `guardians.no_wa` / `users.phone`).

**Template pesan (contoh, gaya Meta authentication template):**
> Kode masuk SIMS *[Nama Sekolah]*: *483921*. Berlaku 5 menit. Jangan berikan kode ini ke siapa pun, termasuk yang mengaku dari sekolah.

**Anti-abuse:**
- Deteksi pola anomali: lonjakan permintaan ke rentang nomor berurutan, *verify rate* anjlok tiba-tiba → alert + blokir sementara otomatis.
- Nomor VoIP/burner ditandai berisiko untuk pendaftaran baru (pre-flight check opsional).
- Semua percobaan tercatat (siapa, kapan, IP, perangkat) — masuk log keamanan §17.3.

**Fallback:** bila WA tidak terkirim dalam 60 detik (tidak ada internet / nomor tidak terdaftar di WA), tawarkan **SMS OTP** sebagai cadangan otomatis. Email hanya opsi terakhir untuk staf.

### 3.2 Passkey / WebAuthn (biometrik: sidik jari & Face ID)

**Cara kerja singkat:** saat registrasi, perangkat membuat pasangan kunci kriptografi — kunci privat **tidak pernah keluar dari perangkat** (disimpan di Secure Enclave/TPM atau tersinkron terenkripsi via iCloud Keychain / Google Password Manager); server hanya menyimpan kunci publik. Saat login, server mengirim tantangan yang ditandatangani perangkat setelah verifikasi biometrik. Karena tanda tangan **terikat ke domain asli**, passkey **kebal phishing**: situs palsu tidak bisa memakainya.

**Status dukungan (Oktober 2026):** praktis universal — Chrome/Edge 128+, Safari 18+ (iOS 18+/macOS 15+), Firefox 122+. Autentikator platform (sidik jari Android, Face ID/Touch ID Apple) didukung penuh; login lintas perangkat via QR (scan dari HP) juga didukung. **Catatan penting:** WebAuthn **tidak berjalan di webview dalam aplikasi** (browser di dalam aplikasi Facebook/Instagram/dll.) — pengguna yang membuka tautan dari sana harus diarahkan membuka di browser utama.

**Alur registrasi ("Aktifkan login sidik jari"):**
1. Pengguna sudah login (via WA OTP/password) → di Pengaturan mengetuk "Aktifkan login sidik jari".
2. Browser meminta biometrik → passkey terdaftar ke akun (satu akun boleh punya beberapa passkey: HP + laptop).
3. Login berikutnya: cukup ketuk "Masuk dengan sidik jari" → biometrik → masuk. Tanpa mengetik apa pun.

**Deteksi kemampuan otomatis:** PWA/web memeriksa `PublicKeyCredential` di browser. Bila tidak didukung (HP lama, webview), tombol passkey **disembunyikan** dan pengguna diarahkan ke WA OTP — tanpa pesan error membingungkan.

**Cross-device:** login di komputer TU memakai QR yang dipindai HP (passkey di HP menandatangani tantangan untuk sesi komputer) — berguna untuk staf yang HP-nya modern tapi komputernya bersama.

**Recovery bila HP hilang:** (1) cabut perangkat hilang dari daftar perangkat (lewat perangkat lain atau minta admin), yang otomatis menonaktifkan passkey-nya; (2) daftar ulang passkey di HP baru setelah verifikasi ulang via WA OTP. Tidak ada jalan pintas "recovery code" yang disimpan di kertas — itu justru vektor serangan.

**Rekomendasi penempatan:** jadikan **metode utama yang ditawarkan** (bukan dipaksa) untuk staf dan orang tua yang perangkatnya mendukung; WA OTP tetap sebagai cadangan. Untuk Platform Admin, passkey + password = 2FA wajib.

### 3.3 QR Login untuk Siswa (kartu pelajar + perangkat bersama)

**Masalah yang dipecahkan:** siswa SD/SMP tanpa HP perlu memakai PWA di perangkat bersama (tablet kelas, komputer perpus) untuk melihat jadwal/nilai atau presensi — tanpa mengetik username/password yang pasti lupa atau bocor.

**Alur:**
1. Sekolah mencetak **kartu pelajar ber-QR** per siswa (dari panel admin; dapat dicetak massal per rombel).
2. QR berisi token acak, **sekali pakai, kedaluwarsa 60 detik**, dan di-refresh otomatis di layar (untuk kartu cetak: token dicetak ulang tiap semester; untuk sesi: QR dinamis di layar).
3. Siswa memindai QR di perangkat bersama → sesi **terbatas** terbuka: hanya data miliknya, **tanpa hak ubah data sensitif**, dan **otomatis keluar** setelah 8 jam (satu hari sekolah) atau saat tombol Keluar ditekan.
4. Varian dinamis: guru dapat menampilkan QR sesi di layar kelas untuk presensi mandiri — siswa memindai dengan HP-nya (bagi yang punya) sebagai bukti kehadiran.

**Keamanan:** token QR tidak dapat ditebak (128-bit acak), terikat ke satu siswa + satu sekolah, dan tidak memberi akses ke data siswa lain. Kartu hilang = admin menonaktifkan token kartu itu dalam 1 klik dan mencetak ulang.

### 3.4 Akun Siswa Terhubung ke Orang Tua (mode profil anak)

**Masalah yang dipecahkan:** mayoritas siswa SD/SMP tidak punya HP/WA sendiri — memaksa mereka punya akun login sendiri justru menciptakan akun hantu yang passwordnya dipegang orang tua juga.

**Desain:** PWA orang tua memiliki **pengalih profil** (profile switcher). Setelah orang tua login (WA OTP), ia dapat beralih ke "mode anak" untuk tiap anaknya — tanpa login ulang, tanpa password anak. Mode anak menampilkan: jadwal, nilai, absensi, pengumuman — **read-only**, tanpa akses ke pengaturan akun, perangkat, atau data saudaranya di luar yang diizinkan. Izin mode anak dapat dimatikan orang tua kapan pun (mis. untuk anak yang sudah cukup umur memiliki akun sendiri).

**Aturan:** satu sesi ortu = satu identitas terverifikasi; semua aksi dalam mode anak dicatat atas nama akun orang tua (audit jelas). Saat anak naik ke jenjang/usia yang layak punya akun sendiri, sekolah dapat "memisahkan" akun: nomor WA anak didaftarkan, riwayat tetap tersambung.

### 3.5 SSO Google/Microsoft

Untuk sekolah yang memakai Google Workspace for Education / Microsoft 365: login dengan akun sekolah (`@sman1.sch.id`) via OAuth2/OIDC. Domain sekolah didaftarkan di allowlist tenant; klaim email dicocokkan ke `users.email`. Cocok untuk guru/tendik yang sehari-hari sudah memakai akun tersebut. Bukan prioritas Fase 1 (butuh koordinasi IT sekolah), tetapi disiapkan arsitekturnya sejak awal (Socialite/Laravel).

### 3.6 Username + Password (fallback universal)

Tetap dipertahankan untuk **staf dan peran platform** sebagai metode yang selalu tersedia: username = NIP/NISN/email; kebijakan sesuai FR-903 (min. 8 karakter huruf+angka, kunci 15 menit setelah 5x gagal); 2FA TOTP wajib untuk Platform Admin dan dianjurkan untuk Admin Sekolah (FR-904). **Tidak** dijadikan metode utama untuk siswa/orang tua — pengalaman menunjukkan di sinilah mayoritas tiket bantuan berasal.

---

## 4. Kebijakan Sesi per Peran

| Konteks | Idle timeout (keluar otomatis bila tidak aktif) | Masa berlaku absolut | "Ingat perangkat" | Catatan |
|---|---|---|---|---|
| PWA orang tua (perangkat pribadi) | 30 hari (sliding) | 90 hari → login ulang | Ya, 30 hari per perangkat tepercaya | Gesekan minimal; perangkat dapat dicabut kapan pun |
| PWA siswa (HP sendiri) | 30 hari (sliding) | 90 hari | Ya | Sama seperti ortu |
| PWA mode anak / QR di perangkat bersama | **8 jam** (satu hari sekolah) | 8 jam | **Tidak** — tidak ada "ingat" di perangkat bersama | Wajib ada tombol Keluar yang mencolok |
| Web staf (komputer TU/kelas) | 30 menit (selaras §17.2) | 12 jam | Opsional, hanya di perangkat yang ditandai tepercaya | Peringatan 2 menit sebelum timeout |
| Platform Admin / Support / Finance | 15 menit | 8 jam | Tidak | 2FA wajib; impersonasi ≤ 30–60 menit |

Semua sesi: token Sanctum dengan refresh; pencabutan per perangkat sudah tersedia di API (`DELETE /auth/devices/{id}`, Dok 03 §2.7); perangkat baru memicu peringatan (FR-905).

---

## 5. Alur Pemulihan Akun

| Situasi | Alur | Catatan |
|---|---|---|
| **Lupa password (staf)** — *menutup gap G-01 Dok 08* | Ketuk "Lupa password" → pilih kirim via email **atau** WA terdaftar → terima tautan reset sekali pakai, **kedaluwarsa 60 menit** → buat password baru (ikut FR-903) | Tautan tidak menampilkan apakah akun ada (anti-enumerasi); semua permintaan dicatat |
| **Ganti password (PWA)** — *menutup gap G-02 Dok 08* | Pengaturan → Ganti password: masukkan password lama (atau verifikasi WA OTP bila login via OTP) + password baru 2x | Untuk pengguna OTP-only, "password" bersifat opsional — yang utama adalah rotasi/perangkat |
| **HP hilang** | (1) Dari perangkat lain / minta admin: cabut perangkat hilang → token & passkey-nya mati seketika; (2) di HP baru: login via WA OTP → daftarkan ulang passkey | Tidak ada kode recovery cetak |
| **Akun terkunci** (5x gagal) | Buka otomatis setelah 15 menit (FR-903); admin sekolah dapat membuka manual lebih cepat dengan alasan tercatat | Notifikasi ke pemilik akun saat terjadi penguncian |
| **Nomor WA berubah** | Verifikasi nomor baru via OTP ke nomor baru + konfirmasi via nomor lama bila masih aktif; bila nomor lama hangus: verifikasi tatap muka oleh admin sekolah + KTP/KK | Mencegah pengambilalihan akun via nomor daur ulang operator |
| **Kartu QR hilang** | Admin menonaktifkan token kartu → cetak ulang; sesi aktif dari kartu lama ikut dicabut | 1 klik, tanpa reset data lain |

---

## 6. Anti-Abuse & Keamanan Khusus Auth

(Rincian umum ada di PRD §17 — di sini hanya yang spesifik autentikasi.)

- **Passkey anti-phishing by design**: kredensial terikat domain; tidak ada rahasia yang bisa dicuri server atau disadap.
- **Proteksi brute-force OTP**: 5x salah → kunci 15 menit; kode hangus setelah 1x pakai benar; tidak ada pesan "kode hampir benar".
- **Proteksi OTP bombing**: rate limit kirim (§3.1) + deteksi pola farm (banyak nomor berurutan, *verify rate* anjlok).
- **Deteksi SIM-swap / pengambilalihan**: sinyal gabungan — perangkat baru + login dari lokasi/IP tak biasa + segera ganti pengaturan sensitif → minta verifikasi ulang (step-up via WA OTP) sebelum aksi dilanjutkan.
- **QR login**: token 128-bit, sekali pakai, 60 detik; tidak memberi eskalasi hak; sesi perangkat bersama selalu pendek dan tanpa "ingat saya".
- **Magic link**: token acak 256-bit di URL, sekali pakai, kedaluwarsa 15 menit; hanya dikirim ke nomor WA terdaftar.
- **Enumerasi akun**: respons "lupa password" dan "nomor tidak terdaftar" dibuat identik dari sisi waktu & pesan.

---

## 7. Rekomendasi Implementasi Bertahap

### Fase 1 — Cakupan maksimal, dependensi minimal
**WA OTP + Username/Password + QR login siswa + mode profil anak.**
- Alasan urutan: keempatnya menutup ~95% kebutuhan login Indonesia **tanpa mensyaratkan perangkat modern** — hanya butuh nomor WA yang memang sudah dimiliki. QR + profil anak menyelesaikan kasus "siswa tanpa HP" yang merupakan mayoritas di SD/SMP.
- Yang dibangun: `otp_codes` (sudah di Dok 02), alur OTP PWA, kartu QR + sesi perangkat bersama, pengalih profil anak, lupa/ganti password mandiri (G-01/G-02).

### Fase 2 — Naik kelas keamanan & kenyamanan
**Passkey (WebAuthn) + SSO Google/Microsoft.**
- Alasan urutan: butuh edukasi pengguna ("aktifkan login sidik jari") dan perangkat yang mendukung; dijadikan **opt-in yang ditawarkan otomatis** (deteksi kemampuan) bukan paksaan. Mulai dari staf (risiko tertinggi: pengelola data anak), lalu tawarkan ke orang tua.
- Yang dibangun: endpoint passkey (lihat §8), registrasi & manajemen passkey per akun, integrasi Socialite untuk SSO, panduan aktivasi per peran.

### Yang sengaja tidak dibangun
- **PIN sederhana**: ditolak (lihat §2) — kasus pakainya sudah tertutup QR + profil anak dengan lebih aman.
- **Login via media sosial pribadi** (Facebook/dll.): tidak relevan untuk konteks sekolah dan menambah permukaan privasi.

---

## 8. Endpoint API Baru / Diubah (koordinasi dengan Dok 03)

Cukup daftar + deskripsi 1 baris; spesifikasi penuh mengikuti format Dok 03 saat implementasi.

| Method & Path | Deskripsi |
|---|---|
| `POST /auth/passkey/register-options` | Mulai registrasi passkey: kembalikan challenge + opsi WebAuthn (butuh sesi login aktif) |
| `POST /auth/passkey/register-verify` | Verifikasi respons WebAuthn, simpan public key ke akun |
| `POST /auth/passkey/login-options` | Mulai login passkey: kembalikan challenge (identifikasi via username/nomor atau *discoverable credential*) |
| `POST /auth/passkey/login-verify` | Verifikasi tanda tangan → terbitkan token sesi |
| `GET /auth/passkey` | Daftar passkey milik akun (nama perangkat, terakhir dipakai) |
| `DELETE /auth/passkey/{id}` | Hapus passkey (mis. HP dijual/hilang) |
| `POST /auth/magic-link` | Kirim tautan login sekali pakai via WA ke nomor terdaftar |
| `GET /auth/magic-link/verify?token=` | Tukar token magic link menjadi sesi (satu kali pakai, 15 menit) |
| `POST /auth/qr/challenge` | Buat QR login (oleh admin/guru/layar kelas): token 128-bit, 60 detik |
| `POST /auth/qr/redeem` | Tukar token QR menjadi sesi terbatas perangkat bersama (8 jam, tanpa ingat perangkat) |
| `POST /auth/qr/revoke-card` | Nonaktifkan kartu QR siswa yang hilang + cabut sesinya |
| `POST /auth/password` | Ganti password (perlu password lama, atau verifikasi WA OTP bagi pengguna OTP-only) — *menutup G-02* |
| `POST /auth/password/forgot` | Minta tautan reset (via email/WA terdaftar; respons anti-enumerasi) — *menutup G-01* |
| `POST /auth/password/reset` | Tukar token reset (60 menit) dengan password baru |
| `POST /auth/switch-profile` | Beralih ke mode profil anak `{siswa_id}` dalam sesi orang tua (read-only, tercatat di audit) |
| `POST /auth/step-up` | Verifikasi ulang (WA OTP) saat sinyal risiko terdeteksi (SIM-swap, perangkat baru + aksi sensitif) |

**Perubahan pada endpoint Dok 03 yang sudah ada:** `POST /auth/verify-otp` perlu flag `trust_device` (ingat perangkat 30 hari) dan respons menyertakan `webauthn_supported` agar PWA tahu kapan menawarkan aktivasi passkey.

---

## Referensi Riset

- Status dukungan passkey/WebAuthn 2026 (Chrome/Edge 128+, Safari 18+, Firefox 122+; autentikator platform Android/iOS; keterbatasan webview dalam aplikasi): https://www.corbado.com/blog/webauthn-related-origins-cross-domain-passkeys , https://github.com/1shot-api/skills/blob/HEAD/webauthn-prf-wallet/references/platform-support.md , https://github.com/luigibarretta/wallet-aggregator/blob/HEAD/docs/platform-support.md
- Praktik terbaik WhatsApp OTP (kode 6 digit, expiry 3–5 menit, rate limit kirim ±3/5 menit & verifikasi 5x, template autentikasi Meta resmi dengan tombol salin, fallback SMS, monitoring pola abuse): https://www.messagecentral.com/en-in/blog/whatsapp-login-with-otp-authentication , https://bsg.world/blog/about/otp-meaning-one-time-password-explained , https://dev.to/triochat_io/send-otp-via-whatsapp-the-smarter-faster-way-to-verify-users-3b4i
- Pola login perangkat bersama & QR (MFA standar tidak cocok untuk terminal bersama; QR/NFC sebagai autentikasi per-individu tanpa perangkat pribadi; pola transfer sesi ala WhatsApp Web): https://www.oloid.com/blog/multi-factor-authentication-examples , https://dev.to/debuguser/authsync-a-passwordless-cross-device-authentication-system-inspired-by-whatsapp-web-fido2-5gi2 , https://medium.com/@x2.mobile/secure-user-authentication-passkeys-04aa14f19e38
