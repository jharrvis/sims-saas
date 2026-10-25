# Tech Debt Register

> Setiap utang teknis dicatat di sini (SOP §3.4). Review tiap sprint planning — alokasikan ≥10% kapasitas untuk membayar yang tertinggi.

| ID | Deskripsi | Lokasi kode | Dampak | Estimasi bayar | Pemilik | Status |
|---|---|---|---|---|---|---|
| TD-001 | Contoh: query N+1 di rekap absensi bulanan | `AbsensiService::rekap()` | Lambat di sekolah >1000 siswa | 0,5 hari | — | Open |
| TD-002 | Contoh: permission modul keuangan masih hardcode | `KeuanganServiceProvider` | Harus pindah ke registry (Dok 02 §9.4) | 1 hari | — | Open |

*Hapus contoh di atas saat utang nyata pertama dicatat.*
