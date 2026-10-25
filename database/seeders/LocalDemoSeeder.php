<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Pwa\Services\PwaIdentityService;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\BillingInvoice;
use App\Models\Employee;
use App\Models\FeeType;
use App\Models\Guardian;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\Rombel;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LocalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([PermissionSeeder::class, ModuleSeeder::class]);

        $school = School::create([
            'nama' => 'SMPN 2 Konoha',
            'slug' => 'smpn-2-konoha',
            'npsn' => '20260002',
            'jenjang' => 'SMP',
            'status' => 'aktif',
            'status_langganan' => 'active',
        ]);

        app(TenantContext::class)->set($school);

        RoleSeeder::seedForSchool($school->id);

        $admin = User::create([
            'username' => 'admin',
            'name' => 'Ayu Lestari',
            'email' => 'admin@sims.test',
            'phone' => '6281200000001',
            'password' => Hash::make('password'),
        ]);

        Membership::create(['user_id' => $admin->id, 'school_id' => $school->id, 'status' => 'aktif']);

        app(PermissionRegistrar::class)->setPermissionsTeamId($school->id);
        $admin->assignRole('superadmin');
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        $year = AcademicYear::create([
            'school_id' => $school->id,
            'nama' => '2026/2027',
            'tgl_mulai' => '2026-07-01',
            'tgl_selesai' => '2027-06-30',
            'aktif' => true,
        ]);

        $guru = Employee::create([
            'school_id' => $school->id,
            'nip_nuptk' => '198501012010011001',
            'nik' => '3201010101850001',
            'nama' => 'Budi Santoso, S.Pd.',
            'jk' => 'L',
            'agama' => 'Islam',
            'email' => 'budi@sims.test',
            'phone' => '6281200000011',
            'jenis' => 'guru_mapel',
            'status_kepegawaian' => 'pns',
            'jabatan' => 'Guru Matematika',
            'status_aktif' => true,
        ]);

        $rombel = Rombel::create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'tingkat' => '7',
            'nama' => 'VII A',
            'kapasitas' => 32,
            'wali_kelas_id' => $guru->id,
        ]);

        $students = collect([
            ['nis' => '26001', 'nama' => 'Nadia Putri', 'jk' => 'P', 'phone' => '6281200000101'],
            ['nis' => '26002', 'nama' => 'Raka Pratama', 'jk' => 'L', 'phone' => '6281200000102'],
            ['nis' => '26003', 'nama' => 'Salsabila Aisyah', 'jk' => 'P', 'phone' => '6281200000103'],
            ['nis' => '26004', 'nama' => 'Dimas Saputra', 'jk' => 'L', 'phone' => '6281200000104'],
            ['nis' => '26005', 'nama' => 'Kirana Maharani', 'jk' => 'P', 'phone' => '6281200000105'],
        ])->map(fn (array $data): Student => Student::create([
            'school_id' => $school->id,
            'nis' => $data['nis'],
            'nama' => $data['nama'],
            'jk' => $data['jk'],
            'phone' => $data['phone'],
            'agama' => 'Islam',
            'status' => 'aktif',
        ]));

        $rombel->students()->attach($students->pluck('id'), [
            'academic_year_id' => $year->id,
            'status' => 'aktif',
        ]);

        $matematika = Subject::create([
            'school_id' => $school->id,
            'kode' => 'MTK',
            'nama' => 'Matematika',
            'kelompok' => 'wajib',
            'kkm_default' => 75,
        ]);

        $bahasa = Subject::create([
            'school_id' => $school->id,
            'kode' => 'BIN',
            'nama' => 'Bahasa Indonesia',
            'kelompok' => 'wajib',
            'kkm_default' => 75,
        ]);

        $hari = strtolower(now()->locale('id')->dayName);

        if (in_array($hari, ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'], true)) {
            Schedule::create([
                'school_id' => $school->id,
                'rombel_id' => $rombel->id,
                'academic_year_id' => $year->id,
                'hari' => $hari,
                'jam_ke' => 1,
                'subject_id' => $matematika->id,
                'employee_id' => $guru->id,
                'ruang' => 'R-7A',
            ]);

            Schedule::create([
                'school_id' => $school->id,
                'rombel_id' => $rombel->id,
                'academic_year_id' => $year->id,
                'hari' => $hari,
                'jam_ke' => 2,
                'subject_id' => $bahasa->id,
                'employee_id' => $guru->id,
                'ruang' => 'R-7A',
            ]);
        }

        foreach (range(6, 0) as $daysAgo) {
            $date = now()->subDays($daysAgo)->startOfDay();

            foreach ($students as $index => $student) {
                Attendance::firstOrCreate(
                    ['student_id' => $student->id, 'tanggal' => $date->toDateString()],
                    [
                        'school_id' => $school->id,
                        'rombel_id' => $rombel->id,
                        'status' => ($index === 4 && $daysAgo === 1) ? 'I' : (($index === 3 && $daysAgo === 2) ? 'S' : 'H'),
                        'input_by' => $admin->id,
                    ]
                );
            }
        }

        $fee = FeeType::create([
            'school_id' => $school->id,
            'nama' => 'SPP Oktober',
            'nominal' => 350000,
            'periode' => 'bulanan',
            'aktif' => true,
        ]);

        foreach ($students as $index => $student) {
            Invoice::create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'fee_type_id' => $fee->id,
                'no_invoice' => 'SPP/2026/10/'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'periode_label' => '2026-10',
                'nominal' => 350000,
                'dibayar' => $index < 2 ? 350000 : 0,
                'jatuh_tempo' => now()->addDays(10)->toDateString(),
                'status' => $index < 2 ? 'paid' : 'unpaid',
            ]);
        }

        Announcement::create([
            'school_id' => $school->id,
            'judul' => 'Persiapan Penilaian Tengah Semester',
            'isi' => 'Penilaian Tengah Semester akan dilaksanakan pada 3–7 November 2026. Pastikan siswa mempersiapkan diri dengan baik.',
            'target_type' => 'semua',
            'penting' => true,
            'status' => 'tayang',
            'tayang_dari' => now()->subDays(1)->toDateString(),
            'dibuat_oleh' => $admin->id,
        ]);

        AuditLog::create([
            'school_id' => $school->id,
            'user_id' => $admin->id,
            'aksi' => 'siswa.import',
            'model_type' => Student::class,
            'model_id' => (string) $students->first()->id,
            'after' => ['jumlah' => $students->count()],
            'ip_address' => '127.0.0.1',
        ]);

        $guardian = Guardian::create([
            'school_id' => $school->id,
            'nama' => 'Ibu Nadia',
            'no_wa' => '6281200000201',
            'hubungan' => 'ibu',
        ]);

        $guardian->students()->attach([
            $students[0]->id => ['hubungan' => 'ibu'],
            $students[1]->id => ['hubungan' => 'ibu'],
        ]);

        $studentUser = User::create([
            'username' => 'nadia',
            'name' => 'Nadia Putri',
            'phone' => $students[0]->phone,
            'password' => Hash::make('password'),
        ]);

        app(PwaIdentityService::class)->linkStudent($studentUser, $students[0]);

        $parentUser = User::create([
            'username' => 'orangtua',
            'name' => 'Ibu Nadia',
            'phone' => '6281200000201',
            'password' => Hash::make('password'),
        ]);

        app(PwaIdentityService::class)->linkByPhone($parentUser);

        $plan = Plan::create([
            'nama' => 'Sekolah Pro',
            'slug' => 'sekolah-pro',
            'deskripsi' => 'Paket lengkap untuk operasional sekolah.',
            'harga_bulanan' => 499000,
            'harga_tahunan' => 4990000,
            'max_siswa' => 1200,
            'max_storage_mb' => 10240,
            'max_user_staf' => 80,
            'fitur' => ['akademik', 'keuangan', 'pwa'],
            'aktif' => true,
        ]);

        $subscription = Subscription::create([
            'school_id' => $school->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'periode' => 'bulanan',
            'periode_berakhir_pada' => now()->addMonth(),
        ]);

        BillingInvoice::create([
            'school_id' => $school->id,
            'nama_sekolah_snapshot' => $school->nama,
            'npsn_snapshot' => $school->npsn,
            'subscription_id' => $subscription->id,
            'nomor' => 'INV/2026/10/000001',
            'periode_dari' => now()->startOfMonth()->toDateString(),
            'periode_sampai' => now()->endOfMonth()->toDateString(),
            'subtotal' => 499000,
            'total' => 499000,
            'status' => 'overdue',
            'jatuh_tempo' => now()->subDays(4)->toDateString(),
        ]);

        $trialSchool = School::create([
            'nama' => 'SDN Lintas A',
            'slug' => 'sdn-lintas-a',
            'npsn' => '20260003',
            'jenjang' => 'SD',
            'status' => 'aktif',
            'status_langganan' => 'trialing',
        ]);

        Subscription::create([
            'school_id' => $trialSchool->id,
            'plan_id' => $plan->id,
            'status' => 'trial',
            'periode' => 'bulanan',
            'trial_berakhir_pada' => now()->addDays(4),
        ]);

        $platform = User::create([
            'username' => 'platform',
            'name' => 'Operator SIMS',
            'email' => 'platform@sims.test',
            'phone' => '6281200000002',
            'password' => Hash::make('password'),
            'is_platform' => true,
        ]);

        // Sentinel team 0 = konteks platform, sama seperti platform:make-admin.
        app(PermissionRegistrar::class)->setPermissionsTeamId(0);
        $platformRole = Role::findOrCreate('platform-admin', 'platform');
        $platformRole->syncPermissions(
            Permission::where('guard_name', 'platform')->get()
        );
        $platform->assignRole($platformRole);
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        app(TenantContext::class)->clear();
    }
}
