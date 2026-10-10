<?php

declare(strict_types=1);

use App\Domains\Laporan\Services\ReportService;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Membership;
use App\Models\Rombel;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Rapor', 'slug' => 'rapor-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'raporadmin', 'name' => 'Rapor Admin', 'phone' => '628570000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $tahun = AcademicYear::create([
        'nama' => '2026/2027', 'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30', 'aktif' => true,
    ]);
    $this->rombel = Rombel::create(['nama' => 'XII IPA 1', 'tingkat' => '12', 'academic_year_id' => $tahun->id]);
    $this->siswa = Student::create(['nis' => '9001', 'nama' => 'Siswa Rapor', 'jk' => 'L', 'status' => 'aktif']);
    $this->siswa->rombels()->attach($this->rombel->id, ['status' => 'aktif', 'academic_year_id' => $tahun->id]);
    Employee::create(['nip_nuptk' => '1980', 'nama' => 'Guru Rapor', 'jenis' => 'guru_mapel', 'status_kepegawaian' => 'pns', 'status_aktif' => true]);

    Attendance::create([
        'student_id' => $this->siswa->id, 'rombel_id' => $this->rombel->id,
        'tanggal' => now()->toDateString(), 'status' => 'H',
    ]);
    Attendance::create([
        'student_id' => $this->siswa->id, 'rombel_id' => $this->rombel->id,
        'tanggal' => now()->subDay()->toDateString(), 'status' => 'A',
    ]);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('menghasilkan laporan siswa dengan filter status', function (): void {
    $report = app(ReportService::class)->generate('siswa', ['status' => 'aktif']);

    expect($report['columns'])->toContain('NIS');
    expect($report['rows'])->toHaveCount(1);
    expect($report['rows']->first()[2])->toBe('Siswa Rapor');
});

it('menghasilkan laporan guru', function (): void {
    $report = app(ReportService::class)->generate('guru', []);

    expect($report['columns'])->toContain('NIP/NUPTK');
    expect($report['rows'])->toHaveCount(1);
});

it('menghasilkan laporan kelas dengan jumlah siswa', function (): void {
    $report = app(ReportService::class)->generate('kelas', []);

    expect($report['rows']->first()[0])->toBe('XII IPA 1');
    expect($report['rows']->first()[3])->toBe(1);
});

it('menghasilkan rekap absensi dengan persentase', function (): void {
    $report = app(ReportService::class)->generate('absensi', []);

    expect($report['columns'])->toContain('% Hadir');
    $row = $report['rows']->first();
    expect($row[2])->toBe(1); // H
    expect($row[5])->toBe(1); // A
    expect($row[6])->toBe('50%');
});

it('menolak jenis laporan yang tidak dikenal', function (): void {
    expect(fn () => app(ReportService::class)->generate('ngawur', []))
        ->toThrow(InvalidArgumentException::class);
});

it('ekspor CSV menghasilkan file dengan BOM dan header', function (): void {
    $service = app(ReportService::class);
    $csv = $service->toCsv($service->generate('siswa', []));

    expect($csv)->toStartWith("\xEF\xBB\xBF");
    expect($csv)->toContain('Siswa Rapor');
});

it('API laporan butuh permission laporan.view', function (): void {
    $this->admin->removeRole('admin-sekolah');

    $this->actingAs($this->admin)->getJson('/api/v1/laporan?type=siswa')->assertForbidden();
    $this->actingAs($this->admin)->getJson('/api/v1/laporan/ekspor?type=siswa')->assertForbidden();
});

it('API laporan mengembalikan kolom dan baris', function (): void {
    $this->actingAs($this->admin)->getJson('/api/v1/laporan?type=siswa')
        ->assertOk()
        ->assertJsonPath('columns.0', 'NIS');
});
