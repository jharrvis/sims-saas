# Changelog SIMS SaaS

Semua perubahan penting pada proyek ini dicatat di sini, dalam Bahasa Indonesia.
Format mengikuti [Keep a Changelog](https://keepachangelog.com/id/1.1.0/).

## [Unreleased]

### Ditambahkan
- Autentikasi: login WA OTP (rate limit Dok 09 §3.1), MFA TOTP wajib untuk peran platform (FR-904), tenant switcher, API v1 auth
- RBAC: 54 permission (`{resource}.{aksi}`) + 9 peran tenant sesuai matriks Dok 05 via Spatie teams; middleware `module` → 403 `MODULE_DISABLED` untuk add-on nonaktif
- Kesiswaan: CRUD siswa + soft delete, NIS unik per sekolah, profil 360° (wali, riwayat kelas, kehadiran, nilai), UI Livewire + API v1
- Kepegawaian: CRUD guru/pegawai (NIP/NUPTK, jenis, status kepegawaian), UI + API v1
- Kelas: rombel + wali kelas (FR-303), penempatan siswa dengan riwayat pindah (FR-102, partial unique index)
- Akademik: mata pelajaran + KKM, jadwal mingguan dengan deteksi bentrok guru & ruang (FR-502), absensi harian H/I/S/A + kunci (FR-604) + rekap
- Nilai: semester, komponen berbobot, input nilai (validasi 0–100), nilai akhir berbobot, leger (FR-706), rapor (FR-705), rilis nilai ke PWA (FR-707)
- PPDB Online (add-on): periode, formulir pendaftar, verifikasi berkas, seleksi (tes + wawancara), pengumuman kelulusan, daftar ulang → otomatis menjadi data siswa; di belakang feature flag `module:ppdb` → 403 `MODULE_DISABLED`
- Keuangan SPP (add-on): jenis tagihan, generate tagihan massal per periode, pembayaran bertahap + kuitansi otomatis, status lunas/sebagian/jatuh tempo, ringkasan arus kas & tunggakan; di belakang feature flag `module:keuangan`
- `PANDUAN-PENGGUNA.md`: panduan lengkap berbahasa Indonesia untuk pengguna akhir (login WA OTP → semua modul)
- Pusat Bantuan in-app (`/bantuan`): panduan end-user di-render dari `docs/bantuan/*.md` menjadi HTML dengan layout aplikasi (tanpa toolchain Node/Docusaurus); tombol "?" kontekstual di halaman modul
- Pengumuman (add-on): pengumuman tersegmentasi (semua/peran/kelas), draf → tayang, tanda penting, periode tayang; di belakang feature flag `module:pengumuman`; API v1 untuk PWA
- Laporan: 6 laporan bawaan (siswa, guru, kelas, mutasi, absensi, nilai) + filter + ekspor CSV berkode UTF-8 (FR-1001/1002); UI Livewire + API v1
- Pengaturan: profil sekolah (nama, NPSN, alamat, kontak), tahun ajaran (tambah + aktifkan), toggle modul add-on per sekolah; UI tab + API v1
- E-Rapor (add-on): rapor cetak berkop sekolah (nilai + predikat + rata-rata + rekap absensi + catatan wali kelas + tanda tangan), catatan wali per siswa/semester; di belakang feature flag `module:e_rapor`
- Tata Tertib (add-on): master jenis pelanggaran (ringan/sedang/berat + bobot poin), pencatatan pelanggaran per siswa, rekap poin tertinggi; di belakang feature flag `module:tata_tertib`
- BK & Konseling (add-on): penjadwalan sesi konseling (individu/kelompok/klasikal), catatan rahasia + tindak lanjut, status terjadwal/selesai/batal; di belakang feature flag `module:bk_konseling`
- Perpustakaan Digital (add-on): katalog buku + stok tersedia, peminjaman dengan batas stok, pengembalian otomatis hitung denda keterlambatan; di belakang feature flag `module:perpustakaan`
- Ekstrakurikuler & Prestasi (add-on): daftar ekskul + kelola anggota, pencatatan prestasi berjenjang (sekolah → internasional); di belakang feature flag `module:ekstrakurikuler`
- LMS Mini / Tugas Online (add-on): pembuatan tugas per mapel/kelas, pengumpulan jawaban, penilaian + feedback; di belakang feature flag `module:lms_mini`
- Notifikasi Terjadwal / Broadcast (add-on): broadcast WA & notifikasi in-app ke siswa/guru/kelas, kirim langsung atau terjadwal (scheduler `broadcast:proses`), log per penerima; di belakang feature flag `module:broadcast`
- Inventaris & Aset (add-on): kategori, master aset (kode unik/tenant, kondisi, lokasi), riwayat mutasi (masuk/pindah/kondisi/keluar) yang otomatis memperbarui lokasi & kondisi; di belakang feature flag `module:inventaris`
- UKS & Kesehatan (add-on): kunjungan UKS (keluhan, diagnosa, tindakan, suhu, rujukan), stok & pemakaian obat otomatis mengurangi stok; di belakang feature flag `module:uks`
- Antar-Jemput / Transportasi (add-on): rute + sopir/kendaraan/kapasitas, titik penjemputan terurut, penumpang dengan batas kapasitas; di belakang feature flag `module:transportasi`
- Kantin / Koperasi Digital (add-on): produk + stok, kasir POS (keranjang, bayar, kembalian), checkout transaksional anti-oversell, riwayat + omzet harian; di belakang feature flag `module:kantin`
- Survei & Evaluasi (add-on): survei dengan pertanyaan pilihan ganda/skala/teks, periode buka-tutup, pengisian responden, agregat hasil (distribusi & rata-rata); di belakang feature flag `module:survei`
- Alumni & Karier (add-on): data alumni (taut opsional ke siswa), riwayat karier tracer study (kuliah/kerja/wirausaha), statistik distribusi status; di belakang feature flag `module:alumni`
- Organisasi (add-on): organisasi (OSIS/MPK/Pramuka/…), pengurus siswa dengan jabatan anti-duplikat, kegiatan dengan status; di belakang feature flag `module:organisasi`
- Keamanan (audit): `/pilih-sekolah` dipindah ke middleware `auth` (sebelumnya di `guest` sehingga tidak terjangkau user login); dashboard `/` kini wajib `auth`; middleware baru `mfa` menegakkan FR-904 — akun platform tanpa MFA terkonfirmasi dipaksa ke `2fa/setup`, dan sesi tanpa verifikasi MFA (mis. via remember-me) dipaksa ke `mfa.verify`; tambah tautan "Ganti Sekolah" di navigasi untuk akun multi-sekolah
- PWA Siswa & Orang Tua (`/pwa`, Dok 07 P-01 s.d. P-06): login WA OTP dengan penautan otomatis ke siswa/wali via nomor WA (+ membership & peran siswa/orang-tua); beranda peran-aware; Nilai (hanya yang dirilis, FR-707); Jadwal seminggu; Absensi + rekap; Pengumuman; Tagihan (read-only); Profil & perangkat + mode profil anak untuk orang tua multi-anak (tercatat di audit, Dok 09 §3.4); manifest + service worker + ikon agar installable; tamu PWA diarahkan ke login PWA
- QR Login siswa (Dok 09 §3.3): kartu QR cetak per siswa (token 128-bit, berlaku satu semester, terbit/cabut/cetak per siswa & massal per rombel) + tantangan QR dinamis 60 detik sekali pakai untuk layar kelas; pemindai QR di halaman login PWA (perangkat bersama) → sesi 8 jam tanpa remember-me & tanpa eskalasi hak (middleware `qr.limit` memaksa logout saat kedaluwarsa/dicabut); API Dok 09 §8 (`auth/qr/challenge`, `auth/qr/redeem` → token Sanctum 8 jam, `auth/qr/revoke-card`); pencarian identitas global eksplisit untuk tamu (tanpa tenant); middleware `tenant.resolved` melengkapi resolusi tenant untuk Bearer token (auth:sanctum berjalan setelah middleware global)
- Push notification PWA (Dok 03 §5.3/5.4, FR-1304): langganan Web Push per perangkat (VAPID, tabel `push_subscriptions`); endpoint API `push/subscribe` (idempoten, 201/200) & `push/unsubscribe` (404 `SUBSCRIPTION_TIDAK_DITEMUKAN`) + versi web PWA; pemicu otomatis via antrean: pengumuman tayang (mengikuti target segmen), nilai dirilis, tagihan baru, siswa alpa; service worker menampilkan notifikasi & membuka halaman terkait saat diketuk; pengaturan izin di halaman Profil PWA; subscription kedaluwarsa (404/410) dibersihkan otomatis
- Panel Operator Platform (`/platform`, khusus akun `is_platform`): dashboard metrik (total tenant, MRR, trial segera berakhir, piutang), kelola tenant (cari/filter/suspend/aktifkan dengan audit), detail tenant (langganan, modul, kuota & override, tagihan), CRUD paket langganan (Basic/Pro/Enterprise, harga disetujui 9 Okt 2026), daftar langganan (filter `past_due`), tagihan langganan manual + tandai lunas, katalog modul, audit log lintas tenant. Middleware `platform` + permission granular `platform.*` (guard `platform`, peran `platform-admin`); pivot Spatie teams memakai sentinel team 0 untuk konteks tanpa tenant; `User::guardName()` mendukung guard `web` + `platform`. Tabel baru: `plans`, `subscriptions`, `subscription_items`, `billing_invoices` (deviasi Dok 02: `invoices` sudah dipakai tagihan SPP), `tenant_usages`, `tenant_quota_overrides`; command `platform:make-admin`
- Onboarding mandiri (`/daftar`, publik): formulir pendaftaran sekolah → provisioning otomatis (sekolah + langganan trial 30 hari paket Basic + akun admin + membership + peran superadmin + modul inti aktif) via `TenantProvisioningService`; validasi NPSN unik & nomor WA format 62
- `QuotaService`: evaluasi batas (override → paket → trial); trial dibatasi 100 siswa / 10 staf / 1 GB, tanpa broadcast & API (PRD §9.3/§9.5); enforcement di form tambah siswa dan pembuatan/pengiriman broadcast; override diabaikan saat trial
- Pembayaran langganan via **Midtrans** (keputusan pemilik produk 10 Okt 2026, menggantikan Xendit di Dok 11): `midtrans/midtrans-php`, `config/midtrans.php`, kolom `midtrans_order_id`/`snap_token`/`payment_url` di `billing_invoices`; `MidtransService` (Snap redirect, verifikasi signature SHA512, idempoten); webhook `POST /api/v1/webhooks/midtrans` → settlement melunasi tagihan + mengaktifkan tenant `past_due`, expire → overdue; halaman **Langganan** sekolah (`/langganan`, permission `langganan.view`/`langganan.bayar`) untuk lihat paket & bayar tagihan; topik bantuan `langganan.md`
- Siklus hidup langganan otomatis (`DunningService` + command `subscription:lifecycle`, scheduler harian 08:00): pengingat WA H-7/H-3/H-1 sebelum trial berakhir (anti-kirim-ganda via tabel `dunning_logs`), trial kedaluwarsa → `past_due`, `past_due` > 7 hari → suspend otomatis, tagihan `issued` lewat jatuh tempo → `overdue` + pengingat, tagihan perpanjangan otomatis H-7 sebelum periode berakhir (anti-duplikat); `WaSender::sendText()` (+ `LogWaSender` & fake di test); `$by` nullable di `PlatformService` untuk konteks console
- Pengiriman WA via **Fonnte** (keputusan pemilik produk 12 Okt 2026): kontrak `WaSender` + `sendText()`, `FonnteWaSender` (POST `api.fonnte.com/send`, header `Authorization`, field `target`/`message`), binding via `WA_DRIVER` (`log`/`fonnte`) di `config/wa.php`; `FONNTE_TOKEN` diisi saat deploy
- `DEPLOY.md`: panduan deploy production di **HestiaCP** (keputusan 12 Okt 2026) — domain + PHP 8.3 + SSL, database, clone kode, checklist secret `.env`, composer/migrate/cache, cron `schedule:run` + `queue:work --stop-when-empty` tiap menit, registrasi webhook Midtrans, verifikasi & troubleshooting; catatan file yang harus diupload manual via web GitHub (`composer.lock`, `.github/workflows/ci.yml`)

### Diubah
- **2FA kini opsional (opt-in), default nonaktif untuk semua pengguna** — sebelumnya wajib untuk peran platform (FR-904). Halaman baru **Pengaturan → Keamanan Akun** (`/pengaturan/keamanan`) untuk mengaktifkan/menonaktifkan 2FA; penonaktifan diverifikasi ulang via OTP WhatsApp dan dicatat di audit (`mfa.disable`). Middleware `mfa` hanya menantang verifikasi bila user sudah mengaktifkan 2FA sendiri. Kebijakan wajib-2FA untuk operator platform dapat dihidupkan kembali via env `MFA_REQUIRED_FOR_PLATFORM=true` (`config/mfa.php`)

### Keamanan
- Isolasi tenant fail-closed: query model tenant tanpa konteks melempar exception (diuji)
- Identitas global + membership (ADR-0004); token satu tenant aktif
- NIK wali terenkripsi di database

### Diperbaiki
- Unique constraint `class_student`: diubah menjadi partial unique index (`status='aktif'`) agar riwayat pindah kelas tersimpan — Dok 02 §3.11 diperbarui

## [0.1.0] - 2026-10-10
### Ditambahkan
- Scaffolding Laravel 13 + PHP 8.3 + Pest 4 + Livewire + Sanctum + Spatie Permission (teams)
- Fondasi multi-tenant: `TenantContext`, `SchoolScope`, `BelongsToSchool`, `ResolveTenant`
- Migrasi awal 000001–000027 (identitas, sekolah, membership, audit, OTP, MFA)
- Layout Blade + Tailwind v4 mengikuti design system
- CI: Pint + Larastan level 6
