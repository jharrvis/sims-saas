<?php

declare(strict_types=1);

use App\Livewire\Uks\HealthVisitForm;
use App\Livewire\Uks\HealthVisitIndex;
use App\Livewire\Uks\MedicineIndex;
use App\Models\HealthVisit;
use App\Models\Medicine;
use App\Models\Membership;
use App\Models\Module;
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
        'nama' => 'Sekolah UKS', 'slug' => 'uks-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'uks')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'uksadmin', 'name' => 'UKS Admin', 'phone' => '628670000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->siswa = Student::create(['nis' => '9401', 'nama' => 'Siswa UKS', 'jk' => 'L', 'status' => 'aktif']);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul uks nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/uks/kunjungan')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('mencatat kunjungan uks dan memfilter rujukan', function (): void {
    $this->actingAs($this->admin)->postJson('/api/v1/uks/kunjungan', [
        'student_id' => $this->siswa->id,
        'tgl' => now()->format('Y-m-d H:i:s'),
        'keluhan' => 'Sakit kepala dan mual.',
        'diagnosa' => 'Migrain',
        'suhu' => 37.5,
        'rujukan' => 'puskesmas',
    ])->assertCreated();

    $this->actingAs($this->admin)->postJson('/api/v1/uks/kunjungan', [
        'student_id' => $this->siswa->id,
        'tgl' => now()->format('Y-m-d H:i:s'),
        'keluhan' => 'Luka lecet.',
    ])->assertCreated();

    $res = $this->actingAs($this->admin)->getJson('/api/v1/uks/kunjungan?rujukan=puskesmas')
        ->assertOk();

    expect($res->json('data'))->toHaveCount(1);
});

it('pemakaian obat mengurangi stok dan menolak saat kurang', function (): void {
    $obat = $this->actingAs($this->admin)->postJson('/api/v1/uks/obat', [
        'nama' => 'Paracetamol', 'satuan' => 'tablet', 'stok' => 10, 'stok_minimum' => 3,
    ])->assertCreated()->json('id');

    $this->actingAs($this->admin)->postJson("/api/v1/uks/obat/{$obat}/pakai", [
        'jumlah' => 4,
    ])->assertCreated();

    expect(Medicine::findOrFail($obat)->stok)->toBe(6);

    $this->actingAs($this->admin)->postJson("/api/v1/uks/obat/{$obat}/pakai", [
        'jumlah' => 7,
    ])->assertStatus(422);
});

it('filter obat stok rendah', function (): void {
    Medicine::create(['nama' => 'Obat A', 'stok' => 2, 'stok_minimum' => 5]);
    Medicine::create(['nama' => 'Obat B', 'stok' => 20, 'stok_minimum' => 5]);

    $res = $this->actingAs($this->admin)->getJson('/api/v1/uks/obat?rendah=1')
        ->assertOk();

    expect($res->json('data'))->toHaveCount(1)
        ->and($res->json('data.0.nama'))->toBe('Obat A');
});

it('data uks terisolasi per sekolah', function (): void {
    HealthVisit::create([
        'student_id' => $this->siswa->id, 'tgl' => now(),
        'keluhan' => 'Batuk.', 'ditangani_oleh' => $this->admin->id,
    ]);
    Medicine::create(['nama' => 'Obat C', 'stok' => 5]);

    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain10-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    app(TenantContext::class)->set($other);

    expect(HealthVisit::count())->toBe(0)
        ->and(Medicine::count())->toBe(0);
});

it('kunjungan, form kunjungan, dan obat uks memakai design system', function (): void {
    Livewire::actingAs($this->admin)->test(HealthVisitIndex::class)
        ->assertSee('Kunjungan UKS')
        ->assertSee('data-table')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(HealthVisitForm::class)
        ->assertSee('Catat Kunjungan')
        ->assertSee('management-form-card')
        ->assertDontSee('max-w-3xl')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(MedicineIndex::class)
        ->assertSee('Obat UKS')
        ->assertSee('management-page-header')
        ->assertDontSee('<i data-lucide');
});
