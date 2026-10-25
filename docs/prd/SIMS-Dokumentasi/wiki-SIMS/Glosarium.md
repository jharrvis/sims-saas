# Glosarium Istilah Baku

> Acuan tunggal penamaan di kode, API, UI, dan dokumen (SOP §2.1). Jika menemukan sinonim baru, daftarkan di sini DULU sebelum dipakai.

| Indonesia (UI/dokumen) | Kode (EN) | Keterangan |
|---|---|---|
| Sekolah / tenant | `school` | `school_id` di semua tabel operasional |
| Siswa | `student` | |
| Wali murid / orang tua | `guardian` | |
| Guru / pegawai / tendik | `employee` | `jenis`: `teacher` / `staff` |
| Rombel | `rombel` | Istilah baku Indonesia (tabel `rombels`) |
| Mata pelajaran | `subject` | |
| Jadwal pelajaran | `schedule` | |
| Absensi | `attendance` | Status `H/I/S/A` |
| Nilai | `grade` | Komponen: `grade_component` |
| Tahun ajaran | `academic_year` | |
| Semester | `semester` | `ganjil` / `genap` |
| Pengguna | `user` | |
| Pengumuman | `announcement` | |
| Paket langganan | `plan` | `basic` / `pro` / `enterprise` |
| Modul / add-on | `module` | Kode: `keuangan`, `ppdb`, … |
| Langganan | `subscription` | |
| Tagihan | `invoice` | |
| Batas kuota | `quota` | `tenant_usages`, `tenant_quota_overrides` |

**Aturan boolean:** prefix `is_` / `has_` / `can_` (mis. `is_active`, `can_release`).
**Dilarang:** singkatan tak baku (`std`, `gr`), campur bahasa (`dataSiswaList`).
