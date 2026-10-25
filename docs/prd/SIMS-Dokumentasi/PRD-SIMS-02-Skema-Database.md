# Dok 02 — Skema Database & Migrasi Laravel 13

| Atribut | Keterangan |
|---|---|
| Dokumen induk | PRD — Sistem Informasi Manajemen Sekolah (SIMS) Multi-Sekolah Terpusat v1.0 (2026-10-09) |
| Versi Dokumen | 1.1 |
| Tanggal | 9 Oktober 2026 |
| Target | Laravel 13, PHP 8.3+, PostgreSQL 16 (utama) / MySQL 8 (alternatif) |
| Changelog | v1.1 (9 Okt 2026) — **pivot SaaS**: tambah tabel platform & langganan (§8), konvensi modul add-on (§9), tenant resolution via domain (§10), seeder SaaS (§11), enforcement kuota (§12) |

Dokumen ini menerjemahkan §7 (kebutuhan fungsional FR-xxx) dan §10 (ringkasan entitas) dari PRD menjadi skema database konkret beserta kode migrasi Laravel 13 yang siap dipakai.

---

## 1. Konvensi & Prinsip

1. **Nama tabel**: `snake_case` plural (`students`, `teaching_assignments`, `grade_releases`).
2. **Primary key**: `id` → `$table->id()` (unsigned bigint auto-increment).
3. **Foreign key**: `$table->foreignId('xxx_id')` dengan perilaku `onDelete` sebagai berikut:

   | Kebijakan | Dipakai untuk | Alasan |
   |---|---|---|
   | `cascadeOnDelete()` | `school_id` di semua tabel operasional; pivot (`guardian_student`, `class_student`); anak hierarkis (`semesters`, `rombels`, `schedules`, `grade_components`, `push_subscriptions`) | Data operasional tidak bermakna tanpa induknya; penghapusan tenant/induk oleh superadmin ikut membersihkan anaknya |
   | `restrictOnDelete()` | `semester_id` di `grades`/`grade_releases`; `subject_id` di `grades`/`schedules`/`teaching_assignments`; `employee_id` di `schedules`/`teaching_assignments` | Melindungi arsip: semester/mapel/pegawai **tidak boleh** dihapus selama masih dirujuk nilai, jadwal, atau penugasan (mendukung FR-705, FR-804) |
   | `nullOnDelete()` | `users.school_id`; `input_by`, `dirilis_oleh`, `dibuat_oleh`, `wali_kelas_id`; `audit_logs.school_id/user_id` | Riwayat tetap utuh walau akun/pegawai dihapus; user yatim bisa di-reassign; audit log tidak boleh hilang |

   > Catatan: penghapusan baris `schools` oleh superadmin akan men-cascade ke seluruh data tenant. Operasional harian **tidak pernah** menghapus sekolah — gunakan penonaktifan. Untuk purge tenant penuh, gunakan service khusus (`SchoolPurgeService`) yang menghapus berurutan + audit, bukan `delete()` mentah.

4. **`$table->timestamps()`** di semua tabel; **`$table->softDeletes()`** untuk master data: `students`, `guardians`, `employees`, `rombels` (mendukung FR-106: histori nilai & absensi tetap tersimpan walau siswa pindah/keluar).
5. **`school_id` wajib** di semua tabel operasional (fondasi isolasi tenant §6.2 PRD). Pengecualian: `schools` itu sendiri, `users` (identitas global — relasi ke sekolah via `memberships`, ADR-0004), `otp_codes` (pra-login), dan `guardian_student`/`class_student` (school tersirat dari induknya).
6. **Index**: `school_id` di semua tabel operasional; kolom yang sering difilter/di-join (`nis`, `tanggal`, `nama`, `no_wa`) diberi index; kombinasi unik untuk aturan bisnis (lihat tiap migrasi).
7. **Enum**: memakai `$table->enum(...)`. Di PostgreSQL Laravel menerjemahkannya menjadi `varchar` + *check constraint* (aman); bila butuh native PG enum, buat via `DB::statement` terpisah.
8. **Migrasi bawaan Laravel 13** (`0001_01_01_000000_create_users_table`, `password_reset_tokens`, `cache`, `jobs`) — migrasi `users` di dokumen ini **menggantikan** migrasi users bawaan (hapus/ganti file bawaan agar tidak bentrok). Migrasi `personal_access_tokens` tetap memakai bawaan Sanctum.
9. **Tabel level platform vs level tenant (pivot SaaS v1.1)**: `organizations`, `plans`, `modules` adalah tabel **platform** — **tanpa** `school_id`, hanya diakses peran operator SaaS. `subscriptions`, `subscription_items`, `invoices`, `invoice_items`, `tenant_modules`, `tenant_usages`, `tenant_domains`, `tenant_quota_overrides` adalah tabel **tenant** — punya `school_id` dan memakai `BelongsToSchool`; operator platform mengaksesnya via `withoutGlobalScope` + policy khusus (lihat §10).
10. **Penomoran migrasi**: inti akademik `000001–000021` (§3); SaaS platform & billing `000022–000044` (§8); modul add-on mulai `000100+` dengan pola nama file `2026_10_09_0001xx_mod_{kode}_...` (lihat §9) agar tidak bentrok antar modul.

---

## 2. Daftar Ringkas Tabel

| Nama Tabel | Deskripsi |
|---|---|
| `schools` | Master tenant: identitas & konfigurasi per sekolah |
| `academic_years` | Tahun ajaran per sekolah (satu yang aktif) |
| `semesters` | Semester ganjil/genap per tahun ajaran |
| `users` | Akun login semua peran; `school_id` null = superadmin pusat |
| `students` | Master data siswa (soft delete) |
| `guardians` | Master data wali murid / orang tua (soft delete) |
| `guardian_student` | Pivot many-to-many siswa ↔ wali |
| `employees` | Master guru & pegawai (soft delete) |
| `subjects` | Master mata pelajaran + KKM default |
| `rombels` | Rombongan belajar per tahun ajaran + wali kelas |
| `class_student` | Riwayat keanggotaan siswa di rombel per tahun ajaran |
| `teaching_assignments` | Penugasan mengajar: guru ↔ mapel ↔ rombel per tahun ajaran |
| `schedules` | Jadwal pelajaran mingguan per rombel |
| `attendances` | Absensi harian siswa per tanggal |
| `grade_components` | Komponen penilaian (Tugas/UH/PTS/PAS) + bobot |
| `grades` | Nilai siswa per komponen per semester |
| `grade_releases` | Penanda rilis nilai per rombel per semester (pengunci tampil di PWA) |
| `announcements` | Pengumuman tersegmentasi |
| `push_subscriptions` | Langganan push notification per perangkat (PWA) |
| `otp_codes` | Kode OTP verifikasi WA (aktivasi PWA, reset password) |
| `audit_logs` | Jejak audit semua mutasi penting (append-only) |
| `organizations` | Grup sekolah / yayasan pelanggan (level platform, v1.1) |
| `organization_school` | Keanggotaan sekolah dalam organisasi (v1.1) |
| `plans` | Paket langganan: Basic/Pro/Enterprise + batas kuota (v1.1) |
| `modules` | Registry katalog modul add-on + harga & paket (v1.1) |
| `tenant_modules` | Feature flag modul per tenant + konfigurasi (v1.1) |
| `subscriptions` | Langganan aktif per sekolah: paket, status, periode (v1.1) |
| `subscription_items` | Rincian item langganan: paket dasar + add-on (v1.1) |
| `invoices` | Tagihan langganan per periode (v1.1) |
| `invoice_items` | Rincian baris tagihan (v1.1) |
| `tenant_usages` | Pemakaian kuota per metrik per periode (v1.1) |
| `tenant_domains` | Domain tenant: subdomain & custom domain (v1.1) |
| `tenant_quota_overrides` | Override batas kuota per tenant oleh Platform Admin (v1.2) |

> Tabel `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` disediakan oleh package `spatie/laravel-permission` — jalankan `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"` dan migrasinya, tidak ditulis ulang di sini.

