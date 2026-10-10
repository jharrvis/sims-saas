# QR Login Siswa

> Masuk PWA tanpa mengetik — untuk siswa yang belum punya HP, lewat perangkat bersama (tablet kelas, komputer perpustakaan)

## Cara kerja

1. Sekolah menerbitkan **kartu QR** untuk tiap siswa (menu **QR Login**).
2. Kartu dicetak dan dibagikan ke siswa — berlaku **satu semester**.
3. Di perangkat bersama, buka halaman login PWA → tab **Pindai QR** → arahkan kamera ke kartu.
4. Siswa masuk otomatis sebagai dirinya. Sesi perangkat bersama berlaku **8 jam**, tanpa "ingat saya", dan hanya bisa melihat (jadwal, nilai, absensi, pengumuman) — tidak bisa mengubah data atau naik hak akses.

## Menerbitkan kartu

- **Per siswa**: buka **QR Login → Kartu**, cari siswa, tekan **Terbitkan**. Kode QR tampil sekali — langsung **Cetak** atau simpan.
- **Massal per rombel**: pilih rombel, tekan **Terbitkan & Cetak Rombel**. Semua kartu lama rombel tersebut dinonaktifkan (cocok untuk cetak ulang tiap semester).

## Kartu hilang

Tekan **Cabut** pada kartu siswa. Kartu tidak bisa dipakai lagi dan **semua sesi QR siswa tersebut ikut berakhir**.

## Tantangan QR (layar kelas)

Untuk siswa yang kartunya tertinggal: buka **QR Login → Tantangan** (atau tombol di halaman yang sama), pilih siswa, tekan **Buat Kode Baru**. Tampilkan kode di layar kelas — siswa memindainya dari perangkat bersama. Kode berlaku **60 detik**, **sekali pakai**, dan diperbarui otomatis.

## Keamanan

- Token 128-bit acak; yang disimpan hanya hash-nya.
- Sesi QR kedaluwarsa (8 jam) atau kartunya dicabut → logout otomatis.
- Setiap penukaran QR tercatat di log audit.

---

