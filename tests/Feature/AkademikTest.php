<?php

declare(strict_types=1);

use App\Livewire\Akademik\AttendanceIndex;
use App\Livewire\Akademik\GradeInput;
use App\Livewire\Akademik\GradeLeger;
use App\Livewire\Akademik\ScheduleForm;
use App\Livewire\Akademik\ScheduleIndex;
use App\Livewire\Akademik\SubjectIndex;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Membership;
use App\Models\Rombel;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Akd', 'slug' => 'akd-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->ta = AcademicYear::create([
        'nama' => '2026/2027', 'semester' => 'ganjil', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);

    $this->admin = User::create([
        'username' => 'akdadmin', 'name' => 'Akd Admin', 'phone' => '628990000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->rombel = Rombel::create([
        'academic_year_id' => $this->ta->id, 'tingkat' => '10', 'nama' => 'X-1', 'kapasitas' => 36,
    ]);

    $this->guru = Employee::create([
        'nama' => 'Pak Guru', 'jenis' => 'guru_mapel', 'status_kepegawaian' => 'pns',
    ]);

    $this->mapel = Subject::create([
        'kode' => 'MTK', 'nama' => 'Matematika', 'kelompok' => 'wajib',
    ]);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('jadwal dibuat dan bentrok guru ditolak', function (): void {
    $payload = [
        'rombel_id' => $this->rombel->id,
        'academic_year_id' => $this->ta->id,
        'hari' => 'senin',
        'jam_ke' => 1,
        'subject_id' => $this->mapel->id,
        'employee_id' => $this->guru->id,
        'ruang' => 'R-101',
    ];

    $this->actingAs($this->admin)->postJson('/api/v1/jadwal', $payload)->assertCreated();

    // Rombel lain, guru sama, jam sama → bentrok
    $rombelB = Rombel::create([
        'academic_year_id' => $this->ta->id, 'tingkat' => '10', 'nama' => 'X-2', 'kapasitas' => 36,
    ]);
    $mapelB = Subject::create(['kode' => 'BIN', 'nama' => 'B. Indonesia', 'kelompok' => 'wajib']);

    $this->actingAs($this->admin)->postJson('/api/v1/jadwal', [
        ...$payload,
        'rombel_id' => $rombelB->id,
        'subject_id' => $mapelB->id,
    ])->assertStatus(422)
        ->assertJsonPath('message', 'Jadwal bentrok.');

    // Ruang sama, guru beda, jam sama → bentrok ruang
    $guruB = Employee::create(['nama' => 'Bu Guru', 'jenis' => 'guru_mapel', 'status_kepegawaian' => 'honorer']);

    $this->actingAs($this->admin)->postJson('/api/v1/jadwal', [
        ...$payload,
        'rombel_id' => $rombelB->id,
        'subject_id' => $mapelB->id,
        'employee_id' => $guruB->id,
    ])->assertStatus(422);
});

it('absensi tersimpan, terkunci, dan tidak bisa diubah setelah dikunci', function (): void {
    $siswa = Student::create(['nis' => '6001', 'nama' => 'Siswa Absen', 'jk' => 'L']);
    $this->rombel->students()->attach($siswa->id, [
        'academic_year_id' => $this->ta->id, 'status' => 'aktif',
    ]);

    $tanggal = '2026-10-12';

    $this->actingAs($this->admin)->postJson('/api/v1/absensi', [
        'rombel_id' => $this->rombel->id,
        'tanggal' => $tanggal,
        'records' => [
            ['student_id' => $siswa->id, 'status' => 'H'],
        ],
    ])->assertOk();

    expect(Attendance::whereDate('tanggal', $tanggal)->count())->toBe(1);

    // Kunci
    $this->actingAs($this->admin)->postJson('/api/v1/absensi/kunci', [
        'rombel_id' => $this->rombel->id,
        'tanggal' => $tanggal,
    ])->assertOk();

    // Coba ubah setelah dikunci → 423
    $this->actingAs($this->admin)->postJson('/api/v1/absensi', [
        'rombel_id' => $this->rombel->id,
        'tanggal' => $tanggal,
        'records' => [
            ['student_id' => $siswa->id, 'status' => 'A'],
        ],
    ])->assertStatus(423);

    expect(Attendance::whereDate('tanggal', $tanggal)->first()->status)->toBe('H');
});

it('rekap absensi menghitung H/I/S/A per siswa', function (): void {
    $s1 = Student::create(['nis' => '6002', 'nama' => 'Satu', 'jk' => 'L']);
    $s2 = Student::create(['nis' => '6003', 'nama' => 'Dua', 'jk' => 'P']);

    foreach ([$s1, $s2] as $s) {
        $this->rombel->students()->attach($s->id, [
            'academic_year_id' => $this->ta->id, 'status' => 'aktif',
        ]);
    }

    foreach (['2026-10-12' => 'H', '2026-10-13' => 'S', '2026-10-14' => 'A'] as $tgl => $st) {
        Attendance::create([
            'student_id' => $s1->id, 'rombel_id' => $this->rombel->id,
            'tanggal' => $tgl, 'status' => $st, 'input_by' => $this->admin->id,
        ]);
    }
    Attendance::create([
        'student_id' => $s2->id, 'rombel_id' => $this->rombel->id,
        'tanggal' => '2026-10-12', 'status' => 'H', 'input_by' => $this->admin->id,
    ]);

    $res = $this->actingAs($this->admin)->getJson(
        '/api/v1/absensi/rekap?rombel_id='.$this->rombel->id.'&dari=2026-10-12&sampai=2026-10-14'
    )->assertOk();

    /** @var Collection<int, array<string,mixed>> $data */
    $data = collect($res->json('data'));
    /** @var array<string,mixed>|null $row1 */
    $row1 = $data->firstWhere('student_id', $s1->id);

    expect((int) $row1['H'])->toBe(1)
        ->and((int) $row1['S'])->toBe(1)
        ->and((int) $row1['A'])->toBe(1)
        ->and((int) $row1['I'])->toBe(0);
});

it('jadwal dan mapel memakai struktur design system management (Batch 4)', function (): void {
    Livewire::test(ScheduleIndex::class)
        ->assertSee('Jadwal Pelajaran')
        ->assertSeeHtml('management-page-header')
        ->assertDontSee('<i data-lucide');

    Livewire::test(ScheduleForm::class)
        ->assertSee('Tambah Jadwal')
        ->assertSeeHtml('management-form-card')
        ->assertDontSee('max-w-3xl');

    Livewire::test(SubjectIndex::class)
        ->assertSee('Mata Pelajaran')
        ->assertSeeHtml('management-page-header')
        ->assertSee('Matematika')
        ->assertDontSee('<i data-lucide');
});

it('presensi, input nilai, dan leger memakai struktur design system management (Batch 5)', function (): void {
    Livewire::test(AttendanceIndex::class)
        ->assertSee('Presensi Harian')
        ->assertSeeHtml('management-page-header')
        ->assertDontSee('<i data-lucide');

    Livewire::test(GradeInput::class)
        ->assertSee('Input Nilai')
        ->assertSeeHtml('management-page-header')
        ->assertDontSee('<i data-lucide');

    Livewire::test(GradeLeger::class)
        ->assertSee('Leger Nilai')
        ->assertSeeHtml('management-page-header')
        ->assertDontSee('<i data-lucide');
});

it('jadwal tanpa izin create ditolak 403 saat save (perbaikan QA P0)', function (): void {
    $viewOnly = User::create([
        'username' => 'akdview', 'name' => 'View Only', 'phone' => '628990000201',
    ]);
    Membership::create(['user_id' => $viewOnly->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $viewOnly->assignRole('kepala-sekolah');

    Livewire::actingAs($viewOnly)->test(ScheduleForm::class)
        ->set('rombel_id', $this->rombel->id)
        ->set('academic_year_id', $this->ta->id)
        ->set('subject_id', $this->mapel->id)
        ->set('employee_id', $this->guru->id)
        ->call('save')
        ->assertForbidden();
});

it('jadwal dengan foreign key sekolah lain ditolak validasi (perbaikan QA P0)', function (): void {
    $sekolahLain = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    $taLain = AcademicYear::create([
        'school_id' => $sekolahLain->id, 'nama' => '2026/2027', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);
    $rombelLain = Rombel::create([
        'school_id' => $sekolahLain->id, 'academic_year_id' => $taLain->id,
        'tingkat' => '10', 'nama' => 'X-9', 'kapasitas' => 36,
    ]);
    $mapelLain = Subject::create([
        'school_id' => $sekolahLain->id, 'kode' => 'BIO', 'nama' => 'Biologi', 'kelompok' => 'wajib',
    ]);
    $guruLain = Employee::create([
        'school_id' => $sekolahLain->id, 'nama' => 'Guru Lain',
        'jenis' => 'guru_mapel', 'status_kepegawaian' => 'pns',
    ]);

    Livewire::actingAs($this->admin)->test(ScheduleForm::class)
        ->set('rombel_id', $rombelLain->id)
        ->set('academic_year_id', $taLain->id)
        ->set('subject_id', $mapelLain->id)
        ->set('employee_id', $guruLain->id)
        ->call('save')
        ->assertHasErrors(['rombel_id', 'academic_year_id', 'subject_id', 'employee_id']);

    $this->assertDatabaseMissing('schedules', ['rombel_id' => $rombelLain->id]);
});

it('jadwal sekolah sendiri tetap bisa dibuat (perbaikan QA P0)', function (): void {
    Livewire::actingAs($this->admin)->test(ScheduleForm::class)
        ->set('rombel_id', $this->rombel->id)
        ->set('academic_year_id', $this->ta->id)
        ->set('hari', 'senin')
        ->set('jam_ke', 1)
        ->set('subject_id', $this->mapel->id)
        ->set('employee_id', $this->guru->id)
        ->call('save')
        ->assertRedirect(route('jadwal.index', ['rombel' => $this->rombel->id]));

    $this->assertDatabaseHas('schedules', [
        'school_id' => $this->school->id, 'rombel_id' => $this->rombel->id,
    ]);
});

it('perbaikan QA visual: tombol massal absensi wrap, input 44px, tombol hapus jadwal 44px', function (): void {
    $siswa = Student::create([
        'nis' => 'AKD1', 'nama' => 'Siswa Absen', 'jk' => 'L',
    ]);
    $siswa->rombels()->attach($this->rombel->id, [
        'academic_year_id' => $this->ta->id, 'status' => 'aktif',
    ]);

    Livewire::actingAs($this->admin)->test(AttendanceIndex::class)
        ->assertSeeHtml('flex flex-wrap gap-2')
        ->assertSeeHtml('field min-h-11');

    Schedule::create([
        'rombel_id' => $this->rombel->id, 'academic_year_id' => $this->ta->id,
        'hari' => 'senin', 'jam_ke' => 1, 'subject_id' => $this->mapel->id,
        'employee_id' => $this->guru->id,
    ]);

    Livewire::actingAs($this->admin)->test(ScheduleIndex::class)
        ->assertSeeHtml('min-h-11 px-3 text-[11px]');
});
