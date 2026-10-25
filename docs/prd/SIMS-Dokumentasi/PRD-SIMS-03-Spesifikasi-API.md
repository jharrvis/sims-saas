# Dok 03 — Spesifikasi API v1 (untuk PWA Siswa & Orang Tua)

| Atribut | Keterangan |
|---|---|
| Dokumen induk | PRD — Sistem Informasi Manajemen Sekolah (SIMS) Multi-Sekolah Terpusat v1.0 |
| Versi Dokumen | 1.1 |
| Tanggal | 9 Oktober 2026 (revisi pivot SaaS) |
| Cakupan | REST API untuk PWA siswa & orang tua + API tenant, billing sekolah, dan API platform internal (SaaS multi-tenant publik) |
| Konsumen | PWA (service worker + web app), integrasi internal |
| Status | Draft untuk review |

---

## 1. Informasi Umum

### 1.1 Base URL & Versioning

```
https://api.sims.example.com/api/v1
```

- Semua endpoint diawali prefix `/api/v1`. Versi mayor baru (`/api/v2`) hanya dibuat bila ada perubahan *breaking*; v1 dipertahankan minimal 12 bulan setelah v2 rilis.
- Header wajib: `Accept: application/json`.
- Header auth: `Authorization: Bearer <sanctum_token>`.

### 1.2 Autentikasi

Menggunakan **Laravel Sanctum** (personal access token):

1. Klien login via OTP (nomor WA) atau username+password → server menerbitkan token.
2. Setiap request berikutnya menyertakan `Authorization: Bearer <token>`.
3. Token terikat ke **perangkat** (`device_name`); satu user boleh punya banyak token (banyak perangkat).
4. Token dapat dicabut per perangkat (`DELETE /auth/devices/{id}`) atau semuanya saat logout.

### 1.3 Format Respons Sukses (Envelope)

```json
{
  "success": true,
  "message": "Data jadwal berhasil diambil.",
  "data": { "...": "..." },
  "meta": { "cached_at": "2026-10-09T07:00:00+07:00" }
}
```

- `data`: objek/array hasil. Boleh `null` untuk aksi tanpa payload (mis. logout).
- `meta`: opsional; dipakai untuk info tambahan (cache, pagination ringkas, dsb).

### 1.4 Format Respons Error

```json
{
  "success": false,
  "message": "Kode OTP salah atau sudah kedaluwarsa.",
  "errors": { "otp": ["Kode OTP tidak valid."] },
  "code": "OTP_KEDALUWARSA"
}
```

- `errors`: detail validasi per field (format Laravel, hanya ada pada 422).
- `code`: kode bisnis/mesin (lihat §7), stabil untuk di-switch oleh klien.

### 1.5 Paginasi Standar

Untuk endpoint daftar:

```json
{
  "success": true,
  "message": "OK",
  "data": [ { "...": "..." } ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 132,
    "last_page": 7
  }
}
```

Query umum: `?page=1&per_page=20` (maks. `per_page` = 100).

### 1.6 Rate Limit

| Endpoint | Batas |
|---|---|
| Umum (per token / per IP anonim) | 60 request/menit |
| `POST /auth/request-otp` & `POST /auth/verify-otp` | 5x per 10 menit per nomor |
| `POST /auth/login` | 5x per menit per IP (kunci akun setelah 5x gagal, FR-903) |

Respons 429 menyertakan header `Retry-After` (detik).

### 1.7 Waktu & Zona Waktu

- Semua timestamp dalam **ISO-8601 dengan offset zona waktu sekolah**, mis. `2026-10-09T07:00:00+07:00`.
- Nama hari memakai Bahasa Indonesia lowercase: `senin, selasa, rabu, kamis, jumat, sabtu, minggu`.

### 1.8 Tenant Resolution (SaaS)

SIMS adalah **SaaS multi-tenant publik**: tiap sekolah adalah 1 tenant pelanggan independen dengan subdomain, paket, dan modul add-on sendiri.

| Kanal | Cara tenant ditentukan |
|---|---|
| Web staf & PWA (browser) | Subdomain `{slug-sekolah}.sims.id` (cth. `sman1bdg.sims.id`) atau custom domain milik sekolah; middleware `ResolveTenant` memetakan host → `schools.id` **sebelum** auth |
| API (seluruh endpoint tenant) | Disimpulkan dari **token Sanctum**: `abilities` token memuat `school_id` (+ `role`). Klien **tidak pernah** mengirim `school_id` |

- Token diterbitkan per kombinasi user × **satu membership aktif** (ADR-0004): `abilities` token memuat tepat satu `school_id`. Wali dengan anak di 2 sekolah memilih tenant aktif saat login (atau via switcher); dashboard lintas tenant diagregasi server-side dengan memverifikasi tiap membership + relasi wali-anak satu per satu. Token multi-`school_id` **dilarang**.
- Bila status langganan tenant bermasalah → **402** (`LANGGANAN_BERMASALAH`); bila endpoint termasuk modul add-on yang tidak aktif untuk tenant → **403** (`MODULE_DISABLED`). Lihat §7.

---

## 2. Modul Auth

### 2.1 Minta Kode OTP — `POST /auth/request-otp`

**Auth:** publik (tanpa token). **Rate limit:** 5x/10 menit per nomor.

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `phone` | body | string | Ya | Nomor WA format Indonesia, cth. `081234567890` atau `6281234567890` |

Contoh respons (200):

```json
{
  "success": true,
  "message": "Kode OTP telah dikirim ke WhatsApp 0812***7890.",
  "data": {
    "phone_masked": "0812***7890",
    "expires_in_seconds": 300,
    "resend_available_in_seconds": 60
  },
  "meta": null
}
```

Error: `422` (nomor tidak terdaftar → code `NOMOR_TIDAK_TERDAFTAR`), `429` (terlalu sering → code `OTP_RATE_LIMITED`).

### 2.2 Verifikasi OTP — `POST /auth/verify-otp`

**Auth:** publik. **Rate limit:** 5x/10 menit per nomor.

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `phone` | body | string | Ya | Nomor WA yang sama |
| `otp` | body | string | Ya | 6 digit |
| `device_name` | body | string | Ya | Nama perangkat, cth. `HP Ibu - Redmi 12` |

Contoh respons (200) — wali dengan 2 anak di 2 sekolah (FR-203):

```json
{
  "success": true,
  "message": "Login berhasil. Selamat datang, Ibu Siti Aminah.",
  "data": {
    "token": "12|aBcDeFgHiJkLmNoPqRsTuVwXyZ0123456789abcd",
    "token_type": "Bearer",
    "user": {
      "id": 501,
      "nama": "Siti Aminah",
      "peran": "orang_tua",
      "no_wa": "081234567890"
    },
    "profil": [
      {
        "tipe": "orang_tua",
        "wali_id": 501,
        "anak": [
          {
            "siswa_id": 1201,
            "nama": "Ahmad Fauzi",
            "nis": "24250115",
            "kelas": "XII IPA 1",
            "sekolah": { "id": 3, "nama": "SMA Negeri 1 Bandung", "logo_url": "https://cdn.sims.example.com/logo/sman1bdg.png" }
          },
          {
            "siswa_id": 3407,
            "nama": "Dinda Aulia",
            "nis": "26220044",
            "kelas": "VIII A",
            "sekolah": { "id": 7, "nama": "SMP Negeri 2 Bandung", "logo_url": "https://cdn.sims.example.com/logo/smpn2bdg.png" }
          }
        ]
      }
    ],
    "device_id": 88
  },
  "meta": null
}
```

