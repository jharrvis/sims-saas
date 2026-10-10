<?php

declare(strict_types=1);

use App\Models\Membership;
use App\Models\Module;
use App\Models\School;
use App\Models\Student;
use App\Models\TenantModule;
use App\Models\TransportRoute;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Transport', 'slug' => 'tr-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'transportasi')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'tradmin', 'name' => 'TR Admin', 'phone' => '628680000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->siswa = Student::create(['nis' => '9301', 'nama' => 'Siswa Transport', 'jk' => 'L', 'status' => 'aktif']);
    $this->siswa2 = Student::create(['nis' => '9302', 'nama' => 'Siswa Transport 2', 'jk' => 'P', 'status' => 'aktif']);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul transportasi nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/transportasi')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('titik penjemputan terurut otomatis', function (): void {
    $route = TransportRoute::create(['nama' => 'Rute Utara', 'kapasitas' => 20]);

    foreach (['Terminal A', 'Pasar B', 'Sekolah'] as $nama) {
        $this->actingAs($this->admin)->postJson("/api/v1/transportasi/{$route->id}/titik", [
            'nama' => $nama,
        ])->assertCreated();
    }

    expect(
        $route->stops()->pluck('urutan')->all()
    )->toBe([1, 2, 3]);
});

it('kapasitas penuh menolak penumpang baru; duplikat idempoten', function (): void {
    $route = TransportRoute::create(['nama' => 'Rute Penuh', 'kapasitas' => 1]);

    $this->actingAs($this->admin)->postJson("/api/v1/transportasi/{$route->id}/penumpang", [
        'student_id' => $this->siswa->id,
    ])->assertOk();

    $this->actingAs($this->admin)->postJson("/api/v1/transportasi/{$route->id}/penumpang", [
        'student_id' => $this->siswa2->id,
    ])->assertUnprocessable()->assertJsonPath('code', 'RUTE_PENUH');

    $route2 = TransportRoute::create(['nama' => 'Rute Longgar', 'kapasitas' => 5]);

    $this->actingAs($this->admin)->postJson("/api/v1/transportasi/{$route2->id}/penumpang", [
        'student_id' => $this->siswa->id,
    ])->assertOk();

    // tambah penumpang yang sama lagi: tetap 1 (idempoten)
    $this->actingAs($this->admin)->postJson("/api/v1/transportasi/{$route2->id}/penumpang", [
        'student_id' => $this->siswa->id,
    ])->assertOk();

    expect(
        $route2->members()->wherePivot('status', 'aktif')->count()
    )->toBe(1);
});

it('rute terisolasi per sekolah', function (): void {
    TransportRoute::create(['nama' => 'Rute A']);

    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain11-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    app(TenantContext::class)->set($other);

    expect(TransportRoute::count())->toBe(0);
});