---

## 3. Kode Migrasi Laravel 13

### 3.1 `2026_10_09_000001_create_schools_table.php` — Master tenant

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->string('npsn', 20)->unique();
            $table->enum('jenjang', ['SD', 'SMP', 'SMA', 'SMK']);
            $table->string('logo_path')->nullable();
            $table->string('kop_surat_path')->nullable();
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->json('konfigurasi')->nullable(); // template jam, branding PWA, dsb.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
```

### 3.2 `2026_10_09_000002_create_academic_years_table.php` — Tahun ajaran (FR-801–804)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nama', 20); // '2026/2027'
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->boolean('aktif')->default(false); // satu aktif per sekolah (dijaga di service)
            $table->timestamps();

            $table->unique(['school_id', 'nama']);
            $table->index('aktif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
```

### 3.3 `2026_10_09_000003_create_semesters_table.php` — Semester (FR-801)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->enum('nama', ['ganjil', 'genap']);
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->boolean('aktif')->default(false);
            $table->timestamps();

            $table->unique(['academic_year_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
```

### 3.4 `2026_10_09_000004_create_users_table.php` — Akun pengguna (FR-901–906)

> Menggantikan migrasi `users` bawaan Laravel 13.

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // Identitas GLOBAL (ADR-0004): satu baris per orang, lintas tenant.
            // Keanggotaan per sekolah dicatat di `memberships`. DILARANG memakai
            // school_id/null sebagai penanda privilege (lihat P0-03).
            $table->boolean('is_platform')->default(false); // operator platform; hanya diubah via command teraudit
            $table->string('username', 100)->unique(); // NIP/NIS/email
            $table->string('name', 150);
            $table->string('email', 150)->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->string('password');
            // kategori kasar utk routing (wali/siswa -> PWA); peran rinci via spatie
            $table->enum('tipe', ['superadmin', 'staf', 'guru', 'siswa', 'wali']);
            $table->string('avatar_path')->nullable();
            $table->timestamp('last_login_at')->nullable(); // FR-905
            $table->rememberToken();
            $table->timestamps();

            $table->index('school_id');
            $table->index('tipe');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

### 3.5 `2026_10_09_000005_create_students_table.php` — Data siswa (FR-101–106)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nis', 30);
            $table->string('nisn', 20)->nullable();
            $table->string('nama', 150);
            $table->enum('jk', ['L', 'P']);
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('agama', 30)->nullable();
            $table->text('alamat')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('foto_path')->nullable();
            $table->enum('status', ['aktif', 'pindah', 'lulus', 'keluar', 'do'])->default('aktif');
            $table->timestamps();
            $table->softDeletes(); // FR-106: histori tetap tersimpan

            $table->unique(['school_id', 'nis']); // NIS unik per sekolah
            $table->index(['school_id', 'status']);
            $table->index('nama'); // FR-104 pencarian cepat
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
```

### 3.6 `2026_10_09_000006_create_guardians_table.php` — Data wali murid (FR-201–204)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nik', 25)->nullable(); // cast 'encrypted' di model (lihat §7)
            $table->string('nama', 150);
            $table->enum('hubungan', ['ayah', 'ibu', 'wali']);
            $table->string('pekerjaan', 100)->nullable();
            $table->string('no_wa', 20); // utk OTP & notifikasi (FR-204, FR-606)
            $table->string('email', 150)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'no_wa']);
            $table->index('nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
```

### 3.7 `2026_10_09_000007_create_guardian_student_table.php` — Relasi siswa ↔ wali (FR-202)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('hubungan', ['ayah', 'ibu', 'wali']);
            $table->timestamps();

            $table->unique(['guardian_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_student');
    }
};
```

### 3.8 `2026_10_09_000008_create_employees_table.php` — Guru & pegawai (FR-301–305)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('nip_nuptk', 30)->nullable();
            $table->string('nama', 150);
            $table->enum('jenis', ['guru_mapel', 'guru_kelas', 'tendik']);
            $table->enum('status_kepegawaian', ['pns', 'pppk', 'honorer', 'kontrak']);
            $table->string('jabatan', 100)->nullable();
            $table->string('foto_path')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'jenis']);
            $table->index('nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
```

### 3.9 `2026_10_09_000009_create_subjects_table.php` — Mata pelajaran (FR-402–403)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('kode', 20); // mis. 'MTK', 'BIN'
            $table->string('nama', 100);
            $table->enum('kelompok', ['wajib', 'peminatan', 'mulok']);
            $table->decimal('kkm_default', 5, 2)->default(75);
            $table->timestamps();

            $table->unique(['school_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
```

### 3.10 `2026_10_09_000010_create_rombels_table.php` — Rombongan belajar (FR-401, FR-303)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rombels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('tingkat', 10); // 'XII', '12', '6'
            $table->string('nama', 50); // 'XII IPA 1'
            $table->unsignedInteger('kapasitas')->default(36);
            // FR-303: satu wali kelas aktif per rombel
            $table->foreignId('wali_kelas_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes(); // arsip rombel tahun lama

            $table->unique(['school_id', 'academic_year_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rombels');
    }
};
```

### 3.11 `2026_10_09_000011_create_class_student_table.php` — Keanggotaan rombel (FR-102)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rombel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['aktif', 'pindah'])->default('aktif');
            $table->timestamps();

            // FR-102: satu siswa = satu rombel aktif per tahun ajaran; riwayat tersimpan
            $table->unique(['student_id', 'academic_year_id']);
            $table->index(['rombel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_student');
    }
};
```

### 3.12 `2026_10_09_000012_create_teaching_assignments_table.php` — Penugasan mengajar (FR-302)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('rombel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['employee_id', 'subject_id', 'rombel_id', 'academic_year_id'],
                'teaching_assignments_unique'
            );
            $table->index(['school_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
    }
};
```

### 3.13 `2026_10_09_000013_create_schedules_table.php` — Jadwal pelajaran (FR-501–506)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rombel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $table->unsignedTinyInteger('jam_ke');
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->string('ruang', 30)->nullable();
            $table->timestamps();

            // FR-502 (lapis DB): cegah satu rombel punya 2 mapel di jam yang sama
            $table->unique(['rombel_id', 'hari', 'jam_ke', 'academic_year_id'], 'schedules_rombel_unique');
            $table->index(['school_id', 'hari']);
            $table->index(['employee_id', 'hari', 'jam_ke']); // bantu deteksi bentrok guru
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
```

> Deteksi bentrok guru & ruang (FR-502) dilengkapi di service layer (`ScheduleConflictService`) karena melibatkan aturan lintas baris; constraint di atas adalah jaring pengaman level DB.