> Bila nomor terdaftar sebagai **siswa** (no. HP siswa), `profil` berisi `{"tipe":"siswa", "siswa_id":..., "nama":..., "kelas":..., "sekolah":{...}}`. Bila nomor merangkap (wali sekaligus pegawai), `profil` berisi >1 entri dan klien menampilkan pemilih peran.

Error: `422` (OTP salah → `OTP_SALAH`; kedaluwarsa → `OTP_KEDALUWARSA`).

### 2.3 Login Username+Password — `POST /auth/login`

**Auth:** publik. Alternatif OTP (FR-1306).

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `username` | body | string | Ya | NIS / NIP / email |
| `password` | body | string | Ya | Min. 8 karakter |
| `device_name` | body | string | Ya | Nama perangkat |

Respons sukses sama seperti §2.2 (token + profil). Error: `401` (`KREDENSIAL_SALAH`), `423` terkunci (`AKUN_TERKUNCI`).

### 2.4 Logout — `POST /auth/logout`

**Auth:** Bearer token (peran apa pun).

Respons (200):

```json
{
  "success": true,
  "message": "Berhasil keluar. Token perangkat ini telah dicabut.",
  "data": null,
  "meta": null
}
```

### 2.5 Profil Saya — `GET /auth/me`

**Auth:** Bearer token.

Contoh respons (200) — token siswa:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "user_id": 1201,
    "nama": "Ahmad Fauzi",
    "peran": "siswa",
    "siswa": {
      "siswa_id": 1201,
      "nis": "24250115",
      "nisn": "0061234567",
      "kelas": "XII IPA 1",
      "tahun_ajaran": "2026/2027",
      "semester": "Ganjil",
      "foto_url": "https://cdn.sims.example.com/foto/siswa/1201.jpg"
    },
    "sekolah": {
      "id": 3,
      "nama": "SMA Negeri 1 Bandung",
      "npsn": "20100123",
      "timezone": "Asia/Jakarta",
      "logo_url": "https://cdn.sims.example.com/logo/sman1bdg.png",
      "warna_tema": "#1B5E20"
    }
  },
  "meta": null
}
```

### 2.6 Daftar Perangkat — `GET /auth/devices`

**Auth:** Bearer token. Untuk manajemen sesi (FR-1306).

Contoh respons (200):

```json
{
  "success": true,
  "message": "OK",
  "data": [
    { "id": 88, "device_name": "HP Ibu - Redmi 12", "login_terakhir": "2026-10-09T06:42:10+07:00", "perangkat_ini": true },
    { "id": 71, "device_name": "HP Ayah - Samsung A14", "login_terakhir": "2026-10-07T19:15:44+07:00", "perangkat_ini": false }
  ],
  "meta": null
}
```

### 2.7 Cabut Perangkat — `DELETE /auth/devices/{id}`

**Auth:** Bearer token. Mencabut token perangkat lain milik user yang sama.

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `id` | path | integer | Ya | ID perangkat dari §2.6 |

Respons (200): `{success:true, message:"Sesi perangkat 'HP Ayah - Samsung A14' telah dicabut."}`. Error: `404` (`PERANGKAT_TIDAK_DITEMUKAN`), `409` bila mencoba mencabut perangkat yang sedang dipakai (`TIDAK_BISA_CABUT_DIRI_SENDIRI`).

---

### 2.8 Ganti Password — `POST /auth/password`

**Auth:** Bearer <redacted> (peran apa pun; untuk staf via web maupun siswa/orang tua via PWA — mendukung US-106).

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `password_lama` | body | string | Ya | Password saat ini (verifikasi identitas) |
| `password_baru` | body | string | Ya | Min. 8 karakter, kombinasi huruf+angka |
| `password_baru_confirmation` | body | string | Ya | Harus sama dengan `password_baru` |

Respons (200):

```json
{
  "success": true,
  "message": "Password berhasil diubah. Silakan login kembali di perangkat lain.",
  "data": null,
  "meta": null
}
```

Error: `422` (password lama salah → `PASSWORD_LAMA_SALAH`; password baru tidak memenuhi kebijakan). Seluruh token lain milik user **tetap aktif** kecuali user memilih `cabut_perangkat_lain: true` (opsional, disarankan bila password diduga bocor). Aksi tercatat di audit log.

---

### 2.9 Refresh Token — `POST /auth/refresh` (US-126, P1-08)

**Model sesi:** access token berumur pendek (60 menit) + refresh token (30 hari, httpOnly cookie untuk web / secure storage untuk PWA). Refresh memakai **rotasi**: setiap pemakaian menerbitkan pasangan baru dan menginvalidasi yang lama.

| Aturan | Detail |
|---|---|
| Reuse detection | Refresh token yang sudah dipakai lalu dipakai lagi = indikasi pencurian → **seluruh token family user dicabut** + notifikasi keamanan ke user |
| Device binding | Refresh token terikat `device_id` (§2.6); dipakai dari device lain → ditolak (`REFRESH_DEVICE_MISMATCH`) |
| Revoke chain | Logout / cabut perangkat / ganti password mencabut seluruh family |

Body: `{ "device_id": 88 }` (opsional; default perangkat saat ini). Respons (200): access token baru + refresh token baru (rotasi). Error: `401` (`REFRESH_KEDALUWARSA`, `REFRESH_DICABUT`, `REFRESH_REUSE_TERDETEKSI`).

---

## 3. Modul Siswa (token peran `siswa`)

Semua endpoint di bawah **hanya** bisa diakses token berperan siswa, dan otomatis mengembalikan data **siswa pemilik token** (FR-1302). Tidak ada parameter `siswa_id`/`school_id` dari klien — scoping dari token (§8).

### 3.1 Profil Siswa — `GET /siswa/profil`

**Auth:** Bearer token peran `siswa`.

Contoh respons (200):

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "siswa_id": 1201,
    "nama": "Ahmad Fauzi",
    "nis": "24250115",
    "nisn": "0061234567",
    "jenis_kelamin": "L",
    "kelas": "XII IPA 1",
    "wali_kelas": "Dra. Hj. Ratna Dewi, M.Pd.",
    "tahun_ajaran": "2026/2027",
    "semester": "Ganjil",
    "foto_url": "https://cdn.sims.example.com/foto/siswa/1201.jpg",
    "sekolah": {
      "id": 3,
      "nama": "SMA Negeri 1 Bandung",
      "npsn": "20100123",
      "telepon": "(022) 1234567",
      "alamat": "Jl. Ir. H. Juanda No. 93, Bandung"
    }
  },
  "meta": null
}
```

### 3.2 Jadwal — `GET /siswa/jadwal`

