<?php

declare(strict_types=1);

use App\Livewire\Bk\CounselingForm;
use App\Livewire\Bk\CounselingIndex;
use App\Models\CounselingSession;
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
        'nama' => 'Sekolah BK', 'slug' => 'bk-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'bk_konseling')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'bkadmin', 'name' => 'BK Admin', 'phone' => '628610000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->siswa = Student::create(['nis' => '9701', 'nama' => 'Siswa BK', 'jk' => 'L', 'status' => 'aktif']);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul bk_konseling nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/bk')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('menjadwalkan sesi konseling lalu menandai selesai', function (): void {
    $id = $this->actingAs($this->admin)->postJson('/api/v1/bk', [
        'student_id' => $this->siswa->id,
        'tanggal' => now()->toDateString(),
        'jenis' => 'individu',
        'topik' => 'Motivasi belajar',
    ])->assertCreated()->json('id');

    expect(CounselingSession::find($id)->status)->toBe('terjadwal');

    $this->actingAs($this->admin)->putJson("/api/v1/bk/{$id}", [
        'status' => 'selesai',
        'catatan' => 'Siswa menunjukkan progres baik.',
        'tindak_lanjut' => 'Pemantauan 2 minggu.',
    ])->assertOk()->assertJsonPath('status', 'selesai');
});

it('sesi konseling terisolasi per sekolah', function (): void {
    CounselingSession::create([
        'student_id' => $this->siswa->id,
        'tanggal' => now()->toDateString(),
        'topik' => 'Rahasia',
    ]);

    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain4-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    app(TenantContext::class)->set($other);

    expect(CounselingSession::count())->toBe(0);
});

it('guru bk memiliki akses penuh sedangkan wali kelas hanya lihat', function (): void {
    $bk = User::create(['username' => 'gurubk1', 'name' => 'Guru BK', 'phone' => '628610000002']);
    Membership::create(['user_id' => $bk->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $bk->assignRole('guru-bk');

    expect($bk->can('bk.create'))->toBeTrue();
    expect($bk->can('bk.delete'))->toBeTrue();

    $wk = User::create(['username' => 'walikelas1', 'name' => 'Wali Kelas', 'phone' => '628610000003']);
    Membership::create(['user_id' => $wk->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $wk->assignRole('wali-kelas');

    expect($wk->can('bk.view'))->toBeTrue();
    expect($wk->can('bk.create'))->toBeFalse();
});

it('sesi konseling dan form konseling memakai design system', function (): void {
    Livewire::actingAs($this->admin)->test(CounselingIndex::class)
        ->assertSee('Konseling')
        ->assertSee('management-page-header')
        ->assertSee('rahasia')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(CounselingForm::class)
        ->assertSee('Jadwalkan Konseling')
        ->assertSee('management-form-card')
        ->assertDontSee('max-w-3xl')
        ->assertDontSee('<i data-lucide');
});