### 3.14 `2026_10_09_000014_create_attendances_table.php` — Absensi harian (FR-601–606)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rombel_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('status', ['H', 'I', 'S', 'A']); // Hadir, Izin, Sakit, Alpa
            $table->string('keterangan', 255)->nullable();
            $table->foreignId('input_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable(); // FR-604: kunci absensi
            $table->timestamps();

            $table->unique(['student_id', 'tanggal']); // satu baris absensi harian per siswa
            $table->index(['school_id', 'tanggal']);
            $table->index(['rombel_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
```

> Absensi per mapel (FR-602, opsional jenjang SMA/SMK) direncanakan di tabel terpisah `subject_attendances` pada fase lanjutan agar tidak mengotori constraint absensi harian.

### 3.15 `2026_10_09_000015_create_grade_components_table.php` — Komponen penilaian (FR-701)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            // null = komponen default berlaku utk semua mapel di sekolah tsb.
            $table->foreignId('subject_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('nama', 50); // Tugas, UH, PTS, PAS
            $table->decimal('bobot', 5, 2); // persen, mis. 20.00
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['school_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_components');
    }
};
```

### 3.16 `2026_10_09_000016_create_grades_table.php` — Nilai siswa (FR-702–707)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('grade_component_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->restrictOnDelete();
            $table->decimal('nilai', 5, 2); // 0.00 - 100.00 (validasi di aplikasi, FR-703)
            $table->foreignId('input_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['student_id', 'subject_id', 'grade_component_id', 'semester_id'],
                'grades_unique'
            );
            $table->index(['school_id', 'semester_id']);
            $table->index(['subject_id', 'semester_id']); // leger per mapel (FR-706)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
```

### 3.17 `2026_10_09_000017_create_grade_releases_table.php` — Rilis nilai ke PWA (FR-707)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->restrictOnDelete();
            $table->foreignId('rombel_id')->constrained()->cascadeOnDelete();
            $table->timestamp('dirilis_pada');
            $table->foreignId('dirilis_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Nilai tampil di PWA hanya jika baris ini ada (FR-707)
            $table->unique(['semester_id', 'rombel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_releases');
    }
};
```

### 3.18 `2026_10_09_000018_create_announcements_table.php` — Pengumuman (FR-1103, FR-1302)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('judul', 200);
            $table->text('isi');
            $table->enum('target_type', ['semua', 'peran', 'kelas'])->default('semua');
            $table->json('target_data')->nullable(); // {roles:[...]} atau {rombel_ids:[...]}
            $table->date('tayang_dari');
            $table->date('tayang_sampai')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'tayang_dari']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
```

### 3.19 `2026_10_09_000019_create_push_subscriptions_table.php` — Push PWA (FR-1304)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('endpoint')->unique();
            $table->text('p256dh');
            $table->text('auth');
            $table->string('device_info', 255)->nullable(); // FR-1306: cabut sesi per perangkat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
```

### 3.20 `2026_10_09_000020_create_otp_codes_table.php` — OTP verifikasi WA (FR-204, FR-1306)

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20);
            $table->string('code_hash', 255); // kode disimpan sbg hash, bukan plaintext
            $table->string('keperluan', 50); // 'aktivasi_pwa', 'reset_password'
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0); // batasi brute force
            $table->timestamps();

            $table->index('phone');
            $table->index('expires_at'); // utk purge kode kedaluwarsa via scheduler
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
```

### 3.21 `2026_10_09_000021_create_audit_logs_table.php` — Audit log (NFR keamanan)

```php
return new class extends Migration
{
    public function up(): void
    {
        // Append-only: tanpa updated_at; baris tidak pernah di-update/di-delete aplikasi
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('aksi', 50); // created, updated, deleted, restored, login, export, unlock, release...
            $table->string('model_type', 150);
            $table->string('model_id', 50);
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['school_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
```

---

## 4. Isolasi Tenant di Eloquent — Trait `BelongsToSchool` + Global Scope

Semua model operasional memakai trait ini agar query otomatis terfilter `school_id` (§6.2 PRD).

**`app/Concerns/BelongsToSchool.php`**

```php
<?php

namespace App\Concerns;

use App\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToSchool
{
    public static function bootBelongsToSchool(): void
    {
        static::addGlobalScope(new SchoolScope);

        // Otomatis isi school_id saat create dari konteks aktif
        static::creating(function (Model $model) {
            if (empty($model->school_id) && app()->bound('currentSchoolId')) {
                $model->school_id = app('currentSchoolId');
            }
        });
    }

    public function school()
    {
        return $this->belongsTo(\App\Domains\Pengguna\Models\School::class);
    }
}
```

**`app/Scopes/SchoolScope.php`**

```php
<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SchoolScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // FAIL-CLOSED (P0-02): tanpa konteks tenant yang valid, query DITOLAK —
        // tidak pernah jalan tanpa filter.
        if (! app()->bound('currentSchoolId') || ! app('currentSchoolId')) {
            throw new \App\Exceptions\TenantContextMissing(
                'Tenant context belum terpasang untuk model ' . get_class($model)
            );
        }
        $builder->where($model->getTable() . '.school_id', app('currentSchoolId'));
    }
}
```

**Aturan pakai:**

- Middleware `SetSchoolContext`: `app()->instance('currentSchoolId', $user->school_id ?? session('active_school_id'))`.
- Superadmin memakai *tenant switcher* → `session(['active_school_id' => $id])`; setiap perpindahan dicatat di `audit_logs`.
- Akses lintas tenant (konsolidasi yayasan, operator platform) memakai helper eksplisit `Model::withoutTenantScope()` — helper ini WAJIB dipasangkan dengan policy khusus + pencatatan `audit_logs`; dilarang dipakai di request tenant biasa. `withoutGlobalScope(SchoolScope::class)` langsung dilarang via deptrac rule.
- **Test wajib**: setiap model bertrait diuji bahwa data sekolah A tidak bocor ke konteks sekolah B (DoD #3 PRD §14).

### 4.1 Tenant resolution — middleware `ResolveTenant` (revisi SaaS v1.1)

`currentSchoolId` tidak lagi semata dari user login — pada SaaS, tenant ditentukan dari **domain yang diakses**:

```php
class ResolveTenant
{
    public function handle(Request $request, Closure $next)
    {
        $school = null;

        // 1. Resolusi utama via domain — HANYA domain terverifikasi & aktif (P0-02)
        if ($domain = TenantDomain::where('domain', $request->getHost())
                ->where('terverifikasi', true)->where('aktif', true)->first()) {
            $school = $domain->school;
        }

        // 2. Fallback: membership aktif user (mis. akses via domain pusat app.sims.id)
        //    — bukan lagi $user->school_id (kolom itu sudah tidak ada, ADR-0004)
        if (! $school && $request->user()) {
            $membership = $request->user()->memberships()->aktif()->latest()->first();
            $school = $membership?->school;
        }

        // 3. Operator platform (users.is_platform = true): TANPA tenant.
        //    Boleh memilih tenant via switcher (?tenant= / session active_school_id),
        //    setiap perpindahan dicatat di audit_logs. Dilarang menebak privilege
        //    dari school_id null (P0-03).

        // 4. Validasi membership (setelah auth): user biasa WAJIB punya membership
        //    aktif pada tenant hasil resolusi, jika tidak → 403.
        if ($school && $request->user() && ! $request->user()->is_platform) {
            abort_unless(
                $request->user()->memberships()->aktif()->where('school_id', $school->id)->exists(),
                403, 'BUKAN_ANGGOTA_TENANT'
            );
        }

        if ($school) {
            // Guard langganan: tenant suspended/cancelled tidak bisa tulis data
            if (in_array($school->status_langganan, ['suspended', 'cancelled'])
                && ! $request->routeIs('billing.*')) {
                abort(402, 'Langganan tidak aktif. Selesaikan pembayaran untuk melanjutkan.');
            }

            app()->instance('currentSchoolId', $school->id);
            app()->instance('currentSchool', $school);
        }

        return $next($request);
    }
}
```

Urutan middleware: `ResolveTenant` → `SetSchoolContext` → `auth` → `RequireModule:{kode}` (lihat §9).

### 4.2 Pengecualian: tabel level platform (revisi SaaS v1.1)

- Model `Organization`, `Plan`, `Module` **tidak** memakai `BelongsToSchool` (tidak punya `school_id`) — CRUD-nya hanya untuk peran platform (`platform_admin`, `platform_finance`) via guard `platform` terpisah, dijaga policy `PlatformPolicy`.
- Tabel tenant berlanganan (`subscriptions`, `tenant_modules`, `invoices`, `tenant_usages`, `tenant_domains`, `tenant_quota_overrides`) **tetap** memakai `BelongsToSchool`. Operator platform mengaksesnya lintas tenant hanya via `Model::withoutGlobalScope(SchoolScope::class)` + policy khusus (mis. `PlatformBillingPolicy::viewAny`), dan setiap akses lintas tenant dicatat di `audit_logs` dengan `school_id` target.
- Peran `superadmin` lama **dihapus dari model identitas**: operator platform = `users.is_platform = true` + role platform via guard `platform` (ADR-0004, P0-03). Admin tiap sekolah = membership aktif + role `admin_sekolah`.

---

## 5. Relasi Eloquent Penting per Model

```php
// School
hasMany: academicYears, users, students, guardians, employees, subjects,
         rombels, schedules, attendances, grades, announcements

// AcademicYear
belongsTo: school
hasMany: semesters, rombels, schedules, classStudents, teachingAssignments

// Semester
belongsTo: academicYear
hasMany: grades, gradeReleases

// User (tipe: superadmin|staf|guru|siswa|wali)
belongsTo: school // nullable
// relasi ke profil: staf/guru -> employees, siswa -> students, wali -> guardians
//   via kolom pivot tambahan user_profiles (user_id, profile_type, profile_id) bila diperlukan

// Student
belongsTo: school
belongsToMany: guardians (pivot guardian_student + kolom hubungan)
hasMany: classStudents, attendances, grades

// Guardian
belongsTo: school
belongsToMany: students (pivot guardian_student)

// Employee
belongsTo: school
hasMany: teachingAssignments, schedules, waliKelasOf (rombels via wali_kelas_id)

// Subject
belongsTo: school
hasMany: teachingAssignments, schedules, grades, gradeComponents

// Rombel
belongsTo: school, academicYear, waliKelas (Employee via wali_kelas_id)
hasMany: classStudents, schedules, gradeReleases
belongsToMany: students (via class_student)

// TeachingAssignment
belongsTo: school, employee, subject, rombel, academicYear

// Schedule
belongsTo: school, rombel, academicYear, subject, employee

// Attendance
belongsTo: school, student, rombel, inputBy (User)

// GradeComponent
belongsTo: school, subject // nullable
hasMany: grades

// Grade
belongsTo: school, student, subject, gradeComponent, semester, inputBy (User)

// GradeRelease
belongsTo: school, semester, rombel, dirilisOleh (User)

// Announcement
belongsTo: school, dibuatOleh (User)

// PushSubscription
belongsTo: user
```

> Relasi user ↔ profil (karyawan/siswa/wali): tambahkan tabel `user_profiles` (`user_id`, `profile_type` morph, `profile_id`) bila satu user perlu terhubung ke >1 profil; untuk MVP cukup kolom langsung bila 1:1.

---

## 6. Rencana Seeder

Urutan eksekusi di `DatabaseSeeder` (mengikuti dependensi FK):

```php
public function run(): void
{
    $this->call([
        RolePermissionSeeder::class,      // 1. peran & permission (spatie) — v1.1: +3 peran platform
        PlanSeeder::class,                // 2. paket langganan + registry 16 modul (v1.1, lihat §11)
        SchoolSeeder::class,              // 3. 1 sekolah demo + tahun ajaran + semester aktif
        TenantProvisioningSeeder::class,  // 4. provisioning tenant: trial + modul (v1.1, lihat §11)
        DemoDataSeeder::class,            // 5. data contoh akademik (dev/staging saja)
    ]);
}
```

### 6.1 `RolePermissionSeeder`

- **Roles**: `superadmin`, `admin_sekolah`, `kepala_sekolah`, `tata_usaha`, `bendahara`, `wali_kelas`, `guru_mapel`, `guru_bk`, `siswa`, `wali`.
- **Permissions** (contoh granular, pola `{modul}.{resource}.{aksi}` — lihat §9.3):
  - `sekolah.kelola`, `tahun_ajaran.kelola`
  - `siswa.lihat`, `siswa.tambah`, `siswa.ubah`, `siswa.hapus`, `siswa.import`, `siswa.export`
  - `wali.lihat`, `wali.tambah`, `wali.ubah`, `wali.hapus`
  - `pegawai.*` (pola sama spt siswa)
  - `kelas.kelola`, `mapel.kelola`
  - `jadwal.lihat`, `jadwal.susun` (validasi bentrok), `jadwal.ubah`
  - `absensi.input`, `absensi.kunci`, `absensi.buka_kunci`, `absensi.rekap`
  - `nilai.input`, `nilai.import`, `nilai.kunci`, `nilai.buka_kunci`, `nilai.rilis`, `nilai.leger`
  - `laporan.lihat`, `laporan.export`
  - `pengumuman.kelola`, `pengguna.kelola`, `audit.lihat`
- Mapping role → permission mengikuti matriks §5 PRD (mis. `guru_mapel` hanya dapat `nilai.input` untuk ampuannya — dibatasi lagi via policy + `teaching_assignments`).

### 6.2 `SchoolSeeder`

Membuat 1 sekolah demo beserta fondasinya:

```php
$school = School::create([
    'nama' => 'SMA Negeri 1 Contoh',
    'npsn' => '20100001',
    'jenjang' => 'SMA',
    'timezone' => 'Asia/Jakarta',
    'konfigurasi' => ['jam_template' => [...], 'pwa' => ['theme_color' => '#1d4ed8']],
]);

$year = $school->academicYears()->create([
    'nama' => '2026/2027', 'tgl_mulai' => '2026-07-01',
    'tgl_selesai' => '2027-06-30', 'aktif' => true,
]);

foreach (['ganjil', 'genap'] as $smt) { /* ... */ }

User::create([... 'username' => 'admin', 'tipe' => 'staf', 'school_id' => $school->id])
    ->assignRole('admin_sekolah');
```

### 6.3 `DemoDataSeeder` (dev/staging only)

- **50 siswa** + 30 wali (relasi acak 1–2 wali per siswa) via factory.
- **10 guru** (`employees` jenis `guru_mapel`) + 2 tendik.
- **8 mapel** (kode unik) + **3 rombel** (X-1, X-2, XI-1) + `class_student` + `teaching_assignments`.
- **Jadwal** 1 minggu per rombel (cek bentrok harus lolos).
- **Absensi** 5 hari kerja terakhir (campuran H/I/S/A, 1 baris terkunci).
- **Komponen nilai** (Tugas 20, UH 25, PTS 25, PAS 30) + **nilai acak** 70–95 untuk semester ganjil + 1 `grade_releases` agar PWA demo menampilkan nilai.
- **3 pengumuman** (target `semua`, `kelas`, `peran`).
- Semua factory memakai `BelongsToSchool` (school_id terisi otomatis dari konteks seeder).

> Factory yang dibutuhkan: `StudentFactory`, `GuardianFactory`, `EmployeeFactory`, `UserFactory` (custom), `SubjectFactory`, `RombelFactory`. Data sensitif contoh memakai nilai fiktif yang jelas (`nama` diawali `[DEMO]`).

---

## 7. Catatan Penting

### 7.1 Kolom sensitif terenkripsi — cast `encrypted`

NIK wali (dan NIP bila dikategorikan PII) memakai enkripsi at-rest via cast bawaan Laravel:

```php
// app/Domains/Kepegawaian/Models/Guardian.php
protected function casts(): array
{
    return [
        'nik' => 'encrypted',   // dienkripsi dgn APP_KEY; tetap bisa dicari? TIDAK —
        'tgl_lahir' => 'date',  // kolom encrypted tidak bisa di-WHERE; filter di aplikasi
    ];
}
```

Implikasi: kolom `encrypted` **tidak bisa** dipakai di klausa `where`/`orderBy` DB. Untuk kebutuhan pencarian NIK, pertimbangkan kolom hash terpisah (`nik_hash` = HMAC) yang bisa di-index. **Jaga `APP_KEY`**: rotasi key tanpa re-enkripsi = data tidak terbaca; simpan key di secret manager, bukan di repo.

### 7.2 Strategi evolusi ke separate-database tenancy

Skema ini siap berevolusi bila satu sekolah menuntut isolasi DB penuh (regulasi/data residency):

1. **Kolom `school_id` tetap dipertahankan** sebagai kunci tenant logis — tidak ada perubahan domain/model.
2. Yang berubah hanya **lapisan koneksi**: resolver memilih koneksi DB per tenant (mis. package `stancl/tenancy` atau resolver kustom membaca mapping `schools` → `database_name`).
3. `SchoolScope` menjadi no-op pada mode separate-DB (satu koneksi = satu tenant), sehingga seluruh query, policy, dan komponen panel admin **tidak perlu diubah**.
4. Migrasi dijalankan per database tenant (`tenants:migrate`); tabel `schools`, `users` superadmin, dan `audit_logs` lintas-tenant dapat tinggal di **database central**.
5. Urutan migrasi yang disarankan: pisahkan dulu tabel central (`schools`, `users`, `audit_logs`) dari tabel tenant; lalu aktifkan mode hybrid (sebagian sekolah shared, sebagian dedicated).

### 7.3 Checklist sebelum `migrate:fresh --seed` pertama

- [ ] Hapus/ganti migrasi `users` bawaan Laravel 13 agar tidak bentrok dengan §3.4.
- [ ] Jalankan `php artisan vendor:publish` untuk spatie permission & sanctum; pastikan urutan migrasi spatie sebelum seeder role.
- [ ] Set `APP_KEY` final sebelum data terenkripsi masuk (lihat §7.1).
- [ ] Buat scheduler purge `otp_codes` kedaluwarsa (`expires_at < now`, hapus > 24 jam).
- [ ] Smoke test isolasi tenant: buat 2 sekolah di seeder, assert query bertrait tidak bocor.

---

*Perubahan skema setelah dokumen ini disetujui dicatat di changelog dengan versi dokumen baru, dan diiringi migrasi Laravel baru (tidak mengedit migrasi lama yang sudah rilis).*

---

## 8. Tabel-tabel SaaS & Langganan (Pivot SaaS v1.1)

> Bagian ini menggantikan asumsi "satu yayasan" pada v1.0. Setiap sekolah = **tenant pelanggan mandiri**; modul bersifat **add-on per tenant** (feature flag); ada paket langganan + billing; onboarding mandiri dengan provisioning otomatis.

### 8.1 `2026_10_09_000022_update_schools_table_for_saas.php` — Kolom SaaS di `schools`

> Mengikuti aturan changelog dokumen ini: kolom baru ditambah via migrasi baru, bukan mengedit migrasi `000001`.

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // Grup/yayasan opsional (pelanggan yang menaungi banyak sekolah)
            $table->foreignId('organization_id')->nullable()->after('id')
                ->constrained()->nullOnDelete();
            // Identitas akses tenant: https://{subdomain}.sims.id
            $table->string('subdomain', 63)->nullable()->unique()->after('npsn');
            // Cache status langganan (didenormalisasi dari subscriptions; diupdate BillingService)
            $table->enum('status_langganan', ['trial', 'active', 'past_due', 'suspended', 'cancelled'])
                ->default('trial')->after('subdomain');
            // Preset kurikulum default utk provisioning cepat per jenjang
            // (kolom jenjang SD/SMP/SMA/SMK sudah ada sejak 000001)
            $table->enum('kurikulum_default', ['merdeka', 'k13'])->nullable()->after('jenjang');
            $table->json('preset_jenjang')->nullable()->after('kurikulum_default'); // snapshot preset saat provisioning

            $table->index('organization_id');
            $table->index('status_langganan');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
            $table->dropUnique(['subdomain']);
            $table->dropColumn(['subdomain', 'status_langganan', 'kurikulum_default', 'preset_jenjang']);
        });
    }
};
```

### 8.2 `2026_10_09_000023_create_organizations_table.php` — Grup sekolah

```php
return new class extends Migration
{
    public function up(): void
    {
        // Level PLATFORM: tanpa school_id (lihat §1 konvensi #9, §4.2)
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150); // mis. 'Yayasan Pendidikan Maju'
            $table->string('kontak_nama', 150)->nullable();
            $table->string('kontak_wa', 20)->nullable();
            $table->string('kontak_email', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
```

### 8.3 `2026_10_09_000024_create_organization_school_table.php` — Keanggotaan organisasi

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_school', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['organization_id', 'school_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_school');
    }
};
```

### 8.4 `2026_10_09_000025_create_plans_table.php` — Paket langganan

```php
return new class extends Migration
{
    public function up(): void
    {
        // Level PLATFORM: tanpa school_id
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50); // Basic, Pro, Enterprise
            $table->string('slug', 50)->unique(); // basic, pro, enterprise
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('harga_bulanan');  // rupiah, mis. 499000
            $table->unsignedBigInteger('harga_tahunan');  // mis. 4990000
            // Batas kuota; null = tanpa batas
            $table->unsignedInteger('max_siswa')->nullable();
            $table->unsignedInteger('max_storage_mb')->nullable();
            $table->unsignedSmallInteger('max_user_staf')->nullable();
            $table->json('fitur'); // kode fitur inti, mis. ["akademik","pwa","laporan_dasar"]
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
```

### 8.5 `2026_10_09_000026_create_modules_table.php` — Registry katalog modul

```php
return new class extends Migration
{
    public function up(): void
    {
        // Level PLATFORM: tanpa school_id — katalog tunggal untuk semua tenant
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique(); // 'keuangan', 'ppdb', 'e_rapor', ...
            $table->string('nama', 100);          // 'Keuangan & SPP'
            $table->text('deskripsi')->nullable();
            $table->enum('kategori', ['inti', 'addon'])->default('addon');
            // slug paket yg sudah mencakup modul ini, mis. ["pro","enterprise"]; [] = hanya via add-on
            $table->json('termasuk_paket')->nullable();
            $table->unsignedBigInteger('harga_addon_bulanan')->nullable(); // null = tidak dijual terpisah
            $table->json('prasyarat')->nullable(); // ["keuangan"] — kode modul yg wajib aktif dulu
            $table->string('versi', 20)->default('1.0.0');
            $table->boolean('aktif')->default(true); // false = deprecated, tidak bisa diaktifkan tenant baru
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
```

### 8.6 `2026_10_09_000027_create_tenant_modules_table.php` — Feature flag per tenant

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->restrictOnDelete();
            $table->boolean('aktif')->default(true); // feature flag per tenant
            $table->json('konfigurasi')->nullable(); // konfigurasi khusus tenant utk modul ini
            $table->timestamp('diaktifkan_pada')->useCurrent();
            $table->timestamp('dinonaktifkan_pada')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'module_id']);
            $table->index(['school_id', 'aktif']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_modules');
    }
};
```

### 8.7 `2026_10_09_000028_create_subscriptions_table.php` — Langganan tenant

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['trial', 'active', 'past_due', 'suspended', 'cancelled'])
                ->default('trial');
            $table->enum('periode', ['bulanan', 'tahunan'])->default('bulanan');
            $table->timestamp('trial_berakhir_pada')->nullable();
            $table->timestamp('periode_berakhir_pada')->nullable();
            $table->timestamp('dibatalkan_pada')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index('periode_berakhir_pada'); // scheduler: reminder & auto-suspend
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
```

### 8.8 `2026_10_09_000029_create_subscription_items_table.php` — Item langganan

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            // null = item paket dasar; terisi = add-on modul
            $table->foreignId('module_id')->nullable()->constrained()->nullOnDelete();
            $table->string('deskripsi', 200); // 'Paket Pro — Bulanan' / 'Add-on Keuangan & SPP'
            $table->unsignedBigInteger('harga'); // rupiah per periode
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_items');
    }
};
```

### 8.9 `2026_10_09_000030_create_invoices_table.php` — Tagihan

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            // nullOnDelete + snapshot: arsip fiskal 10 tahun WAJIB selamat dari purge tenant (P0-05)
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama_sekolah_snapshot', 150); // denormalisasi saat terbit
            $table->string('npsn_snapshot', 20)->nullable();
            $table->foreignId('subscription_id')->constrained()->restrictOnDelete();
            $table->string('nomor', 50)->unique(); // 'INV/2026/10/000123' (dibuat BillingService)
            $table->date('periode_dari');
            $table->date('periode_sampai');
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('diskon')->default(0);
            $table->unsignedBigInteger('pajak')->default(0);
            $table->unsignedBigInteger('total');
            $table->enum('status', ['draft', 'issued', 'paid', 'overdue', 'void'])->default('draft');
            $table->date('jatuh_tempo');
            $table->timestamp('dibayar_pada')->nullable();
            $table->string('metode_bayar', 50)->nullable();
            $table->string('bukti_path')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index('jatuh_tempo'); // scheduler: tandai overdue & kirim reminder
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
```

### 8.10 `2026_10_09_000031_create_invoice_items_table.php` — Rincian tagihan

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('deskripsi', 200);
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('harga_satuan');
            $table->unsignedBigInteger('total');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
```

### 8.11 `2026_10_09_000032_create_tenant_usages_table.php` — Pemakaian kuota

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('metrik', 50); // 'siswa_aktif', 'user_staf_aktif', 'storage_mb'
            $table->unsignedBigInteger('nilai')->default(0);
            $table->string('periode', 7); // '2026-10' (YYYY-MM)
            $table->timestamps();

            $table->unique(['school_id', 'metrik', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_usages');
    }
};
```

> Nilai diisi oleh job agregasi harian (`AggregateTenantUsage`, lihat §12) — bukan dihitung live per request.

### 8.12 `2026_10_09_000033_create_tenant_domains_table.php` — Domain tenant

```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('domain', 253)->unique(); // 'sman1.sims.id' atau 'sistem.sman1.sch.id'
            $table->enum('tipe', ['subdomain', 'custom'])->default('subdomain');
            $table->boolean('terverifikasi')->default(false); // custom domain wajib verifikasi DNS
            $table->string('token_verifikasi', 64)->nullable();
            $table->timestamps();

            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_domains');
    }
};
```

**Relasi Eloquent baru (ringkas):**

```php
// School
belongsTo: organization
hasMany: tenantModules, subscriptions, invoices, tenantUsages, tenantDomains
// helper: activeSubscription() → hasOne Subscription where status in [trial,active,past_due] latest