**Auth:** Bearer token peran `siswa`.

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `hari` | query | string | Tidak | `senin`…`minggu`. Default: hari ini |

Contoh respons (200) — `GET /siswa/jadwal?hari=senin`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "hari": "senin",
    "tanggal": "2026-10-12",
    "kelas": "XII IPA 1",
    "jadwal": [
      { "jam_ke": 1, "waktu": "07:00-07:45", "mapel": "Matematika", "guru": "Drs. Bambang Sutrisno", "ruang": "R-12" },
      { "jam_ke": 2, "waktu": "07:45-08:30", "mapel": "Matematika", "guru": "Drs. Bambang Sutrisno", "ruang": "R-12" },
      { "jam_ke": 3, "waktu": "08:30-09:15", "mapel": "Bahasa Indonesia", "guru": "Siti Rahayu, S.Pd.", "ruang": "R-12" },
      { "jam_ke": 4, "waktu": "09:30-10:15", "mapel": "Fisika", "guru": "Agus Hermawan, S.Si.", "ruang": "Lab Fisika" },
      { "jam_ke": 5, "waktu": "10:15-11:00", "mapel": "Fisika", "guru": "Agus Hermawan, S.Si.", "ruang": "Lab Fisika" }
    ]
  },
  "meta": { "cached_at": "2026-10-09T07:00:00+07:00" }
}
```

### 3.3 Jadwal Seminggu — `GET /siswa/jadwal/minggu-ini`

**Auth:** Bearer token peran `siswa`. Tanpa parameter.

Contoh respons (200) — ringkas, 2 hari pertama ditampilkan:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "kelas": "XII IPA 1",
    "pekan": "2026-10-12 s.d. 2026-10-17",
    "hari": [
      {
        "hari": "senin",
        "tanggal": "2026-10-12",
        "jadwal": [
          { "jam_ke": 1, "waktu": "07:00-07:45", "mapel": "Matematika", "guru": "Drs. Bambang Sutrisno", "ruang": "R-12" },
          { "jam_ke": 3, "waktu": "08:30-09:15", "mapel": "Bahasa Indonesia", "guru": "Siti Rahayu, S.Pd.", "ruang": "R-12" }
        ]
      },
      {
        "hari": "selasa",
        "tanggal": "2026-10-13",
        "jadwal": [
          { "jam_ke": 1, "waktu": "07:00-07:45", "mapel": "Kimia", "guru": "Dewi Lestari, S.Si.", "ruang": "Lab Kimia" },
          { "jam_ke": 2, "waktu": "07:45-08:30", "mapel": "Bahasa Inggris", "guru": "Rina Marlina, S.Pd.", "ruang": "R-12" }
        ]
      }
    ]
  },
  "meta": { "cached_at": "2026-10-09T07:00:00+07:00" }
}
```

> Hari tanpa jadwal (libur/minggu) dikembalikan dengan array `jadwal` kosong dan flag opsional `"keterangan": "Libur"`.

### 3.4 Absensi Saya — `GET /siswa/absensi`

**Auth:** Bearer token peran `siswa`. Riwayat + rekap (FR-1302, FR-605).

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `semester_id` | query | integer | Tidak | Default: semester aktif |
| `bulan` | query | string | Tidak | Format `YYYY-MM`, cth. `2026-09`. Default: bulan berjalan |

Contoh respons (200):

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "semester": "Ganjil 2026/2027",
    "periode": "2026-09",
    "rekap": { "hadir": 20, "izin": 1, "sakit": 1, "alpa": 0, "total_hari_efektif": 22, "persen_kehadiran": 90.9 },
    "riwayat": [
      { "tanggal": "2026-09-30", "status": "hadir", "keterangan": null },
      { "tanggal": "2026-09-29", "status": "sakit", "keterangan": "Surat dokter" },
      { "tanggal": "2026-09-28", "status": "hadir", "keterangan": null },
      { "tanggal": "2026-09-25", "status": "izin", "keterangan": "Acara keluarga" }
    ]
  },
  "meta": { "current_page": 1, "per_page": 31, "total": 22, "last_page": 1 }
}
```

> `status` ∈ `hadir | izin | sakit | alpa`. `persen_kehadiran` = (hadir+izin+sakit)/total_hari_efektif × 100.

### 3.5 Nilai Saya — `GET /siswa/nilai`

**Auth:** Bearer token peran `siswa`. **Hanya nilai yang sudah dirilis** (FR-707); bila belum ada yang dirilis → respons sukses dengan data kosong + code `NILAI_BELUM_DIRILIS` di `meta`.

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `semester_id` | query | integer | Tidak | Default: semester aktif |

Contoh respons (200) — sudah dirilis:

```json
{
  "success": true,
  "message": "Nilai semester Ganjil 2026/2027 telah dirilis pada 20 Desember 2026.",
  "data": {
    "semester": "Ganjil 2026/2027",
    "dirilis_pada": "2026-12-20T10:00:00+07:00",
    "nilai": [
      {
        "mapel": "Matematika",
        "kkm": 75,
        "komponen": [
          { "nama": "Tugas", "bobot": 20, "nilai": 85 },
          { "nama": "Ulangan Harian", "bobot": 30, "nilai": 78 },
          { "nama": "PTS", "bobot": 20, "nilai": 82 },
          { "nama": "PAS", "bobot": 30, "nilai": 88 }
        ],
        "nilai_akhir": 83.2,
        "predikat": "B",
        "tuntas_kkm": true
      },
      {
        "mapel": "Bahasa Indonesia",
        "kkm": 75,
        "komponen": [
          { "nama": "Tugas", "bobot": 20, "nilai": 90 },
          { "nama": "Ulangan Harian", "bobot": 30, "nilai": 86 },
          { "nama": "PTS", "bobot": 20, "nilai": 84 },
          { "nama": "PAS", "bobot": 30, "nilai": 91 }
        ],
        "nilai_akhir": 88.1,
        "predikat": "A",
        "tuntas_kkm": true
      },
      {
        "mapel": "Fisika",
        "kkm": 75,
        "komponen": [
          { "nama": "Tugas", "bobot": 20, "nilai": 70 },
          { "nama": "Ulangan Harian", "bobot": 30, "nilai": 68 },
          { "nama": "PTS", "bobot": 20, "nilai": 72 },
          { "nama": "PAS", "bobot": 30, "nilai": 74 }
        ],
        "nilai_akhir": 71.0,
        "predikat": "C",
        "tuntas_kkm": false
      }
    ],
    "rata_rata": 80.8
  },
  "meta": null
}
```

Contoh respons bila belum dirilis (200):

```json
{
  "success": true,
  "message": "Nilai semester ini belum dirilis oleh wali kelas.",
  "data": { "semester": "Ganjil 2026/2027", "nilai": [], "rata_rata": null },
  "meta": { "code": "NILAI_BELUM_DIRILIS" }
}
```

### 3.6 Pengumuman — `GET /siswa/pengumuman`

**Auth:** Bearer token peran `siswa`. Pengumuman yang targetnya mencakup siswa/kelasnya.

Contoh respons (200):

```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 45,
      "judul": "Upacara Bendera Hari Senin",
      "isi": "Seluruh siswa kelas X–XII wajib mengikuti upacara bendera pukul 07:00 di lapangan utama. Seragam lengkap.",
      "penting": true,
      "tayang_mulai": "2026-10-09T00:00:00+07:00",
      "tayang_selesai": "2026-10-12T23:59:59+07:00",
      "dibaca": false
    },
    {
      "id": 42,
      "judul": "Jadwal PAS Ganjil 2026/2027",
      "isi": "Penilaian Akhir Semester dilaksanakan 1–12 Desember 2026. Jadwal per kelas menyusul.",
      "penting": true,
      "tayang_mulai": "2026-10-01T00:00:00+07:00",
      "tayang_selesai": "2026-12-12T23:59:59+07:00",
      "dibaca": true
    }
  ],
  "meta": { "current_page": 1, "per_page": 20, "total": 2, "last_page": 1 }
}
```

---

## 4. Modul Orang Tua (token peran `orang_tua`)

Token wali dapat melihat **semua anaknya**, termasuk yang beda sekolah dalam jejaring (FR-203). Setiap endpoint memakai `{siswa_id}` di path; server memverifikasi relasi wali↔siswa — bila bukan anaknya → `403` (`BUKAN_ANAK_ANDA`).

### 4.1 Daftar Anak — `GET /ortu/anak`

**Auth:** Bearer token peran `orang_tua`. Tanpa parameter.

Contoh respons (200):

```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "siswa_id": 1201,
      "nama": "Ahmad Fauzi",
      "nis": "24250115",
      "kelas": "XII IPA 1",
      "foto_url": "https://cdn.sims.example.com/foto/siswa/1201.jpg",
      "sekolah": { "id": 3, "nama": "SMA Negeri 1 Bandung" },
      "kehadiran_hari_ini": { "tanggal": "2026-10-09", "status": "hadir" }
    },
    {
      "siswa_id": 3407,
      "nama": "Dinda Aulia",
      "nis": "26220044",
      "kelas": "VIII A",
      "foto_url": "https://cdn.sims.example.com/foto/siswa/3407.jpg",
      "sekolah": { "id": 7, "nama": "SMP Negeri 2 Bandung" },
      "kehadiran_hari_ini": { "tanggal": "2026-10-09", "status": "izin", "keterangan": "Kontrol ke dokter gigi" }
    }
  ],
  "meta": null
}
```

### 4.2 Ringkasan Anak — `GET /ortu/anak/{siswa_id}/ringkasan`

**Auth:** Bearer token peran `orang_tua`. Ringkasan satu layar (FR-1303).

Contoh respons (200) — `GET /ortu/anak/1201/ringkasan`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "siswa": {
      "siswa_id": 1201,
      "nama": "Ahmad Fauzi",
      "kelas": "XII IPA 1",
      "foto_url": "https://cdn.sims.example.com/foto/siswa/1201.jpg",
      "sekolah": { "id": 3, "nama": "SMA Negeri 1 Bandung" }
    },
    "kehadiran_hari_ini": { "tanggal": "2026-10-09", "status": "hadir", "keterangan": null },
    "kehadiran_bulan_ini": { "hadir": 14, "izin": 1, "sakit": 0, "alpa": 0, "persen_kehadiran": 100.0 },
    "nilai_terbaru": {
      "semester": "Ganjil 2026/2027",
      "dirilis_pada": "2026-12-20T10:00:00+07:00",
      "rata_rata": 80.8,
      "mapel_terendah": { "mapel": "Fisika", "nilai_akhir": 71.0, "tuntas_kkm": false }
    },
    "pengumuman_terbaru": [
      { "id": 45, "judul": "Upacara Bendera Hari Senin", "penting": true, "tayang_mulai": "2026-10-09T00:00:00+07:00" }
    ]
  },
  "meta": null
}
```

