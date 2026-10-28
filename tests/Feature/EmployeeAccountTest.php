<?php

declare(strict_types=1);

use App\Livewire\Kepegawaian\EmployeeAccountForm;
use App\Livewire\Kepegawaian\EmployeeIndex;
use App\Livewire\Pengaturan\PenggunaIndex;
use App\Models\Employee;
use App\Models\Membership;
use App\Models\School;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Akun', 'slug' => 'akun-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'akunadmin', 'name' => 'Akun Admin', 'phone' => '628590000021',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');
    $this->admin->givePermissionTo('pengguna.create');

    $this->guru = Employee::create([
        'nip_nuptk' => '67890', 'nama' => 'Guru Akun', 'jenis' => 'guru_mapel',
        'status_kepegawaian' => 'honorer', 'jabatan' => 'Guru Matematika',
        'phone' => '628120000002', 'email' => 'guru.akun@contoh.sch.id', 'status_aktif' => true,
    ]);

    $this->tendik = Employee::create([
        'nip_nuptk' => '67891', 'nama' => 'Tendik Akun', 'jenis' => 'tendik',
        'status_kepegawaian' => 'honorer', 'jabatan' => 'Staf TU',
        'phone' => '628120000003', 'status_aktif' => true,
    ]);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('menampilkan form akun dengan peran bawaan sesuai jenis', function (): void {
    $this->actingAs($this->admin)
        ->get('/guru/'.$this->guru->id.'/akun')
        ->assertOk()
        ->assertSee('Buatkan Akun Login')
        ->assertSee('Guru Akun');

    Livewire::test(EmployeeAccountForm::class, ['employee' => $this->guru])
        ->assertSet('roles', ['guru-mapel'])
        ->assertSet('phone', '628120000002')
        ->assertSet('email', 'guru.akun@contoh.sch.id');

    Livewire::test(EmployeeAccountForm::class, ['employee' => $this->tendik])
        ->assertSet('roles', ['tata-usaha']);
});

it('membuat akun login yang tertaut ke data guru', function (): void {
    Livewire::actingAs($this->admin)
        ->test(EmployeeAccountForm::class, ['employee' => $this->guru])
        ->set('username', 'guruakun')
        ->set('password', 'rahasia123')
        ->set('password_confirmation', 'rahasia123')
        ->call('simpan')
        ->assertRedirect('/guru/'.$this->guru->id);

    $user = User::where('username', 'guruakun')->firstOrFail();
    expect($user->employee_id)->toBe($this->guru->id)
        ->and($user->name)->toBe('Guru Akun')
        ->and($user->hasRole('guru-mapel'))->toBeTrue();

    $membership = Membership::where('user_id', $user->id)
        ->where('school_id', $this->school->id)->firstOrFail();
    expect($membership->status)->toBe('aktif');

    $this->assertDatabaseHas('audit_logs', [
        'aksi' => 'pengguna.create',
        'model_id' => (string) $user->id,
    ]);
});

it('membuat membership nonaktif untuk pegawai nonaktif', function (): void {
    $nonaktif = Employee::create([
        'nip_nuptk' => '67892', 'nama' => 'Guru Nonaktif', 'jenis' => 'guru_mapel',
        'status_kepegawaian' => 'honorer', 'phone' => '628120000004', 'status_aktif' => false,
    ]);

    Livewire::actingAs($this->admin)
        ->test(EmployeeAccountForm::class, ['employee' => $nonaktif])
        ->set('username', 'gurunonaktif')
        ->set('password', 'rahasia123')
        ->set('password_confirmation', 'rahasia123')
        ->call('simpan')
        ->assertRedirect('/guru/'.$nonaktif->id);

    $user = User::where('username', 'gurunonaktif')->firstOrFail();
    $membership = Membership::where('user_id', $user->id)
        ->where('school_id', $this->school->id)->firstOrFail();
    expect($membership->status)->toBe('dinonaktifkan');
});

it('menolak akun kedua untuk guru yang sudah tertaut', function (): void {
    $user = User::create([
        'username' => 'gurusudah', 'name' => 'Guru Akun', 'employee_id' => $this->guru->id,
    ]);
    Membership::create(['user_id' => $user->id, 'school_id' => $this->school->id, 'status' => 'aktif']);

    $this->actingAs($this->admin)
        ->get('/guru/'.$this->guru->id.'/akun')
        ->assertRedirect('/guru/'.$this->guru->id);

    expect(User::where('employee_id', $this->guru->id)->count())->toBe(1);
});

it('menolak pembuatan akun tanpa hak pengguna.create', function (): void {
    $guruUser = User::create([
        'username' => 'penggunabiasa', 'name' => 'Guru Biasa', 'phone' => '628590000022',
    ]);
    Membership::create(['user_id' => $guruUser->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $guruUser->assignRole('guru-mapel');

    $this->actingAs($guruUser)
        ->get('/guru/'.$this->tendik->id.'/akun')
        ->assertForbidden();
});

it('menolak username yang sudah dipakai', function (): void {
    User::create(['username' => 'bentrok', 'name' => 'Pemilik Nama']);

    Livewire::actingAs($this->admin)
        ->test(EmployeeAccountForm::class, ['employee' => $this->guru])
        ->set('username', 'bentrok')
        ->set('password', 'rahasia123')
        ->set('password_confirmation', 'rahasia123')
        ->call('simpan')
        ->assertHasErrors('username');

    expect(User::where('username', 'bentrok')->count())->toBe(1);
});

it('menampilkan username tertaut di daftar guru dan nama pegawai di daftar pengguna', function (): void {
    $user = User::create([
        'username' => 'guruakun', 'name' => 'Guru Akun', 'employee_id' => $this->guru->id,
    ]);
    Membership::create(['user_id' => $user->id, 'school_id' => $this->school->id, 'status' => 'aktif']);

    Livewire::actingAs($this->admin)
        ->test(EmployeeIndex::class)
        ->assertSee('guruakun');

    Livewire::actingAs($this->admin)
        ->test(PenggunaIndex::class)
        ->assertSee('Guru/Pegawai: Guru Akun');
});
