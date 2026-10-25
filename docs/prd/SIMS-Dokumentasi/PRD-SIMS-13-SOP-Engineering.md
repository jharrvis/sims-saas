# Dok 13 — SOP Engineering: Standar Kode, Review, Git & Dokumentasi

| Atribut | Keterangan |
|---|---|
| Berlaku untuk | Seluruh developer SIMS (internal & vendor) |
| Status | Wajib — bagian dari Definition of Done (Dok 04 §5) |
| Penegakan | Pre-commit hook + CI gates + review berkala (§14) |

---

## 1. Tujuan & Prinsip

1. **Kode adalah aset jangka panjang.** Aplikasi ini akan hidup bertahun-tahun dan disentuh banyak tangan — keterbacaan mengalahkan kecerdikan.
2. **Standar > selera pribadi.** Jika ragu, ikuti dokumen ini; jika dokumen ini bisu, ikuti konvensi Laravel resmi.
3. **Otomatisasi > imbauan.** Semua yang bisa dicek mesin (format, static analysis, test) dicek mesin — review manusia fokus pada desain & logika bisnis.
4. **Dokumentasi adalah deliverable**, bukan sisa waktu.

---

## 2. Standar Penulisan Kode

### 2.1 Bahasa & Istilah Standar

- **Kode (class, method, variabel, tabel, kolom): bahasa Inggris.** Komentar & dokumentasi: bahasa Indonesia (atau Inggris untuk library publik).
- **Satu istilah = satu makna**, di kode, API, UI, dan dokumen. Tabel padanan baku (dilarang memakai sinonim lain di kode):

| Istilah Indonesia (UI/dokumen) | Istilah kode (EN) | Catatan |
|---|---|---|
| Sekolah / tenant | `school` (tenant) | `school_id` di semua tabel operasional |
| Siswa | `student` | |
| Wali murid / orang tua | `guardian` | |
| Guru / pegawai / tendik | `employee` (`jenis`: `teacher`/`staff`) | |
| Rombel | `rombel` | Istilah baku Indonesia, dipertahankan (tabel `rombels`) |
| Mata pelajaran | `subject` | |
| Jadwal pelajaran | `schedule` | |
| Absensi | `attendance` | Status: `H/I/S/A` |
| Nilai | `grade` | Komponen: `grade_component` |
| Tahun ajaran | `academic_year` | |
| Semester | `semester` (`ganjil`/`genap`) | |
| Pengguna | `user` | |
| Pengumuman | `announcement` | |
| Paket langganan | `plan` | |
| Modul / add-on | `module` | Kode: `keuangan`, `ppdb`, … |
| Langganan | `subscription` | |
| Tagihan | `invoice` | |
| Batas kuota | `quota` (`tenant_usages`, `tenant_quota_overrides`) | |

- Dilarang: singkatan tidak baku (`std`, `gr`, `usr`), campuran bahasa dalam satu identifier (`dataSiswaList`), dan penamaan boolean yang ambigu (pakai `is_`, `has_`, `can_` — mis. `is_active`, `can_release`).

### 2.2 PHP & Laravel

- `declare(strict_types=1);` di semua file PHP baru.
- Format: **Laravel Pint** (preset `laravel`) — dijalankan via pre-commit; CI menolak kode yang belum diformat.
- Static analysis: **Larastan level ≥ 6** (naik dari 5 di PRD — level 6 tanpa baseline untuk kode baru), nol error di CI.
- Arsitektur: monolit modular (Dok 02 §9) — logika bisnis di **Service class** per domain (`app/Domains/Akademik/Services/`), bukan di Controller; Controller tipis (validasi → panggil service → respons).
- Dilarang: logika bisnis di Blade/Livewire view, query di dalam loop tanpa eager loading (N+1 = bug), `DB::raw` tanpa komentar alasan, dan `env()` di luar file config.
- Enum PHP untuk status/kategori (`AttendanceStatus: H/I/S/A`, `SubscriptionStatus`, …) — dilarang string bebas untuk status.
- Semua tanggal/waktu disimpan UTC; konversi ke timezone sekolah hanya di lapisan presentasi.

### 2.3 Eloquent & Database

- Semua model operasional memakai trait `BelongsToSchool` (Dok 02 §4) — model baru tanpa trait = ditolak review.
- Migrasi mengikuti konvensi penomoran Dok 02 §1 butir 10; migrasi destruktif (drop kolom/tabel) wajib dalam PR terpisah + rencana rollback.
- Foreign key selalu didefinisikan dengan aksi `onDelete` eksplisit; index untuk kolom filter/sort.
- Seeder & factory wajib untuk setiap model baru (dipakai test + demo).