// Organization
hasMany: schools (via organization_school pivot → belongsToMany)

// Plan
hasMany: subscriptions

// Module
hasMany: tenantModules, subscriptionItems

// Subscription
belongsTo: school, plan
hasMany: items (subscription_items), invoices

// Invoice
belongsTo: school, subscription
hasMany: items (invoice_items)

// TenantQuotaOverride
belongsTo: school, dibuatOleh (users, guard platform)
```

### 8.13 `2026_10_09_000034_create_tenant_quota_overrides_table.php` — Override kuota per tenant

```php
Schema::create('tenant_quota_overrides', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('metrik', 50); // siswa_aktif, user_staf, storage_mb, wa_bulanan
    $table->unsignedBigInteger('batas_custom')->nullable(); // null = tanpa batas
    $table->text('alasan');
    $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('berlaku_sampai')->nullable(); // null = permanen hingga dicabut
    $table->timestamps();

    $table->unique(['school_id', 'metrik']);
    $table->index('berlaku_sampai');
});
```

> **Aturan enforcement di `QuotaService::check()`** (lihat §11): urutan evaluasi batas = (1) `tenant_quota_overrides` yang masih berlaku → (2) batas paket dari `plans` → (3) batas trial bila status `trial`. Override tidak berlaku untuk masa trial (trial selalu memakai batas §9.3 PRD). Override kedaluwarsa otomatis diabaikan oleh query scope (`berlaku_sampai >= now() OR null`).

---

### 8.14 `2026_10_09_000035_create_memberships_table.php` — Keanggotaan identitas per tenant (ADR-0004, P0-01)

```php
Schema::create('memberships', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // identitas global
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->enum('status', ['aktif', 'dinonaktifkan'])->default('aktif');
    $table->timestamp('berakhir_pada')->nullable();
    $table->timestamps();

    $table->unique(['user_id', 'school_id']);
    $table->index(['school_id', 'status']);
});
```

> Role per membership memakai Spatie Teams (`setPermissionsTeamId($school->id)`); token Sanctum membawa **satu** `school_id` membership aktif.

### 8.15 `2026_10_09_000036_create_webhook_subscriptions_table.php` — Langganan webhook keluar (P1-07)

```php
Schema::create('webhook_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('url_tujuan', 500);
    $table->json('event'); // ['nilai.dirilis', 'invoice.lunas', ...]
    $table->string('secret', 100); // untuk signature HMAC
    $table->boolean('aktif')->default(true);
    $table->timestamps();
});
```

### 8.16 `2026_10_09_000037_create_webhook_deliveries_table.php` — Outbox & retry ledger (P1-07)

```php
Schema::create('webhook_deliveries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('webhook_subscription_id')->constrained()->cascadeOnDelete();
    $table->string('event', 100);
    $table->json('payload');
    $table->enum('status', ['antre', 'terkirim', 'gagal', 'dead_letter'])->default('antre');
    $table->unsignedTinyInteger('percobaan')->default(0);
    $table->timestamp('coba_lagi_pada')->nullable();
    $table->text('respons_terakhir')->nullable();
    $table->timestamps();

    $table->index(['status', 'coba_lagi_pada']);
});
```

### 8.17 `2026_10_09_000038_create_payment_transactions_table.php` — Ledger pembayaran (P1-07)

```php
Schema::create('payment_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete(); // arsip selamat dari purge
    $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
    $table->string('provider', 30); // xendit
    $table->string('external_id', 100)->unique(); // ID di sisi provider
    $table->string('tipe', 30); // subscription, add_on, overage
    $table->unsignedBigInteger('jumlah_idr');
    $table->enum('status', ['pending', 'lunas', 'gagal', 'kedaluwarsa', 'refund'])->default('pending');
    $table->json('payload_provider')->nullable();
    $table->timestamps();

    $table->index(['school_id', 'status']);
});
```

### 8.18 `2026_10_09_000039_create_idempotency_keys_table.php` — Kunci idempotensi (P1-07)

```php
Schema::create('idempotency_keys', function (Blueprint $table) {
    $table->id();
    $table->string('provider', 30);
    $table->string('event', 100);
    $table->string('kunci', 150);
    $table->json('hasil')->nullable(); // respons yang sudah diproses
    $table->timestamp('diproses_pada')->useCurrent();

    $table->unique(['provider', 'event', 'kunci']); // satu event = satu efek
});
```

### 8.19 `2026_10_09_000040_create_consent_ledger_table.php` — Buku persetujuan (P0-07, UU PDP)

```php
Schema::create('consent_ledger', function (Blueprint $table) {
    // Immutable: hanya INSERT, tidak ada UPDATE/DELETE (dijaga di level aplikasi + policy DB)
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('guardian_id')->nullable()->constrained()->nullOnDelete();
    $table->string('versi_kebijakan', 20); // mis. 'KP-2026-10'
    $table->string('tujuan', 100); // 'pemrosesan_data_siswa', 'foto_publikasi', ...
    $table->enum('status', ['diberikan', 'ditarik'])->default('diberikan');
    $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
    $table->text('bukti')->nullable(); // referensi dokumen / hash tanda tangan
    $table->timestamp('dicatat_pada')->useCurrent();

    $table->index(['student_id', 'tujuan']);
});
```

### 8.20 `2026_10_09_000041_create_dsar_cases_table.php` — Permohonan hak subjek data (P0-07)

```php
Schema::create('dsar_cases', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('nomor_tiket', 30)->unique();
    $table->enum('jenis', ['akses', 'koreksi', 'hapus', 'tarik_persetujuan', 'portabilitas']);
    $table->morphs('pemohon'); // guardian / student / employee
    $table->enum('status', ['baru', 'verifikasi_identitas', 'diproses', 'selesai', 'ditolak'])->default('baru');
    $table->timestamp('batas_waktu'); // SLA per jenis hak (UU PDP: 3x24 jam untuk jenis tertentu)
    $table->json('hasil')->nullable(); // bundle ekspor / bukti eksekusi
    $table->foreignId('ditangani_oleh')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();

    $table->index(['school_id', 'status']);
});
```

### 8.21 `2026_10_09_000042_create_import_batches_table.php` — Batch impor & undo (P0-06, US-123)

```php
Schema::create('import_batches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('jenis', 50); // siswa, nilai, ...
    $table->string('nama_file', 255);
    $table->unsignedInteger('baris_total')->default(0);
    $table->unsignedInteger('baris_berhasil')->default(0);
    $table->enum('status', ['diproses', 'selesai', 'selesai_sebagian', 'dibatalkan', 'diurungkan'])->default('diproses');
    $table->foreignId('diimpor_oleh')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('diurungkan_pada')->nullable();
    $table->timestamps();
});
// Setiap baris yang ditulis batch ini menandai import_batch_id → undo = compensating delete per batch.
```

### 8.22 `2026_10_09_000043_create_webauthn_credentials_table.php` — Kredensial passkey (P1-13)

```php
Schema::create('webauthn_credentials', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('nama_perangkat', 100);
    $table->text('credential_id');
    $table->text('public_key');
    $table->unsignedBigInteger('counter')->default(0);
    $table->timestamps();

    $table->unique(['user_id', 'credential_id']);
});
```

### 8.23 `2026_10_09_000044_create_notification_templates_table.php` — Template notifikasi (P1-13, US-107)

```php
Schema::create('notification_templates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete(); // null = template bawaan platform
    $table->string('kode', 100); // 'absensi.alpa', 'nilai.dirilis', ...
    $table->string('kanal', 20); // wa, push, email
    $table->text('isi'); // dengan placeholder {{nama_siswa}} ...
    $table->boolean('aktif')->default(true);
    $table->timestamps();

    $table->unique(['school_id', 'kode', 'kanal']);
});
```

---

## 9. Konvensi Modul — Arsitektur Add-on per Tenant (v1.1)

Setiap modul add-on (keuangan, PPDB, dsb.) adalah unit terisolasi yang bisa diaktifkan/dinonaktifkan **per tenant** via `tenant_modules`. Modul inti akademik (§3.1–§3.21) selalu aktif dan tidak butuh flag.

### 9.1 Struktur direktori per modul

```
app/Domains/Modul/{NamaModul}/
  {NamaModul}ModuleServiceProvider.php  → registrasi modul (wajib)
  Models/        → model-model modul (WAJIB pakai BelongsToSchool)
  Services/
  Ui/            → Komponen panel admin (menu terdaftar kondisional)
  Http/          → Controller API (bila ada endpoint PWA)
  database/
    migrations/  → migrasi modul, pola: 2026_10_09_0001xx_mod_{kode}_*.php
    seeders/
