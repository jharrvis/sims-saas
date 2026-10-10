# Keuangan SPP

> Tagihan, pembayaran, dan kuitansi

Menu **Keuangan SPP** (ikon dompet). Alur: **buat jenis tagihan → generate → catat pembayaran**.

### Jenis tagihan
Buat dulu jenisnya (mis. SPP bulanan Rp 500.000, Uang Gedung sekali bayar) via API atau minta admin.

### Generate tagihan massal
1. Di halaman Keuangan, pilih **jenis tagihan** dan isi **periode** (mis. `2026-07`).
2. Klik **Generate** — tagihan dibuat otomatis untuk **semua siswa aktif**. Tagihan yang sudah ada tidak diduplikat.

### Mencatat pembayaran
1. Cari tagihan siswa, klik **Bayar**.
2. Isi nominal (tidak boleh melebihi sisa), tanggal, dan metode (Tunai/Transfer/QRIS/VA/E-Wallet).
3. Klik **Simpan Pembayaran** — **nomor kuitansi dibuat otomatis** (`KWT/2026/00001`).
4. Status tagihan berubah otomatis: Belum Bayar → Sebagian → Lunas.

### Memantau tunggakan
Kartu ringkasan di atas halaman menampilkan total tagihan, terkumpul, tunggakan (Rp), dan jumlah tagihan belum lunas. Saring status **Jatuh Tempo** untuk daftar penagihan.

---