### 2.4 API (merujuk Dok 03)

- Resource class untuk setiap respons; tidak ada `return $model;` langsung (kebocoran field sensitif).
- Envelope & kode error mengikuti Dok 03 §1.4/§7 — kode bisnis baru didaftarkan di sana, bukan di karang per endpoint.
- Setiap endpoint baru: permission di Dok 05, test feature (200 + 403 + 422), dan entri di tabel ringkasan Dok 03 §6.

### 2.5 Panel Admin (Livewire / Blade) & PWA

- Komponen panel admin mengikuti design system yang disediakan; pola form/table/actions/filters konsisten per model; custom page seperlunya.
- Livewire: validasi di `rules()`, otorisasi di `mount()`/action via policy — bukan di Blade.
- PWA: panggil API via satu client terpusat (base URL, token refresh, penanganan 401/402/403 terstandar); tidak ada URL hardcode per layar.

---

## 3. Clean Code & Refactoring

### 3.1 Prinsip

SOLID, DRY, KISS, YAGNI. Aturan praktis: **fungsi ≤ 30 baris dan satu tanggung jawab**; jika butuh komentar untuk menjelaskan *apa* yang dilakukan kode (bukan *mengapa*), itu sinyal untuk refactor (ekstrak method / beri nama lebih jelas).

### 3.2 Code Smells — tolak di review

Duplikasi logika, method > 50 baris, parameter > 4 (bungkus jadi DTO/value object), *magic number/string*, *god class/service*, komentar basi yang bertentangan dengan kode, dan `TODO` tanpa issue tracker (format wajib: `// TODO(<issue-id>): ...`).

### 3.3 Kapan me-refactor

- **Boy scout rule**: tinggalkan kode lebih bersih dari saat ditemukan — refactor kecil boleh dalam PR fitur yang sama.
- **Refactor besar** (>1 hari kerja atau menyentuh kontrak API/DB): wajib **RFC** (lihat §9.3) + persetujuan Tech Lead; dilarang diam-diam dalam PR fitur.
- Dilarang refactor + fitur baru dalam satu PR (sulit di-review, sulit di-rollback).

### 3.4 Tech Debt Register

Setiap utang teknis dicatat di wiki (`Tech-Debt.md`): deskripsi, lokasi kode, dampak, estimasi bayar, dan pemilik. Review tiap sprint planning — minimal 10% kapasitas sprint untuk membayar utang tertinggi.

## 4. Code Review & Audit Kode

### 4.1 Alur Pull Request

1. Branch dari `main`, kerjakan dalam unit kecil (PR ideal ≤ 400 baris perubahan; lebih besar = pecah).
2. PR wajib berisi: ringkasan perubahan, issue/US yang ditutup (`Closes US-xxx`), langkah uji manual, dan tangkapan layar untuk perubahan UI.
3. CI hijau (format, Larastan, test) **sebelum** minta review — PR dengan CI merah tidak direview.
4. Minimal **1 approval** dari developer lain; PR yang menyentuh auth/billing/tenant-isolation wajib approval Tech Lead.
5. Merge dengan **squash** ke `main`; pesan merge mengikuti Conventional Commits.

### 4.2 Checklist Reviewer (wajib dilewati)

- [ ] Apakah perubahan sesuai dengan US/acceptance criteria?
- [ ] Apakah isolasi tenant terjaga? (cari query tanpa scope, `school_id` yang diisi dari request)
- [ ] Apakah permission/policy sudah benar? (coba akses sebagai peran yang tidak berhak)
- [ ] Apakah ada N+1, missing index, atau migrasi destruktif tanpa rencana rollback?
- [ ] Apakah error handling & kode error API mengikuti Dok 03?
- [ ] Apakah test mencakup happy path + 1 kasus negatif?
- [ ] Apakah dokumentasi diperbarui? (Dok 02/03/05 bila menyentuh tabel/API/permission; ADR bila keputusan desain)

### 4.3 SLA Review

PR direview ≤ 1×24 jam kerja. PR yang menggantung > 48 jam otomatis dieskalasi ke Tech Lead. Dilarang menumpuk > 3 PR aktif per developer.

### 4.4 Audit Kode Berkala

