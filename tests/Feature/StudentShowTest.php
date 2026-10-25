<?php

declare(strict_types=1);

use App\Models\Guardian;
use App\Models\Membership;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->withoutVite();

    $this->school = School::create([
        'nama' => 'Sekolah Profil', 'slug' => 'profil-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'profiladmin', 'name' => 'Admin Profil', 'phone' => '628590000030',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('superadmin');

    $this->siswa = Student::create([
        'nis' => '9101', 'nama' => 'Siswa Profil', 'jk' => 'L', 'status' => 'aktif',
    ]);

    $this->wali = Guardian::create([
        'nik' => '456001', 'nama' => 'Wali Profil', 'hubungan' => 'ibu', 'no_wa' => '628555000009',
    ]);
    $this->wali->students()->attach($this->siswa->id, ['hubungan' => 'ibu']);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('membuka detail siswa 200 untuk admin dengan siswa.view dan wali-murid.link', function (): void {
    $this->actingAs($this->admin)
        ->get('/siswa/'.$this->siswa->id)
        ->assertOk()
        ->assertSee('Siswa Profil')
        ->assertSee('9101');
});

it('wali yang sudah tertaut tetap tampil pada detail siswa', function (): void {
    $this->actingAs($this->admin)
        ->get('/siswa/'.$this->siswa->id)
        ->assertOk()
        ->assertSee('Wali Profil')
        ->assertSee('Ibu')
        ->assertSee('628555000009');
});

it('render tidak memuat referensi attach/detach/consent-panel yang menggantung', function (): void {
    $html = $this->actingAs($this->admin)
        ->get('/siswa/'.$this->siswa->id)
        ->assertOk()
        ->getContent();

    foreach (['attachGuardian', 'detachGuardian', 'guardian_id', 'consent-panel', 'wire:model="hubungan"'] as $referensi) {
        expect($html)->not->toContain($referensi);
    }
});

it('siswa milik tenant lain menghasilkan 404', function (): void {
    $sekolahLain = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(TenantContext::class)->set($sekolahLain);
    $siswaLain = Student::create([
        'nis' => '9901', 'nama' => 'Siswa Tenant Lain', 'jk' => 'P', 'status' => 'aktif',
    ]);
    app(TenantContext::class)->set($this->school);

    $this->actingAs($this->admin)->get('/siswa/'.$siswaLain->id)->assertNotFound();
});