```

### 9.2 Migrasi per modul

- Rentang nomor `000100+`; tiap modul mendapat blok `xx` sendiri agar file tidak bentrok, mis. keuangan `000101–000109`, PPDB `000110–000119`, dst. Contoh: `2026_10_09_000101_mod_keuangan_create_tagihans_table.php`.
- **Aturan keras**: setiap tabel baru milik modul **WAJIB** punya `foreignId('school_id')->constrained()->cascadeOnDelete()` dan modelnya memakai trait `BelongsToSchool` (§4). Migrasi modul yang melanggar ditolak di CI (custom linter memeriksa string `school_id`).
- Tabel modul tidak boleh FK langsung ke tabel modul lain — relasi antar modul lewat `school_id` + id logis, atau via service layer (menghindari coupling migrasi).

### 9.3 Permission namespaced `{modul}.{resource}.{aksi}`

Pola: `{kode_modul}.{resource}.{aksi}` — kosakata `{aksi}` mengikuti Dok 05 §2 (view/create/update/delete/input/lock/approve/activate/…). Contoh:

```
keuangan.tagihan.view, keuangan.tagihan.create, keuangan.tagihan.update,
keuangan.tagihan.approve, keuangan.laporan.view,
ppdb.pendaftar.view, ppdb.pendaftar.approve, ppdb.pengumuman.create,
perpus.buku.view, perpus.peminjaman.create
```

Aksi domain baru didaftarkan di Dok 05 §2, bukan di dokumen ini — satu sumber kebenaran kosakata permission.

Aturan: permission modul hanya **efektif** bila modul aktif untuk tenant (`tenant_modules.aktif = true`) — dicek di `ModulePermissionMiddleware`/policy via helper `tenant()->moduleAktif('keuangan')`. Menonaktifkan modul otomatis menyembunyikan menu & memblokir route (403 + kode `MODULE_DISABLED`, sama seperti Dok 03 §7 dan Dok 05 §5).

### 9.4 Registrasi via `ModuleServiceProvider`

Setiap modul menyediakan provider yang mengimplementasikan kontrak berikut:

```php
interface ModuleRegistrar
{
    public function kode(): string;          // 'keuangan' — cocok dengan modules.kode
    public function nama(): string;         // 'Keuangan & SPP'
    public function migrationPath(): string;
    public function permissions(): array;   // daftar permission namespaced
    public function menuItems(): array;     // item navigasi panel admin (ditampilkan bila modul aktif)
    public function seeders(): array;       // seeder preset per jenjang (lihat §10.3)
}
```

- Provider inti mendaftarkan semua `ModuleRegistrar` yang terdeteksi ke `ModuleRegistry` (singleton).
- Route modul dibungkus middleware `RequireModule:{kode}`:

```php
Route::middleware(['auth', 'ResolveTenant', 'RequireModule:keuangan'])
    ->group(fn () => /* route modul keuangan */);