| Jenis audit | Frekuensi | Pelaksana |
|---|---|---|
| Dependency & secret scan (`composer audit`, gitleaks) | Setiap CI + mingguan terjadwal | Otomatis |
| Review arsitektur modul (deptrac — tidak ada dependensi terlarang antar layer) | Tiap rilis minor | Tech Lead |
| Security code review (fokus auth, billing, upload, webhook) | Tiap kuartal | Tech Lead + 1 developer bergilir |
| Audit utang teknis (register §3.4) | Tiap sprint planning | Tim |

---

## 5. Git Version Control

### 5.1 Strategi Branch — Trunk-Based (disederhanakan)

- Satu branch utama: `main` — **selalu dalam kondisi deployable** (terproteksi).
- Branch fitur berumur pendek: `feat/US-105-lupa-password`, `fix/…`, `chore/…` — target hidup < 2 hari kerja, lalu PR → squash merge → hapus branch.
- Tidak ada branch `develop`/`release` permanen; rilis = tag dari `main`. Hotfix: branch dari tag rilis → PR cepat → tag patch baru.
- Dilarang: commit langsung ke `main`, branch berumur > 1 sprint, dan merge commit (gunakan squash).

### 5.2 Pesan Commit — Conventional Commits (wajib)

Format: `<tipe>(<cakupan>): <deskripsi singkat>` — deskripsi dalam bahasa Indonesia atau Inggris, konsisten per repo (dipilih: **Indonesia**).

Tipe: `feat`, `fix`, `refactor`, `perf`, `test`, `docs`, `chore`, `sec` (keamanan), `db` (migrasi).

Contoh:
```
feat(absensi): tambah mode tandai-semua-hadir (US-039)
fix(nilai): cegah input nilai kelas bukan ampuan guru
sec(auth): paksa re-auth sebelum ganti email
db(siswa): tambah index NIS per sekolah
```

Aturan: satu commit = satu perubahan logis; pesan menjelaskan *mengapa*, bukan mengulang diff.

### 5.3 Template PR & Proteksi

- Template PR baku di `.github/pull_request_template.md` (ringkasan, US terkait, langkah uji, screenshot, checklist §4.2).
- Proteksi `main`: wajib CI hijau + 1 approval + *conversation resolved*; *force push* dilarang.
- Secret (`.env`, kunci API) **tidak pernah** di-commit — gunakan secret manager; `gitleaks` di pre-commit & CI.

### 5.4 Tagging & SemVer

Format `vMAYOR.MINOR.PATCH` (mis. `v1.4.2`): MAYOR = breaking (termasuk API v2), MINOR = fitur, PATCH = bugfix. Setiap tag memicu build rilis + draf release notes.

---

## 6. Changelog

### 6.1 Format — Keep a Changelog

`CHANGELOG.md` di root repo, Bahasa Indonesia, struktur per versi:

```markdown
## [1.4.0] - 2026-11-15
### Ditambahkan
- Mode "tandai semua hadir" pada input absensi (US-039)
### Diperbaiki
- Guru tidak bisa lagi input nilai kelas bukan ampuannya
### Keamanan
- Wajib re-auth sebelum mengganti email akun
```

Kategori: Ditambahkan / Diubah / Dihentikan / Dihapus / Diperbaiki / Keamanan. Draf di-generate dari Conventional Commits, **dikurasi manual** sebelum rilis (bahasa untuk manusia, bukan daftar commit).

### 6.2 Release Notes Pengguna

Setiap rilis minor/mayor wajib ada **catatan rilis berbahasa non-teknis** (maks. 10 butir, fokus manfaat: "Input absensi kini 2× lebih cepat dengan tombol tandai-semua-hadir") — dipublikasikan di pusat bantuan + banner "Yang baru" di aplikasi.

### 6.3 Keterkaitan versi

Versi aplikasi (`v1.4.0`) ≠ versi API (`/api/v1`) — keduanya dicatat di changelog masing-masing; kebijakan depresiasi API mengikuti Dok 03 §13.

## 7. Dokumentasi Developer

### 7.1 Dokumen wajib di repo

| File | Isi | Diperbarui |
|---|---|---|
| `README.md` | Gambaran proyek, cara jalan lokal (≤10 langkah), tautan ke wiki | Tiap perubahan setup |
| `CONTRIBUTING.md` | Ringkasan SOP ini + cara PR pertama | Tiap perubahan SOP |
| `ARCHITECTURE.md` | Diagram C4 level 1–2, daftar modul & dependensinya, keputusan besar (rujuk ADR) | Tiap rilis minor |
| `docs/adr/` | Architecture Decision Records (§7.2) | Tiap keputusan desain signifikan |
| API docs | Generate otomatis (Scribe/Scramble) dari kode — Dok 03 adalah spec, kode adalah kebenaran | Tiap PR yang ubah API |

