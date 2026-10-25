<?php

declare(strict_types=1);

use App\Livewire\Ppdb\PpdbApplicantForm;
use App\Livewire\Ppdb\PpdbApplicantIndex;
use App\Livewire\Ppdb\PpdbPeriodIndex;
use App\Models\AcademicYear;
use App\Models\Membership;
use App\Models\Module;
use App\Models\PpdbApplicant;
use App\Models\PpdbPeriod;
use App\Models\School;
use App\Models\Student;
use App\Models\TenantModule;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah PPDB', 'slug' => 'ppdb-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    // Aktifkan modul PPDB untuk tenant ini
    $ppdb = Module::where('kode', 'ppdb')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id,
        'module_id' => $ppdb->id,
        'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $ta = AcademicYear::create([
        'nama' => '2026/2027', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);

    $this->period = PpdbPeriod::create([
        'academic_year_id' => $ta->id,
        'nama' => 'Gelombang 1',
        'tgl_buka' => '2026-01-01',
        'tgl_tutup' => '2026-12-31',
        'kuota' => 100,
        'aktif' => true,
    ]);

    $this->admin = User::create([
        'username' => 'ppdbadmin', 'name' => 'PPDB Admin', 'phone' => '628660000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul ppdb nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/ppdb/periode')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('alur PPDB lengkap: daftar → verifikasi → seleksi → daftar ulang jadi siswa', function (): void {
    // 1. Daftar
    $res = $this->actingAs($this->admin)->postJson('/api/v1/ppdb/pendaftar', [
        'ppdb_period_id' => $this->period->id,
        'nama' => 'Calon Siswa',
        'jk' => 'L',
        'asal_sekolah' => 'SDN 1',
        'nama_ortu' => 'Pak Ortu',
        'no_hp_ortu' => '628120000001',
    ])->assertCreated();

    $applicant = PpdbApplicant::find($res->json('id'));
    expect($applicant->status)->toBe('draft')
        ->and($applicant->no_pendaftaran)->toStartWith('PPDB/');

    // 2. Submit → verified
    $this->actingAs($this->admin)->postJson("/api/v1/ppdb/pendaftar/{$applicant->id}/status", [
        'status' => 'submitted',
    ])->assertOk();

    $this->actingAs($this->admin)->postJson("/api/v1/ppdb/pendaftar/{$applicant->id}/status", [
        'status' => 'verified',
    ])->assertOk();

    // Transisi tidak sah: verified → enrolled (harus lewat accepted)
    $this->actingAs($this->admin)->postJson("/api/v1/ppdb/pendaftar/{$applicant->id}/status", [
        'status' => 'enrolled',
    ])->assertStatus(422);

    // 3. Seleksi
    $this->actingAs($this->admin)->postJson("/api/v1/ppdb/pendaftar/{$applicant->id}/seleksi", [
        'nilai_tes' => 85,
        'nilai_wawancara' => 90,
        'keputusan' => 'lulus',
    ])->assertOk();

    expect((float) $applicant->fresh()->selection->nilai_akhir)->toBe(87.5);

    // 4. Terima
    $this->actingAs($this->admin)->postJson("/api/v1/ppdb/pendaftar/{$applicant->id}/status", [
        'status' => 'accepted',
    ])->assertOk();

    // 5. Daftar ulang → jadi siswa
    $enroll = $this->actingAs($this->admin)
        ->postJson("/api/v1/ppdb/pendaftar/{$applicant->id}/daftar-ulang")
        ->assertCreated();

    $student = Student::find($enroll->json('student.id'));
    expect($student)->not->toBeNull()
        ->and($student->nama)->toBe('Calon Siswa')
        ->and($applicant->fresh()->status)->toBe('enrolled')
        ->and($applicant->fresh()->student_id)->toBe($student->id);
});

it('pendaftar terisolasi antar tenant', function (): void {
    $schoolB = School::create([
        'nama' => 'Sekolah B', 'slug' => 'ppdb-b-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    RoleSeeder::seedForSchool($schoolB->id);

    $ppdb = Module::where('kode', 'ppdb')->firstOrFail();
    TenantModule::create([
        'school_id' => $schoolB->id, 'module_id' => $ppdb->id, 'aktif' => true,
    ]);

    $userB = User::create(['username' => 'ppdbb', 'name' => 'PPDB B', 'phone' => '628660000002']);
    Membership::create(['user_id' => $userB->id, 'school_id' => $schoolB->id, 'status' => 'aktif']);
    app(PermissionRegistrar::class)->setPermissionsTeamId($schoolB->id);
    $userB->assignRole('admin-sekolah');

    // Pendaftar tenant A tidak terlihat dari tenant B
    PpdbApplicant::create([
        'ppdb_period_id' => $this->period->id,
        'no_pendaftaran' => 'PPDB/2026/0001',
        'nama' => 'Anak A',
        'jk' => 'P',
        'status' => 'draft',
    ]);

    $this->actingAs($userB)->getJson('/api/v1/ppdb/pendaftar?ppdb_period_id='.$this->period->id)
        ->assertOk()
        ->assertJsonPath('total', 0);
});

it('ppdb memakai struktur design system management (Batch 8)', function (): void {
    Livewire::actingAs($this->admin)->test(PpdbPeriodIndex::class)
        ->assertSee('Periode PPDB')
        ->assertSeeHtml('management-page-header')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(PpdbApplicantIndex::class, ['period' => $this->period->id])
        ->assertSee($this->period->nama)
        ->assertSeeHtml('management-page-header')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(PpdbApplicantForm::class, ['period' => $this->period->id])
        ->assertSee('Daftarkan Pendaftar')
        ->assertSeeHtml('management-form-card')
        ->assertDontSee('max-w-3xl');
});