> Bila nilai belum dirilis, `nilai_terbaru` = `null` dan `meta.code` = `NILAI_BELUM_DIRILIS`.

### 4.3 Absensi Anak — `GET /ortu/anak/{siswa_id}/absensi`

**Auth:** Bearer token peran `orang_tua`. Parameter & format respons **sama** dengan §3.4 (`semester_id`, `bulan`, rekap + riwayat).

Contoh respons (200) — `GET /ortu/anak/3407/absensi?bulan=2026-09`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "siswa": { "siswa_id": 3407, "nama": "Dinda Aulia", "kelas": "VIII A" },
    "semester": "Ganjil 2026/2027",
    "periode": "2026-09",
    "rekap": { "hadir": 19, "izin": 2, "sakit": 1, "alpa": 0, "total_hari_efektif": 22, "persen_kehadiran": 100.0 },
    "riwayat": [
      { "tanggal": "2026-09-30", "status": "hadir", "keterangan": null },
      { "tanggal": "2026-09-26", "status": "izin", "keterangan": "Kontrol ke dokter gigi" }
    ]
  },
  "meta": { "current_page": 1, "per_page": 31, "total": 22, "last_page": 1 }
}
```

### 4.4 Nilai Anak — `GET /ortu/anak/{siswa_id}/nilai`

**Auth:** Bearer token peran `orang_tua`. Parameter & format respons **sama** dengan §3.5; hanya nilai yang sudah dirilis yang tampil (FR-707).

Contoh respons (200) — `GET /ortu/anak/1201/nilai` (ringkas, 1 mapel):

```json
{
  "success": true,
  "message": "Nilai semester Ganjil 2026/2027 telah dirilis pada 20 Desember 2026.",
  "data": {
    "siswa": { "siswa_id": 1201, "nama": "Ahmad Fauzi", "kelas": "XII IPA 1" },
    "semester": "Ganjil 2026/2027",
    "dirilis_pada": "2026-12-20T10:00:00+07:00",
    "nilai": [
      {
        "mapel": "Matematika",
        "kkm": 75,
        "komponen": [
          { "nama": "Tugas", "bobot": 20, "nilai": 85 },
          { "nama": "Ulangan Harian", "bobot": 30, "nilai": 78 },
          { "nama": "PTS", "bobot": 20, "nilai": 82 },
          { "nama": "PAS", "bobot": 30, "nilai": 88 }
        ],
        "nilai_akhir": 83.2,
        "predikat": "B",
        "tuntas_kkm": true
      }
    ],
    "rata_rata": 80.8
  },
  "meta": null
}
```

### 4.5 Jadwal Anak — `GET /ortu/anak/{siswa_id}/jadwal`

**Auth:** Bearer token peran `orang_tua`. Parameter & format respons **sama** dengan §3.2 (`hari` opsional; varian `/minggu-ini` juga tersedia: `GET /ortu/anak/{siswa_id}/jadwal/minggu-ini`).

Contoh respons (200) — `GET /ortu/anak/3407/jadwal?hari=jumat`:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "siswa": { "siswa_id": 3407, "nama": "Dinda Aulia", "kelas": "VIII A" },
    "hari": "jumat",
    "tanggal": "2026-10-09",
    "jadwal": [
      { "jam_ke": 1, "waktu": "07:00-07:40", "mapel": "IPA", "guru": "Hendra Gunawan, S.Pd.", "ruang": "R-08" },
      { "jam_ke": 2, "waktu": "07:40-08:20", "mapel": "Matematika", "guru": "Yuni Astuti, S.Pd.", "ruang": "R-08" },
      { "jam_ke": 3, "waktu": "08:20-09:00", "mapel": "Bahasa Indonesia", "guru": "Nina Kurnia, S.Pd.", "ruang": "R-08" }
    ]
  },
  "meta": { "cached_at": "2026-10-09T07:00:00+07:00" }
}
```

