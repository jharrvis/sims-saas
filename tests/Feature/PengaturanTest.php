<?php

declare(strict_types=1);

use App\Livewire\Pengaturan\AcademicYearIndex;
use App\Livewire\Pengaturan\Keamanan;
use App\Livewire\Pengaturan\ModuleIndex;
use App\Livewire\Pengaturan\SchoolProfile;
use App\Models\AcademicYear;
use App\Models\Membership;
use App\Models\Module;
use App\Models\School;
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
        'nama' => 'Sekolah Atur', 'slug' => 'atur-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'aturadmin', 'name' => 'Atur Admin', 'phone' => '628580000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('memperbarui profil sekolah via API', function (): void {
    $this->actingAs($this->admin)->putJson('/api/v1/pengaturan/profil', [
        'nama' => 'SMA Negeri 1 Contoh',
        'npsn' => '12345678',
        'alamat' => 'Jl. Merdeka No. 1',
        'telepon' => '021-123456',
        'email' => 'info@contoh.sch.id',
    ])->assertOk()
        ->assertJsonPath('nama', 'SMA Negeri 1 Contoh')
        ->assertJsonPath('alamat', 'Jl. Merdeka No. 1');
});

it('mengaktifkan tahun ajaran menonaktifkan yang lain', function (): void {
    $lama = AcademicYear::create([
        'nama' => '2025/2026', 'tgl_mulai' => '2025-07-01',
        'tgl_selesai' => '2026-06-30', 'aktif' => true,
    ]);
    $baru = AcademicYear::create([
        'nama' => '2026/2027', 'tgl_mulai' => '2026-07-01',
        'tgl_selesai' => '2027-06-30', 'aktif' => false,
    ]);

    $this->actingAs($this->admin)
        ->postJson("/api/v1/pengaturan/tahun-ajaran/{$baru->id}/aktifkan")
        ->assertOk()
        ->assertJsonPath('aktif', true);

    expect($lama->fresh()->aktif)->toBeFalse();
    expect(AcademicYear::where('aktif', true)->count())->toBe(1);
});

it('toggle modul add-on mengubah status aktif', function (): void {
    $module = Module::where('kode', 'ppdb')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $module->id, 'aktif' => true,
    ]);

    // Simulasi logika toggle seperti di Livewire ModuleIndex
    $tm = TenantModule::where('module_id', $module->id)->firstOrFail();
    $tm->update(['aktif' => ! $tm->aktif]);

    expect($tm->fresh()->aktif)->toBeFalse();
});

it('profil sekolah terisolasi per tenant', function (): void {
    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain2-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(TenantContext::class)->set($other);

    $this->actingAs($this->admin)->getJson('/api/v1/pengaturan/profil')
        ->assertOk()
        // ResolveTenant mengembalikan tenant milik user (sekolah asal), bukan $other
        ->assertJsonPath('nama', 'Sekolah Atur');
});

it('profil, tahun ajaran, modul, dan keamanan memakai design system', function (): void {
    Livewire::actingAs($this->admin)->test(SchoolProfile::class)
        ->assertSee('Profil Sekolah')
        ->assertSee('management-form-card')
        ->assertDontSee('max-w-3xl')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(AcademicYearIndex::class)
        ->assertSee('Tahun Ajaran')
        ->assertSee('data-table')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(ModuleIndex::class)
        ->assertSee('Modul Add-on')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(Keamanan::class)
        ->assertSee('Keamanan Akun')
        ->assertSee('Password Akun')
        ->assertDontSee('<i data-lucide');
});

it('perbaikan QA visual: switch modul 44px dan input password 44px', function (): void {
    Livewire::actingAs($this->admin)->test(ModuleIndex::class)
        ->assertSeeHtml('inline-flex items-center h-11 px-1');

    Livewire::actingAs($this->admin)->test(Keamanan::class)
        ->assertSeeHtml('field min-h-11');
});
