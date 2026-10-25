# Runbook: Restore Data Satu Tenant (Shared-Schema DB)

- **Pemilik:** DevOps / Tech Lead
- **Terakhir diuji:** — (jadwalkan drill kuartalan, PRD §17.4)
- **Terkait:** PRD §17.5, T-01 (Dok 08), ADR-0002

> Keterbatasan: di shared-schema, **tidak ada** restore selektif langsung dari backup full. Prosedur ini memakai database klon + ekspor logis per `school_id`.

## Tujuan

Mengembalikan data SATU sekolah ke titik waktu tertentu tanpa mengganggu tenant lain (kasus: salah impor massal, penghapusan massal tak sengaja).

## Prasyarat

- Akses ke backup storage & kredensial DB (via vault)
- Kapasitas disk ≥ 2× ukuran DB untuk DB klon

## Langkah-langkah

1. **Bekukan tulis tenant target** — set `schools.status = 'maintenance'` agar user mendapat banner, bukan error misterius.
2. **Restore backup** (PITR ke waktu target, mis. 1 jam sebelum insiden) ke **database klon** `sims_restore_YYYYMMDD` — JANGAN ke production.
3. **Ekspor logis per tenant** dari DB klon: untuk tiap tabel operasional, `COPY (SELECT * FROM t WHERE school_id = X) TO ...`. Urutan: tabel referensi dulu (students, rombels), lalu transaksional (attendances, grades).
4. **Verifikasi**: hitung baris per tabel di klon vs ekspor; cek sampel data.
5. **Hapus data tenant target** di production dalam transaksi (urut terbalik dari langkah 3), lalu **impor** hasil ekspor.
6. **Aktifkan kembali** tenant; verifikasi via dashboard + 1 user uji.
7. Catat seluruh langkah + durasi di insiden log.

## Rollback

Jika impor gagal di tengah: transaksi dibatalkan otomatis (langkah 5 dalam 1 transaksi DB); tenant tetap dalam mode maintenance hingga percobaan berikutnya.

## Pencegahan (lebih murah dari restore)

- Fitur **snapshot pra-impor massal**: sebelum impor >100 baris, simpan snapshot tabel terdampak per tenant → tombol "Urungkan impor" (US backlog).
- Konfirmasi ganda untuk aksi destruktif massal di UI.

## Verifikasi sukses

- Jumlah siswa/nilai/absensi tenant kembali sesuai titik waktu target; tenant lain tidak berubah (cek checksum sampling).

## Kontak darurat

Lihat PRD §17.5 (Incident Response).