---

## 5. Modul Umum

### 5.1 Daftar Pengumuman — `GET /pengumuman`

**Auth:** Bearer token (peran `siswa` atau `orang_tua`). Sama dengan §3.6, namun untuk wali menggabungkan pengumuman dari **semua sekolah anaknya**, masing-masing item mencantumkan `sekolah`.

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `sekolah_id` | query | integer | Tidak | Filter per sekolah (harus milik anak/wali ybs.) |
| `penting_saja` | query | boolean | Tidak | `true` → hanya pengumuman penting |

Contoh respons (200) — token orang tua:

```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 45,
      "judul": "Upacara Bendera Hari Senin",
      "isi": "Seluruh siswa kelas X–XII wajib mengikuti upacara bendera pukul 07:00 di lapangan utama.",
      "penting": true,
      "sekolah": { "id": 3, "nama": "SMA Negeri 1 Bandung" },
      "tayang_mulai": "2026-10-09T00:00:00+07:00",
      "dibaca": false
    },
    {
      "id": 61,
      "judul": "Rapat Komite Sekolah",
      "isi": "Undangan rapat komite untuk orang tua siswa kelas VIII, Sabtu 17 Oktober 2026 pukul 09:00.",
      "penting": false,
      "sekolah": { "id": 7, "nama": "SMP Negeri 2 Bandung" },
      "tayang_mulai": "2026-10-08T00:00:00+07:00",
      "dibaca": false
    }
  ],
  "meta": { "current_page": 1, "per_page": 20, "total": 2, "last_page": 1 }
}
```

### 5.2 Tahun Ajaran Aktif — `GET /tahun-ajaran/aktif`

**Auth:** Bearer token. Mengembalikan tahun ajaran & semester aktif **per sekolah yang relevan** dengan token (siswa: 1 sekolah; wali: bisa >1).

Contoh respons (200) — token orang tua:

```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "sekolah": { "id": 3, "nama": "SMA Negeri 1 Bandung" },
      "tahun_ajaran": { "id": 11, "nama": "2026/2027", "tgl_mulai": "2026-07-13", "tgl_selesai": "2027-06-26" },
      "semester": { "id": 21, "nama": "Ganjil", "tgl_mulai": "2026-07-13", "tgl_selesai": "2026-12-24" }
    },
    {
      "sekolah": { "id": 7, "nama": "SMP Negeri 2 Bandung" },
      "tahun_ajaran": { "id": 31, "nama": "2026/2027", "tgl_mulai": "2026-07-13", "tgl_selesai": "2027-06-26" },
      "semester": { "id": 41, "nama": "Ganjil", "tgl_mulai": "2026-07-13", "tgl_selesai": "2026-12-24" }
    }
  ],
  "meta": null
}
```

> `semester_id` dari respons ini dipakai sebagai parameter `semester_id` di endpoint absensi/nilai.

### 5.3 Daftarkan Push Subscription — `POST /push/subscribe`

**Auth:** Bearer token. Menyimpan langganan push per perangkat (FR-1304, PRD §12).

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `endpoint` | body | string (url) | Ya | Push endpoint dari Push API browser |
| `keys.p256dh` | body | string | Ya | Kunci enkripsi p256dh |
| `keys.auth` | body | string | Ya | Kunci auth |
| `device_id` | body | integer | Tidak | ID perangkat dari §2.6 (jika sudah login) |

Contoh respons (201):

```json
{
  "success": true,
  "message": "Perangkat berhasil didaftarkan untuk notifikasi push.",
  "data": { "subscription_id": 512, "endpoint_terdaftar": true },
  "meta": null
}
```

Idempotent: endpoint yang sama didaftarkan ulang → 200 dengan data yang sama.

### 5.4 Hapus Push Subscription — `DELETE /push/unsubscribe`

**Auth:** Bearer token.

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `endpoint` | body | string (url) | Ya | Endpoint yang akan dihapus |

Respons (200): `{success:true, message:"Notifikasi push untuk perangkat ini dimatikan."}`. Error: `404` (`SUBSCRIPTION_TIDAK_DITEMUKAN`).

---

## 6. Ringkasan Endpoint

| Method | Path | Auth & Peran | Deskripsi |
|---|---|---|---|
| POST | `/auth/request-otp` | Publik | Minta kode OTP via WA |
| POST | `/auth/verify-otp` | Publik | Verifikasi OTP → token + profil |
| POST | `/auth/login` | Publik | Login username+password → token + profil |
| POST | `/auth/logout` | Token | Cabut token perangkat ini |
| GET | `/auth/me` | Token | Profil user + sekolah |
| GET | `/auth/devices` | Token | Daftar perangkat login |
| DELETE | `/auth/devices/{id}` | Token | Cabut sesi perangkat lain |
| POST | `/auth/password` | Token | Ganti password (verifikasi password lama) (§2.8) |
| GET | `/siswa/profil` | Token `siswa` | Profil siswa |
| GET | `/siswa/jadwal` | Token `siswa` | Jadwal harian (`?hari=`) |
| GET | `/siswa/jadwal/minggu-ini` | Token `siswa` | Jadwal seminggu |
| GET | `/siswa/absensi` | Token `siswa` | Riwayat + rekap absensi |
| GET | `/siswa/nilai` | Token `siswa` | Nilai per mapel (sudah dirilis) |
| GET | `/siswa/pengumuman` | Token `siswa` | Pengumuman untuk siswa |
| GET | `/ortu/anak` | Token `orang_tua` | Daftar anak (lintas sekolah) |
| GET | `/ortu/anak/{siswa_id}/ringkasan` | Token `orang_tua` | Ringkasan satu layar per anak |
| GET | `/ortu/anak/{siswa_id}/absensi` | Token `orang_tua` | Absensi per anak |
| GET | `/ortu/anak/{siswa_id}/nilai` | Token `orang_tua` | Nilai per anak (sudah dirilis) |
| GET | `/ortu/anak/{siswa_id}/jadwal` | Token `orang_tua` | Jadwal per anak |
| GET | `/pengumuman` | Token | Pengumuman (gabungan utk wali) |
| GET | `/tahun-ajaran/aktif` | Token | Tahun ajaran & semester aktif |
| POST | `/push/subscribe` | Token | Daftarkan push subscription |
| DELETE | `/push/unsubscribe` | Token | Hapus push subscription |
| GET | `/tenant/info` | Token | Profil tenant: sekolah, paket, status langganan (§9) |
| GET | `/tenant/modules` | Token | Daftar modul aktif tenant → show/hide menu PWA (§9) |
| GET | `/billing/subscription` | Token `admin_sekolah`/`kepala_sekolah` | Paket, modul & kuota tenant (§11) |
| GET | `/billing/invoices` | Token `admin_sekolah`/`kepala_sekolah` | Daftar invoice langganan (§11) |
| GET | `/billing/invoices/{id}` | Token `admin_sekolah`/`kepala_sekolah` | Detail invoice + link bayar (§11) |
| POST | `/webhooks/subscriptions` | Token `admin_sekolah` (Pro+) | Daftarkan outbound webhook sekolah (§11.4) |
| GET | `/webhooks/subscriptions` | Token `admin_sekolah` (Pro+) | Daftar webhook milik tenant (§11.5) |
| DELETE | `/webhooks/subscriptions/{id}` | Token `admin_sekolah` (Pro+) | Hapus langganan webhook (§11.6) |
| GET | `/platform/tenants` | Token `platform_admin` | Daftar tenant + filter status langganan (internal, §10) |
| POST | `/platform/tenants` | Token `platform_admin` | Provisioning tenant manual (internal, §10) |
| POST | `/platform/tenants/{id}/suspend` | Token `platform_admin` | Suspend/unsuspend tenant (internal, §10) |
| POST | `/platform/tenants/{id}/impersonate` | Token `platform_admin` | Token impersonasi sementara (internal, §10) |
| GET | `/platform/subscriptions` | Token `platform_admin` | Langganan bermasalah (internal, §10) |
| GET | `/platform/metrics` | Token `platform_admin` | MRR, churn, konversi trial (internal, §10) |

