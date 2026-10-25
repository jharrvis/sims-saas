<?php

declare(strict_types=1);

use App\Livewire\Aset\AssetForm;
use App\Livewire\Aset\AssetIndex;
use App\Models\Asset;
use App\Models\AssetCategory;
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
        'nama' => 'Sekolah Aset', 'slug' => 'aset-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'inventaris')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'asetadmin', 'name' => 'Aset Admin', 'phone' => '628660000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->kategori = AssetCategory::create(['kode' => 'ELK', 'nama' => 'Elektronik']);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul inventaris nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/inventaris')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('tambah aset otomatis mencatat mutasi masuk', function (): void {
    $res = $this->actingAs($this->admin)->postJson('/api/v1/inventaris', [
        'asset_category_id' => $this->kategori->id,
        'kode' => 'AST-001',
        'nama' => 'Proyektor Epson',
        'lokasi' => 'Ruang Guru',
        'harga_beli' => 5500000,
    ])->assertCreated();

    $asset = Asset::findOrFail($res->json('id'));

    expect($asset->mutations)->toHaveCount(1)
        ->and($asset->mutations->first()->jenis)->toBe('masuk');
});

it('mutasi pindah memperbarui lokasi; mutasi kondisi memperbarui kondisi', function (): void {
    $asset = Asset::create([
        'asset_category_id' => $this->kategori->id,
        'kode' => 'AST-002', 'nama' => 'Laptop', 'lokasi' => 'Lab Komputer',
    ]);

    $this->actingAs($this->admin)->postJson("/api/v1/inventaris/{$asset->id}/mutasi", [
        'jenis' => 'pindah', 'dari' => 'Lab Komputer', 'ke' => 'Ruang TU',
    ])->assertCreated();

    expect($asset->fresh()->lokasi)->toBe('Ruang TU');

    $this->actingAs($this->admin)->postJson("/api/v1/inventaris/{$asset->id}/mutasi", [
        'jenis' => 'kondisi', 'ke' => 'rusak_ringan', 'catatan' => 'Keyboard rusak.',
    ])->assertCreated();

    expect($asset->fresh()->kondisi)->toBe('rusak_ringan')
        ->and($asset->mutations()->count())->toBe(2);
});

it('kode aset unik per sekolah', function (): void {
    Asset::create([
        'asset_category_id' => $this->kategori->id, 'kode' => 'AST-003', 'nama' => 'Meja',
    ]);

    $this->actingAs($this->admin)->postJson('/api/v1/inventaris', [
        'asset_category_id' => $this->kategori->id,
        'kode' => 'AST-003',
        'nama' => 'Kursi',
    ])->assertUnprocessable();
});

it('aset terisolasi per sekolah', function (): void {
    Asset::create([
        'asset_category_id' => $this->kategori->id, 'kode' => 'AST-004', 'nama' => 'Papan Tulis',
    ]);

    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain9-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    app(TenantContext::class)->set($other);

    expect(Asset::count())->toBe(0)
        ->and(AssetCategory::count())->toBe(0);
});

it('aset memakai struktur design system management (Batch 9)', function (): void {
    Livewire::actingAs($this->admin)->test(AssetIndex::class)
        ->assertSee('Data Aset')
        ->assertSeeHtml('management-page-header')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(AssetForm::class)
        ->assertSee('Tambah Aset')
        ->assertSeeHtml('management-form-card')
        ->assertDontSee('max-w-3xl');
});
