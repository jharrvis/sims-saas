<?php

declare(strict_types=1);

use App\Models\Membership;
use App\Models\Module;
use App\Models\School;
use App\Models\Student;
use App\Models\TenantModule;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Tenancy\TenantContext;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Tatib', 'slug' => 'tatib-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'tata_tertib')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'tatibadmin', 'name' => 'Tatib Admin', 'phone' => '628600000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->siswa = Student::create(['nis' => '9601', 'nama' => 'Siswa Tatib', 'jk' => 'L', 'status' => 'aktif']);
    $this->jenis = ViolationType::create(['nama' => 'Terlambat', 'kategori' => 'ringan', 'poin' => 5]);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul tata_tertib nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/tatib')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('mencatat pelanggaran dan menghitung total poin', function (): void {
    $this->actingAs($this->admin)->postJson('/api/v1/tatib', [
        'student_id' => $this->siswa->id,
        'violation_type_id' => $this->jenis->id,
        'tanggal' => now()->toDateString(),
        'keterangan' => 'Terlambat 15 menit',
    ])->assertCreated();

    $berat = ViolationType::create(['nama' => 'Berkelahi', 'kategori' => 'berat', 'poin' => 50]);
    Violation::create([
        'student_id' => $this->siswa->id, 'violation_type_id' => $berat->id,
        'tanggal' => now()->toDateString(),
    ]);

    $rekap = $this->actingAs($this->admin)->getJson('/api/v1/tatib/rekap')->assertOk()->json();

    expect($rekap)->toHaveCount(1);
    expect((int) $rekap[0]['total_poin'])->toBe(55);
    expect((int) $rekap[0]['jumlah'])->toBe(2);
});

it('pelanggaran terisolasi per sekolah', function (): void {
    Violation::create([
        'student_id' => $this->siswa->id, 'violation_type_id' => $this->jenis->id,
        'tanggal' => now()->toDateString(),
    ]);

    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain3-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    app(TenantContext::class)->set($other);

    expect(Violation::count())->toBe(0);
});

it('menghapus catatan pelanggaran', function (): void {
    $v = Violation::create([
        'student_id' => $this->siswa->id, 'violation_type_id' => $this->jenis->id,
        'tanggal' => now()->toDateString(),
    ]);

    $this->actingAs($this->admin)->deleteJson("/api/v1/tatib/{$v->id}")->assertNoContent();

    expect(Violation::count())->toBe(0);
});