---

## 7. Daftar Kode Error

### 7.1 Kode HTTP Standar

| HTTP | Arti | Contoh respons |
|---|---|---|
| 400 | Bad Request | Parameter tidak valid di luar skema validasi |
| 401 | Unauthorized | Token hilang/kedaluwarsa → `code: UNAUTHENTICATED`, pesan: "Sesi berakhir, silakan login kembali." |
| 403 | Forbidden | Peran tidak berhak / bukan anaknya / data sekolah lain |
| 402 | Payment Required | Langganan tenant bermasalah (trial kedaluwarsa / past_due / suspended) → `code: LANGGANAN_BERMASALAH` |
| 403 | Forbidden (modul) | Endpoint milik modul add-on yang tidak aktif untuk tenant ini → `code: MODULE_DISABLED` |
| 404 | Not Found | Resource tidak ada |
| 409 | Conflict | Aksi konflik (cabut perangkat sendiri) |
| 422 | Unprocessable Entity | Validasi gagal (`errors` per field) |
| 429 | Too Many Requests | Rate limit; header `Retry-After` |
| 500 | Internal Server Error | `code: SERVER_ERROR`; detail disembunyikan, tercatat di log |

Contoh 403 (wali mengakses anak orang lain):

```json
{
  "success": false,
  "message": "Anda tidak memiliki akses ke data siswa ini.",
  "errors": null,
  "code": "BUKAN_ANAK_ANDA"
}
```

### 7.2 Kode Bisnis (field `code`)

| Kode | HTTP | Arti |
|---|---|---|
| `OTP_SALAH` | 422 | Kode OTP tidak cocok |
| `OTP_KEDALUWARSA` | 422 | OTP lewat 5 menit / sudah dipakai |
| `OTP_RATE_LIMITED` | 429 | Minta OTP terlalu sering |
| `NOMOR_TIDAK_TERDAFTAR` | 422 | Nomor WA tidak terdaftar sebagai siswa/wali |
| `KREDENSIAL_SALAH` | 401 | Username/password salah |
| `AKUN_TERKUNCI` | 423 | Terkunci setelah 5x gagal login |
| `AKUN_NONAKTIF` | 403 | Akun dinonaktifkan admin |
| `NILAI_BELUM_DIRILIS` | 200* | Nilai belum dirilis wali kelas (info, bukan error) |
| `ANAK_TIDAK_DITEMUKAN` | 404 | `siswa_id` tidak ada |
| `BUKAN_ANAK_ANDA` | 403 | `siswa_id` bukan anak dari wali pemilik token |
| `PERANGKAT_TIDAK_DITEMUKAN` | 404 | Device ID tidak milik user |
| `TIDAK_BISA_CABUT_DIRI_SENDIRI` | 409 | Mencabut perangkat yang sedang dipakai |
| `SUBSCRIPTION_TIDAK_DITEMUKAN` | 404 | Push endpoint tidak terdaftar |
| `SEKOLAH_TIDAK_DIIZINKAN` | 403 | Filter `sekolah_id` di luar sekolah anak/wali |
| `LANGGANAN_BERMASALAH` | 402 | Paket trial kedaluwarsa / pembayaran tertunggak / tenant di-suspend |
| `MODULE_DISABLED` | 403 | Endpoint milik modul add-on yang tidak aktif untuk tenant ini |
| `TENANT_TIDAK_DITEMUKAN` | 404 | Subdomain/token tidak memetakan ke tenant yang valid |
| `UNAUTHENTICATED` | 401 | Token tidak valid/kedaluwarsa |
| `SERVER_ERROR` | 500 | Kesalahan server |

> \* `NILAI_BELUM_DIRILIS` dikembalikan dalam respons **sukses** (HTTP 200) via `meta.code` agar PWA menampilkan status "belum dirilis" bukan layar error — sesuai FR-707.

---

## 8. Catatan Keamanan

1. **Scoping tenant otomatis dari token.** Klien **tidak pernah** mengirim `school_id`; sekolah ditentukan dari relasi user→siswa/wali→sekolah di server. Global Scope Eloquent + Policy memfilter setiap query.
2. **Orang tua hanya akses anaknya sendiri.** Setiap endpoint `/ortu/anak/{siswa_id}/*` memverifikasi relasi `guardian_student`; gagal → `403 BUKAN_ANAK_ANDA`. Uji otomatis: wali A tidak bisa membaca `siswa_id` milik wali B.
3. **Siswa hanya akses datanya sendiri.** Tidak ada parameter ID siswa di modul §3 — identitas diambil dari token.
4. **Audit log akses sensitif.** Setiap akses ke data nilai, absensi, dan profil dicatat (user, waktu, IP, resource) dengan retensi ≥ 2 tahun (PRD §9).
5. **Token & perangkat.** Token Sanctum berumur 30 hari, diperpanjang otomatis saat dipakai (*sliding*); tidak ada password/OTP yang disimpan di client; pencabutan per perangkat tersedia (§2.7, FR-1306).
6. **Transport & rate limit.** Wajib HTTPS (TLS 1.2+); rate limit §1.6 + proteksi brute force login (FR-903).
7. **Data minimal (least privilege).** Endpoint hanya mengembalikan field yang dibutuhkan PWA — NIK, alamat lengkap, dan data PII lain **tidak** diekspos via API ini.
8. **Nilai terkunci rilis.** API menolak menyajikan nilai yang belum ada di `grade_releases`; tidak ada flag "preview" yang bisa diakali klien.
9. **Token platform terpisah.** Token operator SaaS (`platform_admin`) diterbitkan dari guard `platform` yang terpisah dan **tidak** berlaku untuk endpoint tenant, begitu pula sebaliknya. Pembedaan ditegakkan di level middleware.
10. **Impersonasi berlabel & beraudit.** Token impersonasi memuat klaim `impersonated_by` (id operator) + masa berlaku ≤ 60 menit; web/PWA menampilkan banner "mode impersonasi"; setiap request tercatat di audit log dengan kedua identitas (operator + tenant).
11. **Rate limit platform diperketat.** Endpoint `/platform/*`: 30 request/menit per token + IP allowlist opsional; 3x kegagalan auth → blokir IP 15 menit + alert ke tim platform.
12. **Webhook payment gateway.** `POST /webhooks/payment/{provider}` **tanpa** Bearer token — hanya verifikasi signature HMAC dari provider + idempotency key; respons 200 cepat dan pemrosesan via queue. Endpoint ini tidak tercantum di daftar endpoint publik.

