<?php

declare(strict_types=1);

use App\Models\Membership;
use App\Models\Module;
use App\Models\Organization;
use App\Models\School;
use App\Models\Student;
use App\Models\TenantModule;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Organisasi', 'slug' => 'org-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'organisasi')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'orgadmin', 'name' => 'Org Admin', 'phone' => '628720000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->siswa = Student::create(['nis' => '9201', 'nama' => 'Siswa Organisasi', 'jk' => 'L', 'status' => 'aktif']);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul organisasi nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/organisasi')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('pengurus tidak bisa didaftarkan dua kali', function (): void {
    $orgId = $this->actingAs($this->admin)->postJson('/api/v1/organisasi', [
        'nama' => 'OSIS', 'jenis' => 'osis',
    ])->assertCreated()->json('id');

    $this->actingAs($this->admin)->postJson("/api/v1/organisasi/{$orgId}/pengurus", [
        'student_id' => $this->siswa->id,
        'jabatan' => 'Ketua',
        'periode' => '2026/2027',
    ])->assertOk();

    $this->actingAs($this->admin)->postJson("/api/v1/organisasi/{$orgId}/pengurus", [
        'student_id' => $this->siswa->id,
        'jabatan' => 'Ketua',
    ])->assertOk();

    expect(
        Organization::findOrFail($orgId)->members()->wherePivot('status', 'aktif')->count()
    )->toBe(1);
});

it('kegiatan organisasi tercatat dengan status', function (): void {
    $org = Organization::create(['nama' => 'Pramuka', 'jenis' => 'pramuka']);

    $this->actingAs($this->admin)->postJson("/api/v1/organisasi/{$org->id}/kegiatan", [
        'nama' => 'Kemah Bakti',
        'tgl_mulai' => '2026-11-01',
        'tgl_selesai' => '2026-11-03',
        'status' => 'rencana',
    ])->assertCreated();

    $res = $this->actingAs($this->admin)->getJson("/api/v1/organisasi/{$org->id}")
        ->assertOk();

    expect($res->json('activities'))->toHaveCount(1)
        ->and($res->json('activities.0.status'))->toBe('rencana');
});

it('organisasi terisolasi per sekolah', function (): void {
    Organization::create(['nama' => 'OSIS']);

    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain15-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    app(TenantContext::class)->set($other);

    expect(Organization::count())->toBe(0);
});
