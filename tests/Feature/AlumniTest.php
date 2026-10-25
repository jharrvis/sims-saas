<?php

declare(strict_types=1);

use App\Livewire\Alumni\AlumniForm;
use App\Livewire\Alumni\AlumniIndex;
use App\Livewire\Alumni\AlumniShow;
use App\Models\Alumni;
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
        'nama' => 'Sekolah Alumni', 'slug' => 'alumni-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'alumni')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'alumnixadmin', 'name' => 'Alumni Admin', 'phone' => '628710000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul alumni nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/alumni')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('mencatat alumni dan riwayat karier; karier terakhir yang terbaru', function (): void {
    $alumniId = $this->actingAs($this->admin)->postJson('/api/v1/alumni', [
        'nama' => 'Budi Santoso',
        'tahun_lulus' => '2023',
        'phone' => '628120000001',
    ])->assertCreated()->json('id');

    $this->actingAs($this->admin)->postJson("/api/v1/alumni/{$alumniId}/karier", [
        'status' => 'kuliah',
        'institusi' => 'Universitas Indonesia',
        'jurusan_posisi' => 'Informatika',
        'tahun_mulai' => '2023',
    ])->assertCreated();

    $this->actingAs($this->admin)->postJson("/api/v1/alumni/{$alumniId}/karier", [
        'status' => 'kerja',
        'institusi' => 'PT Maju Jaya',
        'jurusan_posisi' => 'Programmer',
        'tahun_mulai' => '2027',
    ])->assertCreated();

    $alumni = Alumni::findOrFail($alumniId);

    expect($alumni->karierTerakhir()?->status)->toBe('kerja')
        ->and($alumni->careers()->count())->toBe(2);
});

it('statistik tracer study menghitung distribusi status', function (): void {
    $a1 = Alumni::create(['nama' => 'A1', 'tahun_lulus' => '2024']);
    $a1->careers()->create(['status' => 'kuliah', 'institusi' => 'UI']);
    $a2 = Alumni::create(['nama' => 'A2', 'tahun_lulus' => '2024']);
    $a2->careers()->create(['status' => 'kerja', 'institusi' => 'PT X']);
    Alumni::create(['nama' => 'A3', 'tahun_lulus' => '2024']);

    $res = $this->actingAs($this->admin)->getJson('/api/v1/alumni/statistik')
        ->assertOk()->json();

    expect($res['total_alumni'])->toBe(3)
        ->and($res['distribusi'])->toBe([
            'kuliah' => 1, 'kerja' => 1, 'belum_terdata' => 1,
        ]);
});

it('alumni terisolasi per sekolah', function (): void {
    Alumni::create(['nama' => 'Alumni A']);

    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain14-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    app(TenantContext::class)->set($other);

    expect(Alumni::count())->toBe(0);
});

it('alumni, form alumni, dan detail alumni memakai design system', function (): void {
    Livewire::actingAs($this->admin)->test(AlumniIndex::class)
        ->assertSee('Alumni')
        ->assertSee('management-page-header')
        ->assertSee('data-table')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(AlumniForm::class)
        ->assertSee('Tambah Alumni')
        ->assertSee('management-form-card')
        ->assertDontSee('max-w-3xl')
        ->assertDontSee('<i data-lucide');

    $alumni = Alumni::create(['nama' => 'Alumni Satu', 'tahun_lulus' => '2025']);

    Livewire::actingAs($this->admin)->test(AlumniShow::class, ['alumni' => $alumni])
        ->assertSee('Alumni Satu')
        ->assertSee('Riwayat Karier')
        ->assertDontSee('<i data-lucide');
});