---

## 9. API Tenant & Modul (untuk PWA)

Dipakai PWA saat boot untuk branding dinamis (FR-1301) dan show/hide menu sesuai modul aktif tenant.

### 9.1 Info Tenant — `GET /tenant/info`

**Auth:** Bearer token (peran apa pun). Tanpa parameter.

Contoh respons (200):

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "tenant": {
      "id": 3,
      "nama": "SMA Negeri 1 Bandung",
      "slug": "sman1bdg",
      "npsn": "20100123",
      "jenjang": "SMA",
      "logo_url": "https://cdn.sims.example.com/logo/sman1bdg.png",
      "warna_tema": "#1B5E20",
      "subdomain": "sman1bdg.sims.id",
      "custom_domain": null,
      "telepon": "(022) 1234567",
      "alamat": "Jl. Ir. H. Juanda No. 93, Bandung"
    },
    "langganan": {
      "paket": "Pro",
      "status": "active",
      "trial_berakhir_pada": null,
      "periode_berikutnya": "2026-11-09",
      "kuota": {
        "siswa_aktif": { "terpakai": 342, "batas": 500 },
        "pengguna_staf": { "terpakai": 48, "batas": 100 },
        "penyimpanan_gb": { "terpakai": 12.4, "batas": 50 }
      }
    }
  },
  "meta": null
}
```

> `status` ∈ `trial | active | past_due | suspended`. PWA menampilkan banner lembut bila `past_due` ("Langganan sekolah perlu perhatian — hubungi admin sekolah").

### 9.2 Daftar Modul Aktif — `GET /tenant/modules`

**Auth:** Bearer token. PWA memakai daftar ini untuk menampilkan/menyembunyikan menu (mis. menu Keuangan hanya bila modul `keuangan` aktif).

Contoh respons (200):

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "modul": [
      { "kode": "akademik", "nama": "Akademik Inti", "aktif": true, "versi": "1.0" },
      { "kode": "pwa", "nama": "PWA Siswa & Orang Tua", "aktif": true, "versi": "1.0" },
      { "kode": "notifikasi", "nama": "Notifikasi WA & Push", "aktif": true, "versi": "1.0" },
      { "kode": "erapor", "nama": "E-Rapor", "aktif": true, "versi": "1.0" },
      { "kode": "keuangan", "nama": "Keuangan & SPP", "aktif": false, "versi": null },
      { "kode": "ppdb", "nama": "PPDB Online", "aktif": false, "versi": null },
      { "kode": "perpustakaan", "nama": "Perpustakaan", "aktif": false, "versi": null }
    ]
  },
  "meta": null
}
```

> Bila klien memanggil endpoint modul yang non-aktif → `403 MODULE_DISABLED` (§7). Daftar ini di-cache 15 menit di server; perubahan paket/add-on berlaku maksimal 15 menit kemudian di PWA.

---

## 10. API Platform (internal — operator SaaS)

> **Internal, tidak untuk PWA.** Prefix `/api/v1/platform`. Auth: Bearer token guard `platform` dengan peran `platform_admin` — terpisah dari token tenant (§8). Seluruh aksi tercatat di audit log platform.

### 10.1 Daftar Tenant — `GET /platform/tenants`

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `status_langganan` | query | string | Tidak | `trial` / `active` / `past_due` / `suspended` |
| `q` | query | string | Tidak | Cari nama / NPSN / slug |
| `page`, `per_page` | query | integer | Tidak | Paginasi standar |

Contoh respons (200) — ringkas:

```json
{
  "success": true,
  "message": "OK",
  "data": [
    {
      "id": 3, "nama": "SMA Negeri 1 Bandung", "slug": "sman1bdg", "jenjang": "SMA",
      "paket": "Pro", "status_langganan": "active",
      "siswa_aktif": 342, "mrr_idr": 999000,
      "trial_berakhir_pada": null, "dibuat_pada": "2026-08-01T10:00:00+07:00"
    },
    {
      "id": 9, "nama": "SD Pelita Bangsa", "slug": "sdpelitabangsa", "jenjang": "SD",
      "paket": "Basic", "status_langganan": "past_due",
      "siswa_aktif": 187, "mrr_idr": 500000,
      "trial_berakhir_pada": null, "dibuat_pada": "2026-09-12T09:30:00+07:00"
    }
  ],
  "meta": { "current_page": 1, "per_page": 20, "total": 48, "last_page": 3 }
}
```

### 10.2 Provisioning Tenant — `POST /platform/tenants`

Body: `nama`, `slug` (unik → dipakai untuk subdomain), `jenjang`, `npsn`, `email_pic`, `no_wa_pic`, `paket` (`basic`/`pro`/`enterprise`), `modul[]` (kode modul aktif awal), `trial_hari` (default 30). Respons 201: data tenant + kredensial admin awal (ditampilkan **satu kali**) + checklist onboarding.

### 10.3 Suspend / Unsuspend — `POST /platform/tenants/{id}/suspend`

Body: `aksi` (`suspend`|`unsuspend`), `alasan` (wajib). Saat suspend: seluruh token tenant dinonaktifkan dan API menjawab 402; data **tidak** dihapus — masuk siklus retensi PRD §17.6 (read-only 14 hari → purge T+30; arsip fiskal dikecualikan).

### 10.4 Impersonasi — `POST /platform/tenants/{id}/impersonate`

Body: `tujuan` (mis. `troubleshooting tiket #1234`), `durasi_menit` (maks 60). Respons 200: token sementara berlabel `impersonated_by` + `expires_at`. Setiap request-nya beraudit ganda (operator + tenant).

### 10.5 Langganan Bermasalah — `GET /platform/subscriptions?status=past_due`

Daftar tenant berstatus `past_due`/`suspended` + umur tunggakan + nilai invoice — dipakai tim billing untuk penagihan.

### 10.6 Metrik Platform — `GET /platform/metrics`

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `periode` | query | string | Tidak | Format `YYYY-MM`; default bulan berjalan |

