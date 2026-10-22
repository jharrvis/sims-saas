# Kelola Pengguna

> Buat akun, atur peran, dan aktif/nonaktifkan akses pengguna sekolah

Halaman **Pengaturan → Pengguna** dipakai Admin Sekolah (dan Super Admin) untuk
membuat akun pengguna, menetapkan peran, serta menutup akses pengguna yang sudah
tidak bertugas. Setiap pengguna memiliki satu identitas global, tetapi status dan
perannya diatur **per sekolah**.

## Melihat dan mencari pengguna

1. Buka **Pengaturan → Pengguna**.
2. Gunakan kotak pencarian untuk mencari **nama, username, nomor WhatsApp, atau email**.
3. Saring dengan **Semua peran** atau **Semua status** (Aktif / Nonaktif).
4. Label **Belum bisa masuk** muncul bila pengguna belum punya nomor WhatsApp
   maupun kata sandi.

## Menambah pengguna

1. Klik **Tambah Pengguna**.
2. Isi **Nama Lengkap** dan **Username** (tanpa spasi, dipakai untuk login
   username + kata sandi).
3. Isi minimal salah satu cara masuk:
   - **Nomor WhatsApp** — format `08xx` otomatis diubah menjadi `62xx` dan dipakai
     untuk kode OTP saat login; atau
   - **Kata Sandi** minimal 8 karakter (isi dua kali).
4. Centang minimal satu **Peran**. Peran menentukan menu dan hak akses pengguna.
5. Pilih **Status** di sekolah ini, lalu klik **Buat Pengguna**.

Pengguna dapat langsung masuk setelah akun dibuat. Sampaikan nomor WhatsApp atau
username + kata sandi kepada yang bersangkutan melalui jalur yang aman.

> **Catatan:** siswa dan orang tua biasanya memakai aplikasi PWA (`/pwa`) dan
> akunnya tertaut otomatis saat login dengan nomor WhatsApp. Akun siswa/orang tua
> tidak perlu dibuat manual di halaman ini.

## Mengubah data dan peran

1. Klik ikon **pensil** pada baris pengguna.
2. Ubah nama, username, kontak, atau peran sesuai kebutuhan.
3. Isi **Kata Sandi Baru** hanya bila ingin mereset kata sandi; kosongkan bila
   tidak diubah.
4. Klik **Simpan Perubahan**.

Perubahan **nama, username, kontak, dan kata sandi** berlaku global (untuk semua
sekolah tempat pengguna terdaftar). Halaman akan menampilkan peringatan bila
pengguna juga terdaftar di sekolah lain. **Status dan peran** hanya berlaku untuk
sekolah yang sedang aktif.

## Menonaktifkan akses

- Klik ikon **pengguna dicoret** pada kolom Aksi, lalu konfirmasi.
- Akses pengguna ke sekolah ini ditutup, tetapi identitas dan data historisnya
  tetap tersimpan. Klik ikon yang sama untuk mengaktifkan kembali.
- Anda tidak dapat menonaktifkan akun sendiri.
- Admin terakhir sekolah tidak dapat dinonaktifkan — tetapkan admin lain lebih
  dahulu agar sekolah tidak terkunci.

## Keamanan akun

- Jangan membagikan kata sandi melalui grup atau pesan yang tidak aman.
- Minta pengguna mengganti kata sandi secara berkala melalui admin.
- Aktifkan **2FA** (opsional) dari **Pengaturan → Keamanan Akun** untuk menambah
  lapisan keamanan saat login.
- Semua pembuatan, perubahan, dan perubahan status pengguna dicatat di audit log.

---

Butuh bantuan lain? Buka [Peran & Hak Akses](peran.md) atau hubungi admin sekolah.