```

- `RequireModule` mengecek `tenant_modules` (cache per request) → 403 bila nonaktif; prasyarat (`modules.prasyarat`) divalidasi saat aktivasi di panel billing.

---

## 10. Seeder SaaS (v1.1)

### 10.1 Revisi `RolePermissionSeeder` — peran platform

Tambahan 3 peran operator SaaS (level platform, `users.school_id = null`):

| Peran | Hak |
|---|---|
| `platform_admin` | Kelola `organizations`, `plans`, `modules`, tenant switcher lintas sekolah, `audit.lihat` global |
| `platform_finance` | Kelola `subscriptions`, `invoices` (issued/paid/void), lihat `tenant_usages`; **tanpa** akses data akademik tenant |
| `platform_support` | Impersonasi read-only ke tenant (dengan persetujuan tercatat), kelola `tenant_domains` |

Permission platform memakai namespace `platform.*`: `platform.sekolah.lihat`, `platform.sekolah.suspend`, `platform.plan.kelola`, `platform.modul.kelola`, `platform.invoice.kelola`, `platform.organisasi.kelola`.

### 10.2 `PlanSeeder` — 3 paket + registry 16 modul

```php
public function run(): void
{
    $plans = [
        [
            'nama' => 'Basic', 'slug' => 'basic',
            'harga_bulanan' => 499000, 'harga_tahunan' => 4990000,
            'max_siswa' => 300, 'max_storage_mb' => 10240, 'max_user_staf' => 20,
            'fitur' => ['akademik', 'pwa', 'laporan_dasar'],
        ],
        [
            'nama' => 'Pro', 'slug' => 'pro',
            'harga_bulanan' => 999000, 'harga_tahunan' => 9990000,
            'max_siswa' => 1500, 'max_storage_mb' => 51200, 'max_user_staf' => 75,
            'fitur' => ['akademik', 'pwa', 'laporan_dasar', 'laporan_lanjutan'],
        ],
        [
            'nama' => 'Enterprise', 'slug' => 'enterprise',
            'harga_bulanan' => 2500000, 'harga_tahunan' => 25000000,
            'max_siswa' => null, 'max_storage_mb' => 204800, 'max_user_staf' => null,
            'fitur' => ['akademik', 'pwa', 'laporan_dasar', 'laporan_lanjutan', 'api_akses', 'sso'],
        ],
    ];
    foreach ($plans as $p) { Plan::updateOrCreate(['slug' => $p['slug']], $p); }

    // Registry 16 modul (katalog PRD §8; termasuk_paket selaras matriks Dok 10 §3.1 — model 2-tier: Basic à la carte, Pro all-inclusive)
    $modules = [
        // kode, nama, termasuk_paket, harga_addon_bulanan, prasyarat
        ['keuangan',      'Keuangan & SPP',          ['pro','enterprise'],      499000, []],
        ['ppdb',          'PPDB Online',             ['pro','enterprise'],      750000, []], // per musim PPDB
        ['e_rapor',       'E-Rapor & Cetak Rapor',   ['pro','enterprise'],      299000, []],
        ['tata_tertib',   'Tata Tertib & Poin',      ['pro','enterprise'],      149000, []],
        ['bk_konseling',  'BK & Konseling',          ['pro','enterprise'],      149000, []],
        ['perpustakaan',  'Perpustakaan Digital',    ['pro','enterprise'],      249000, []],
        ['ekstrakurikuler','Ekstrakurikuler & Prestasi',['basic','pro','enterprise'], 0, []],
        ['lms_mini',      'LMS Mini / Tugas Online', ['pro','enterprise'],      349000, []],
        ['broadcast',     'Notifikasi Terjadwal',    ['pro','enterprise'],      199000, []],
        ['inventaris',    'Inventaris & Aset',       ['pro','enterprise'],      149000, []],
        ['uks',           'UKS & Kesehatan',         ['pro','enterprise'],      149000, []],
        ['transportasi',  'Antar-Jemput',            ['pro','enterprise'],      249000, []],
        ['kantin',        'Kantin Digital',          ['pro','enterprise'],      249000, ['keuangan']],
        ['survei',        'Survei & Evaluasi',       ['pro','enterprise'],      149000, []],
        ['alumni',        'Alumni & Karier',         ['pro','enterprise'],      149000, []],
        ['organisasi',    'Organisasi & Konsolidasi', [],                       499000, []],
    ];
    foreach ($modules as [$kode, $nama, $paket, $harga, $prasyarat]) {
        Module::updateOrCreate(['kode' => $kode], [
            'nama' => $nama, 'kategori' => 'addon',
            'termasuk_paket' => $paket, 'harga_addon_bulanan' => $harga,
            'prasyarat' => $prasyarat, 'aktif' => true,
        ]);
    }
}
```

> `updateOrCreate` membuat seeder idempotent — aman dijalankan ulang saat deploy.

### 10.3 `TenantProvisioningSeeder` — simulasi provisioning tenant baru

Mensimulasikan alur **onboarding mandiri**: 1 sekolah baru → subdomain → trial 30 hari → modul trial aktif. Di production, logika yang sama dipakai `ProvisioningService` (dipicu setelah pembayaran/registrasi).

```php
public function run(): void
{
    DB::transaction(function () {
        $school = School::create([
            'nama' => 'SMP Negeri 2 Demo',
            'npsn' => '20100002',
            'jenjang' => 'SMP',
            'subdomain' => 'smpn2-demo',
            'kurikulum_default' => 'merdeka',
            'timezone' => 'Asia/Jakarta',
            // Preset per jenjang: struktur mapel & komponen nilai bawaan utk SMP
            'preset_jenjang' => ['mapel_wajib' => ['BIN','MTK','IPA','IPS','PPKn'], 'semester' => 2],
        ]);

        TenantDomain::create([
            'school_id' => $school->id,
            'domain' => 'smpn2-demo.sims.id',
            'tipe' => 'subdomain',
            'terverifikasi' => true,
        ]);

        $plan = Plan::where('slug', 'basic')->firstOrFail();
        $subscription = Subscription::create([
            'school_id' => $school->id,
            'plan_id' => $plan->id,
            'status' => 'trial',
            'periode' => 'bulanan',
            'trial_berakhir_pada' => now()->addDays(30),
            'periode_berakhir_pada' => now()->addDays(30),
        ]);
        SubscriptionItem::create([
            'subscription_id' => $subscription->id,
            'module_id' => null, // paket dasar
            'deskripsi' => 'Paket Basic — Trial 30 hari',
            'harga' => 0,
        ]);

        // Modul trial: 2 modul "Must" (ppdb, e_rapor) aktif selama trial
        foreach (['ppdb', 'e_rapor'] as $kode) {
            TenantModule::create([
                'school_id' => $school->id,
                'module_id' => Module::where('kode', $kode)->firstOrFail()->id,
                'aktif' => true,
            ]);
        }

        // Baris awal pemakaian kuota periode berjalan
        TenantUsage::create([
            'school_id' => $school->id, 'metrik' => 'siswa_aktif',
            'nilai' => 0, 'periode' => now()->format('Y-m'),
        ]);

        // Admin pertama sekolah (di production: dari form registrasi)
        $admin = User::create([
            'school_id' => $school->id, 'username' => 'admin.smpn2',
            'name' => 'Admin SMPN 2', 'password' => Hash::make('ganti-segera'),
            'tipe' => 'staf',
        ]);
        $admin->assignRole('admin_sekolah');
    });
}
```

### 10.4 Preset per jenjang

`SchoolSeeder` (direvisi) menerima preset berdasarkan `jenjang` + `kurikulum_default`:

| Jenjang | Preset mapel wajib (contoh) | Komponen nilai default |
|---|---|---|
| SD | Tematik, PAI, PJOK, ... | Tugas 30 / UH 30 / PTS 20 / PAS 20 |
| SMP | BIN, MTK, IPA, IPS, ... | Tugas 20 / UH 25 / PTS 25 / PAS 30 |
| SMA/SMK | + peminatan/mulok | Tugas 20 / UH 25 / PTS 25 / PAS 30 |

Preset disimpan sebagai seeder per modul inti (`AcademicPresetSeeder`) dan dipanggil `ProvisioningService` dengan parameter jenjang — sekolah baru langsung punya struktur siap pakai dalam hitungan detik.

---

## 11. Enforcement Kuota Langganan (v1.1)

Kuota (`plans.max_siswa`, `max_storage_mb`, `max_user_staf`) ditegakkan terhadap `tenant_usages` periode berjalan:

```php
// app/Domains/Billing/Services/QuotaService.php
class QuotaService
{
    public function check(string $metrik, School $school): void
    {
        $plan = $school->activeSubscription?->plan;
        $limit = match ($metrik) {
            'siswa_aktif'     => $plan?->max_siswa,
            'storage_mb'      => $plan?->max_storage_mb,
            'user_staf_aktif' => $plan?->max_user_staf,
        };
        if (is_null($limit)) return; // paket unlimited (Enterprise)

        $pakai = TenantUsage::where('school_id', $school->id)
            ->where('metrik', $metrik)
            ->where('periode', now()->format('Y-m'))
            ->value('nilai') ?? 0;

        $rasio = $limit > 0 ? $pakai / $limit : 0;

        if ($rasio >= 1) {
            // HARD LIMIT: tolak — dikonversi ke respons 422 {code: KUOTA_PENUH}
            throw new QuotaExceededException($metrik, $limit);
        }
        if ($rasio >= 0.9) {
            // SOFT LIMIT: peringatan ke admin sekolah (WA/push) + banner upgrade
            event(new QuotaWarning($school, $metrik, (int) $pakai, $limit));
        }
    }
}
```

**Titik enforcement (contoh):**

```php
// app/Domains/Akademik/Services/StudentService.php
public function create(array $data): Student
{
    $school = app('currentSchool');
    app(QuotaService::class)->check('siswa_aktif', $school); // tolak bila kuota penuh
    return Student::create($data); // school_id terisi otomatis via BelongsToSchool
}
```

**Agregasi pemakaian** — job harian `AggregateTenantUsage` (scheduler 02:00):

```php
TenantUsage::updateOrCreate(
    ['school_id' => $s->id, 'metrik' => 'siswa_aktif', 'periode' => $periode],
    ['nilai' => $s->students()->where('status', 'aktif')->count()]
);
// storage_mb: SUM ukuran file di disk per school_id; user_staf_aktif: COUNT users tipe staf/guru
```

> Catatan: `tenant_usages` adalah **sumber kebenaran** untuk penagihan & kuota; hitungan live hanya dipakai saat agregasi. Untuk trial yang kedaluwarsa, scheduler mengubah `subscriptions.status` → `past_due` → (H+7) `suspended`; `status_langganan` di `schools` disinkronkan oleh `BillingService` yang sama.