### 7.2 ADR — Architecture Decision Record

Setiap keputusan desain yang **sulit dibatalkan** (pemilihan framework/library, pola multi-tenant, format permission, strategi pricing teknis) wajib ditulis sebagai ADR: Konteks → Keputusan → Alternatif yang ditolak → Konsekuensi. Format: `docs/adr/NNNN-judul.md`. Contoh ADR awal yang sudah bisa ditulis dari proyek ini: monolit modular, shared-schema multi-tenant, permission `{modul}.{resource}.{aksi}`, flat pricing (keputusan bisnis berdampak teknis).

### 7.3 Komentar & Docblock

- Public method pada Service: docblock 1–3 baris (tujuan + `@throws` bila ada).
- Kode yang *tidak* butuh komentar adalah target; kode yang butuh komentar "mengapa" wajib memilikinya (alasan bisnis/teknis yang tak terlihat dari kode).
- Dilarang komentar basi — ubah kode = perbarui komentarnya.

### 7.4 Runbook Operasional

Setiap kemampuan operasional wajib punya runbook di wiki: deploy, rollback, restore backup (termasuk strategi restore per-tenant PRD §17), rotasi secret, penanganan insiden (merujuk PRD §17.5), dan onboarding tenant manual.

---

## 8. Dokumentasi Pengguna (prioritas tertinggi)

Dokumentasi pengguna **bukan** dokumen teknis yang diterjemahkan — ditulis dari sudut pandang tugas pengguna.

### 8.1 Struktur

- **Pusat Bantuan** (dalam aplikasi, ikon `?`): artikel per tugas ("Cara mengunci absensi harian"), maksimal 5 langkah per artikel, screenshot terbaru.
- **Panduan per peran**: Admin Sekolah, Guru, Wali Kelas, Orang Tua, Siswa — PDF/video ≤ 3 menit per tugas utama (jadwal produksi: Dok 12 Bagian C).
- **Catatan rilis pengguna** (§6.2) + banner "Yang baru" di aplikasi tiap rilis minor.
- **Status page publik** (US-104): status layanan + riwayat insiden 90 hari.

### 8.2 Aturan penulisan

Bahasa Indonesia sehari-hari, kalimat perintah ("Klik *Simpan*", bukan "Pengguna dapat mengklik..."), satu artikel = satu tugas selesai, screenshot ≤ 3 bulan (basi = diperbarui atau dihapus). Setiap artikel mencantumkan versi aplikasi saat ditulis.

---

## 9. Wiki Developer (Obsidian Vault)

Wiki hidup di `docs/wiki/` sebagai **vault Obsidian** — bisa dibuka langsung dengan aplikasi Obsidian (gratis) maupun dibaca sebagai markdown biasa di repo.

### 9.1 Struktur vault

```
docs/wiki/
  Home.md                  # peta isi (MOC)
  Glosarium.md             # istilah baku §2.1 + tautan ke kode
  Tech-Debt.md             # register utang teknis §3.4
  adr/                     # 0000-template.md, 0001-....md
  rfc/                     # 0000-template.md (usulan perubahan besar)
  runbooks/                # deploy.md, restore.md, incident.md, ...
  diagrams/                # diagram Mermaid hasil Graphify §10
  onboarding/              # checklist developer baru §13
```

### 9.2 Aturan penulisan wiki

- Gunakan **wikilinks** `[[Nama Halaman]]` untuk merujuk halaman lain (bukan path relatif) — Obsidian menampilkan graf keterkaitan otomatis.
- Setiap halaman: 1 topik, judul jelas, maksimal 300 baris; halaman panjang dipecah + dibuat MOC-nya.
- Tag baku: `#adr`, `#rfc`, `#runbook`, `#keputusan`, `#todo`.
- Diagram memakai **Mermaid** (dirender native oleh Obsidian) — dilarang menempel gambar diagram yang digambar manual dan akan basi (§10).

### 9.3 Template

Template baku tersedia di vault: `adr/0000-template.md`, `rfc/0000-template.md`, `runbooks/0000-template.md`. Jangan membuat format sendiri.

---

## 10. Visualisasi Alur Program (Graphify)

