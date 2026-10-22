# Peran & Hak Akses

> Daftar peran, mengatur hak akses, dan membuat peran kustom per sekolah

Peran menentukan menu dan tindakan yang boleh dilakukan seorang pengguna.
Pengaturan peran bersifat **per sekolah** — perubahan di satu sekolah tidak
memengaruhi sekolah lain.

## Peran bawaan

| Peran | Bisa melakukan |
|---|---|
| **Super Admin** | Semua: seluruh modul, pengguna, dan peran (selalu penuh, read-only) |
| **Admin Sekolah** | Semua operasional: siswa, guru, kelas, jadwal, absensi, nilai, PPDB, keuangan, pengguna, pengaturan |
| **Kepala Sekolah** | Melihat semua + rilis nilai + setujui PPDB |
| **Tata Usaha** | Input siswa & pendaftar PPDB, verifikasi berkas, lihat jadwal/absensi/nilai |
| **Wali Kelas** | Absensi + kunci absensi kelas binaannya, rilis nilai, lihat siswa binaannya |
| **Guru Mapel** | Lihat jadwal, input absensi & nilai untuk mapel ampuannya |
| **Guru BK** | Lihat data siswa & nilai, catatan layanan BK |
| **Siswa / Orang Tua** | (Aplikasi PWA) Lihat nilai & absensi **yang sudah dirilis** |

Jika ada menu yang tidak muncul, berarti peran Anda tidak memiliki hak untuk itu —
hubungi admin sekolah.

## Membuka kelola peran

1. Buka **Pengaturan → Peran & Akses**.
2. Tabel menampilkan nama peran, deskripsi, jenis (**Bawaan** / **Kustom**),
   jumlah pengguna, dan jumlah hak akses.
3. Klik ikon **pensil** untuk mengatur hak akses.

## Mengatur hak akses

1. Pada halaman ubah peran, hak akses dikelompokkan per menu (mis. Data Siswa,
   Nilai, Keuangan).
2. Centang hak akses yang diizinkan. Gunakan **Pilih** / **Kosongkan** pada tiap
   kelompok, atau **Pilih semua** / **Kosongkan semua**.
3. Klik **Simpan Hak Akses**. Perubahan langsung berlaku untuk semua pengguna
   dengan peran tersebut.

> **Super Admin** bersifat tetap dan selalu memiliki seluruh hak akses. Peran ini
> tidak dapat diubah agar sekolah tidak kehilangan akses penuh.

## Membuat peran kustom

1. Klik **Buat Peran**.
2. Isi nama peran (huruf kecil, angka, tanda hubung; contoh `operator-ppdb`).
3. Centang hak akses yang dibutuhkan, lalu klik **Buat Peran**.
4. Tetapkan peran baru ke pengguna melalui **Pengaturan → Pengguna**.

Peran kustom dapat diubah namanya dan dihapus selama **belum dipakai** pengguna.
Peran bawaan tidak dapat dihapus atau diganti namanya, tetapi hak aksesnya masih
dapat disesuaikan.

## Hal yang perlu diingat

- Satu pengguna dapat memiliki lebih dari satu peran; hak aksesnya merupakan
  gabungan semua peran tersebut.
- Menghapus peran tidak menghapus pengguna. Pindahkan pengguna ke peran lain
  terlebih dahulu.
- Setiap perubahan peran dan hak akses dicatat di audit log.
- Setelah mengubah hak akses, minta pengguna login ulang bila menunya belum
  berubah.

---

Lihat juga: [Kelola Pengguna](pengguna.md).
