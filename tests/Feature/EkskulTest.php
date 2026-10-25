<?php

declare(strict_types=1);

use App\Livewire\Ekskul\AchievementIndex;
use App\Livewire\Ekskul\EkskulForm;
use App\Livewire\Ekskul\EkskulIndex;
use App\Livewire\Ekskul\EkskulShow;
use App\Models\Achievement;
use App\Models\Extracurricular;
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
        'nama' => 'Sekolah Ekskul', 'slug' => 'ekskul-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'ekstrakurikuler')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'ekskuladmin', 'name' => 'Ekskul Admin', 'phone' => '628630000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->siswa = Student::create(['nis' => '9701', 'nama' => 'Siswa Ekskul', 'jk' => 'L', 'status' => 'aktif']);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul ekstrakurikuler nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/ekskul')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('anggota tidak bisa didaftarkan dua kali secara bersamaan', function (): void {
    $ekskul = Extracurricular::create(['nama' => 'Pramuka']);

    $this->actingAs($this->admin)->postJson("/api/v1/ekskul/{$ekskul->id}/anggota", [
        'student_id' => $this->siswa->id,
    ])->assertOk();

    $this->actingAs($this->admin)->postJson("/api/v1/ekskul/{$ekskul->id}/anggota", [
        'student_id' => $this->siswa->id,
    ])->assertOk();

    expect(
        $ekskul->members()->wherePivot('status', 'aktif')->count()
    )->toBe(1);
});

it('mencatat prestasi dan memfilter berdasarkan tingkat', function (): void {
    $this->actingAs($this->admin)->postJson('/api/v1/ekskul/prestasi/data', [
        'student_id' => $this->siswa->id,
        'nama' => 'Juara 1 Olimpiade Matematika',
        'kategori' => 'akademik',
        'tingkat' => 'nasional',
    ])->assertCreated();

    $this->actingAs($this->admin)->postJson('/api/v1/ekskul/prestasi/data', [
        'student_id' => $this->siswa->id,
        'nama' => 'Juara Lomba Futsal Sekolah',
        'kategori' => 'non_akademik',
        'tingkat' => 'sekolah',
    ])->assertCreated();

    $res = $this->actingAs($this->admin)->getJson('/api/v1/ekskul/prestasi/data?tingkat=nasional')
        ->assertOk();

    expect($res->json('data'))->toHaveCount(1)
        ->and($res->json('data.0.nama'))->toBe('Juara 1 Olimpiade Matematika');
});

it('ekstrakurikuler terisolasi per sekolah', function (): void {
    Extracurricular::create(['nama' => 'Paskibra']);
    Achievement::create([
        'student_id' => $this->siswa->id, 'nama' => 'Juara', 'tingkat' => 'sekolah',
    ]);

    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain6-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    app(TenantContext::class)->set($other);

    expect(Extracurricular::count())->toBe(0)
        ->and(Achievement::count())->toBe(0);
});

it('ekskul, form ekskul, kelola anggota, dan prestasi memakai design system', function (): void {
    Livewire::actingAs($this->admin)->test(EkskulIndex::class)
        ->assertSee('Ekstrakurikuler')
        ->assertSee('management-page-header')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(EkskulForm::class)
        ->assertSee('Tambah Ekstrakurikuler')
        ->assertSee('management-form-card')
        ->assertDontSee('max-w-3xl')
        ->assertDontSee('<i data-lucide');

    $ekskul = Extracurricular::create(['nama' => 'Pramuka']);

    Livewire::actingAs($this->admin)->test(EkskulShow::class, ['ekskul' => $ekskul])
        ->assertSee('Pramuka')
        ->assertSee('Tambah Anggota')
        ->assertSee('data-table')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(AchievementIndex::class)
        ->assertSee('Prestasi')
        ->assertSee('data-table')
        ->assertDontSee('<i data-lucide');
});