Prinsip: **diagram dibuat dari kode, bukan digambar manual** — diagram manual basi dalam 2 sprint. Praktik ini disebut *Graphify* dalam SOP ini.

### 10.1 Tooling baku

| Kebutuhan | Perkakas | Keluaran |
|---|---|---|
| Dependensi antar layer/modul | `deptrac` (aturan di `deptrac.yaml`) | Graf + gate CI (dependensi terlarang = gagal) |
| Diagram class/domain | `phpda` atau `phuml` | PlantUML/Mermaid per domain |
| ERD | `artisan model:show` + generator ERD (Mermaid) | `docs/wiki/diagrams/erd.md` |
| Alur bisnis per modul | `php artisan docs:graph {modul}` — **dibangun sendiri** (US-122): memindai route → controller → service → job/event, output Mermaid | `docs/wiki/diagrams/alur-{modul}.md` |

### 10.2 Aturan

- Setiap modul wajib punya minimal 1 diagram alur utama (mis. alur rilis nilai, alur dunning) di `docs/wiki/diagrams/`, diregenerasi otomatis.
- CI memiliki job `docs:graph`: (1) validasi deptrac, (2) regenerasi diagram, (3) gagal bila ada diagram basi yang belum di-commit ulang — atau auto-commit oleh bot (dipilih: **gagalkan PR**, agar developer sadar ada perubahan alur).
- PR yang mengubah alur bisnis wajib menyertakan regenerasi diagram terkait.

---

## 11. Testing (ringkas)

Standar penuh mengikuti DoD (Dok 04 §5): Pest untuk unit/feature, coverage ≥ 70% untuk domain kritis (auth, nilai, absensi, billing, tenant-isolation), dan **test negatif isolasi tenant wajib** untuk setiap fitur baru yang menyentuh data (coba akses data tenant lain → harus 403/404). Test lambat (>1 dtk) ditandai dan dipisah ke suite malam hari.

---

## 12. Quality Gates CI/CD

| Stage | Gate (gagal = build merah) |
|---|---|
| Lint & format | Pint (format), gitleaks (secret) |
| Static analysis | Larastan level ≥ 6, deptrac |
| Test | Pest (unit+feature), coverage domain kritis ≥ 70% |
| Docs | `docs:graph` (diagram sinkron), ADR untuk PR berlabel `needs-adr` |
| Build | Image scan, `composer audit` bersih dari critical |
| Deploy staging | Migrasi otomatis + smoke test + seed demo |
| Deploy production | Manual approval + tag SemVer + changelog terkurasi |

---

## 13. Onboarding Developer Baru

Checklist (ada di `docs/wiki/onboarding/`): hari 1 — baca README + SOP ini + Glosarium; hari 2 — jalankan lokal + baca 3 ADR pertama; hari 3 — kerjakan *good first issue* berpasangan (*pairing*); minggu 1 — pahami 1 modul end-to-end via diagram Graphify + jadi reviewer pendamping. Setiap developer baru mendapat **buddy** selama 2 sprint.

---

## 14. Penegakan SOP

- **Pre-commit** (wajib di-install via `composer setup-hooks`): Pint + gitleaks + cek nama branch.
- **CI**: seluruh gate §12 — tidak ada pengecualian "merge dulu, benerin belakangan".
- **Audit SOP** tiap kuartal: Tech Lead menilai kepatuhan (sample PR, kelengkapan ADR, kebersihan Tech-Debt register); hasil dibahas di retro.
- **Angka ringkasan otomatis**: jumlah story, permission, endpoint, dan tabel di dokumen dilarang diperbarui manual — dihasilkan parser CI dari sumber kanonis (backlog, registry, OpenAPI, migrasi); CI gagal bila angka di dokumen tidak cocok (P1-03).
- Pelanggaran berulang = topik 1-on-1, bukan hukuman publik. SOP ini boleh diusulkan perubahannya via PR ke dokumen ini (perubahan SOP = PR biasa + diskusi tim).

---

## 15. Tautan

- Dok 02 §1 & §9 — konvensi database & modul (diacu §2.2–2.3)
- Dok 03 — konvensi API (diacu §2.4)
- Dok 04 §5 — Definition of Done (SOP ini bagiannya)
- Dok 05 — konvensi permission (diacu §2.4)
- PRD §17 — keamanan & kepatuhan (diacu §4.4, §7.4)
- Vault wiki: `docs/wiki/` (§9) — template ADR/RFC/Runbook tersedia di sana