Contoh respons (200):

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "periode": "2026-10",
    "mrr_idr": 42500000,
    "tenant_aktif": 46,
    "trial_berjalan": 9,
    "konversi_trial_persen": 55.6,
    "churn_persen": 2.1,
    "arpu_idr": 923913
  },
  "meta": null
}
```

---

## 11. API Billing (sekolah)

Dikonsumsi **web admin sekolah** (bukan PWA siswa/orang tua). Auth: Bearer token peran `admin_sekolah`/`kepala_sekolah`; tenant disimpulkan dari token (§1.8).

### 11.1 Langganan Saya — `GET /billing/subscription`

Contoh respons (200):

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "paket": { "kode": "pro", "nama": "Pro", "harga_idr_per_bulan": 999000 },
    "status": "active",
    "periode_aktif": { "mulai": "2026-10-09", "selesai": "2026-11-08" },
    "modul": [
      { "kode": "akademik", "nama": "Akademik Inti", "termasuk_paket": true },
      { "kode": "erapor", "nama": "E-Rapor", "termasuk_paket": true },
      { "kode": "keuangan", "nama": "Keuangan & SPP", "termasuk_paket": false, "harga_addon_idr_per_bulan": 500000 }
    ],
    "kuota": {
      "siswa_aktif": { "terpakai": 342, "batas": 500 },
      "pengguna_staf": { "terpakai": 48, "batas": 100 },
      "penyimpanan_gb": { "terpakai": 12.4, "batas": 50 }
    },
    "pembayaran_berikutnya": { "jatuh_tempo": "2026-11-08", "jumlah_idr": 999000 }
  },
  "meta": null
}
```

### 11.2 Daftar Invoice — `GET /billing/invoices`

Paginasi standar. Tiap item: `id`, `nomor`, `periode`, `jumlah_idr`, `status` (`paid`/`unpaid`/`overdue`), `jatuh_tempo`.

### 11.3 Detail Invoice — `GET /billing/invoices/{id}`

Contoh respons (200) — invoice belum dibayar:

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "id": 881,
    "nomor": "INV/2026/X/0881",
    "periode": "9 Okt – 8 Nov 2026",
    "rincian": [
      { "item": "Paket Pro", "jumlah_idr": 999000 },
      { "item": "Add-on E-Rapor", "jumlah_idr": 0 }
    ],
    "jumlah_idr": 999000,
    "status": "unpaid",
    "jatuh_tempo": "2026-11-08",
    "link_bayar": "https://pay.sims.id/inv/INV-2026-X-0881?sig=abc123",
    "metode_tersedia": ["qris", "va_bca", "va_mandiri", "ewallet"]
  },
  "meta": null
}
```

> Pembayaran diproses via payment gateway; status invoice diperbarui lewat webhook `POST /webhooks/payment/{provider}` (§8 poin 12).

---

### 11.4 Langganan Webhook — `POST /webhooks/subscriptions`

Mendaftarkan URL sekolah untuk menerima **outbound webhook** (event dari SIMS ke sistem sekolah). Fitur paket **Pro+**; mendukung US-112.

**Auth:** Bearer <redacted> (`admin_sekolah`).

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `url` | body | string (url https) | Ya | Endpoint penerima di server sekolah |
| `event` | body | string | Ya | Salah satu: `siswa_baru`, `pembayaran_lunas`, `nilai_dirilis`, `pengumuman_baru` |
| `secret` | body | string | Tidak | Secret untuk verifikasi signature; bila kosong, dibuatkan otomatis dan hanya ditampilkan sekali |

Setiap pengiriman menyertakan header `X-Sims-Signature: sha256=<hmac>` (HMAC-SHA256 dari body dengan secret) dan `X-Sims-Event`. Kegagalan dikirim ulang dengan backoff (maks. 5x dalam 24 jam), lalu ditandai `gagal` di log.

Respons (201): `{success:true, message:"Webhook terdaftar.", data:{id, url, event, aktif:true}}`. Error: `403` (`FITUR_TIDAK_TERSEDIA_DI_PAKET`) bila paket Basic; `422` bila URL tidak https / event tidak dikenal.

### 11.5 Daftar Webhook — `GET /webhooks/subscriptions`

**Auth:** Bearer <redacted> (`admin_sekolah`).

Respons (200): daftar langganan webhook milik tenant — `{success:true, data:[{id, url, event, aktif, terakhir_terkirim_pada, gagal_beruntun}]}`. Secret **tidak pernah** dikembalikan (hanya bisa di-regenerate).

### 11.6 Hapus Webhook — `DELETE /webhooks/subscriptions/{id}`

**Auth:** Bearer <redacted> (`admin_sekolah`).

| Parameter | Lokasi | Tipe | Wajib | Keterangan |
|---|---|---|---|---|
| `id` | path | integer | Ya | ID langganan webhook |

Respons (200): `{success:true, message:"Langganan webhook dihapus."}`. Error: `404` (`WEBHOOK_TIDAK_DITEMUKAN`) bila id bukan milik tenant ini.

---

## 12. Changelog Dokumen

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0 | 9 Oktober 2026 | Rilis awal: Auth OTP, modul Siswa, modul Orang Tua, modul Umum, error code, catatan keamanan |
| 1.1 | 9 Oktober 2026 | Pivot SaaS: tenant resolution (§1.8), API Tenant & Modul (§9), API Platform internal (§10), API Billing (§11), error 402 & `MODULE_DISABLED` (§7), penguatan keamanan token platform & webhook (§8) |
| 1.2 | 9 Oktober 2026 | Tutup gap audit Dok 08: `POST /auth/password` (§2.8), outbound webhook sekolah (§11.4–11.6), kebijakan depresiasi API (§13); perbaikan penomoran subsection §9–11. Catatan: endpoint passkey/WebAuthn & magic link (Fase 2) didefinisikan di Dok 09 §8, belum masuk spec ini |

*Perubahan breaking pada endpoint menaikkan versi mayor path (`/api/v2`); perubahan non-breaking (field tambahan opsional) dicatat di sini tanpa ganti versi path.*

---

## 13. Kebijakan Depresiasi API

Aturan main evolusi API agar integrator (sekolah, partner) punya kepastian (menutup gap T-02):

1. **Versioning di path**: versi mayor baru memakai path baru (`/api/v2`, `/api/v3`, …). Versi minor/perbaikan tidak mengubah path.
2. **Dukungan N-1**: setiap versi mayor didukung **minimal 12 bulan** setelah versi mayor berikutnya rilis stabil. Contoh: v1 tetap hidup 12 bulan sejak v2 GA.
3. **Pengumuman breaking change**: diumumkan **H-90** melalui changelog ini, email ke admin tenant, dan pengumuman di dashboard platform. Tidak ada breaking change diam-diam.
4. **Header `Sunset`**: respons dari endpoint yang dijadwalkan pensiun menyertakan `Sunset: <tanggal RFC 7231>` dan `Deprecation: true` mulai H-90.
5. **Non-breaking = tanpa ganti versi**: field respons tambahan (opsional), endpoint baru, dan parameter opsional baru boleh masuk ke versi berjalan; perubahan tipe/hapus field wajib naik versi mayor.
6. **Dokumentasi migrasi**: setiap rilis mayor disertai panduan migrasi vN → vN+1 (daftar endpoint berubah + contoh sebelum/sesudah).
