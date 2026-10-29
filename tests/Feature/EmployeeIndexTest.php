<?php

declare(strict_types=1);

use App\Livewire\Kepegawaian\EmployeeForm;
use App\Livewire\Kepegawaian\EmployeeIndex;
use App\Models\AcademicYear;
use App\Models\Employee;
use App\Models\Membership;
use App\Models\Rombel;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use App\Scopes\SchoolScope;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Kepegawaian', 'slug' => 'kep-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->guruSatu = Employee::create([
        'nip_nuptk' => '11111', 'nama' => 'Guru Satu', 'jenis' => 'guru_mapel',
        'status_kepegawaian' => 'pns', 'jabatan' => 'Wali Kelas', 'phone' => '628111111111',
        'status_aktif' => true,
    ]);

    $this->pegawaiDua = Employee::create([
        'nip_nuptk' => '22222', 'nama' => 'Pegawai Dua', 'jenis' => 'tendik',
        'status_kepegawaian' => 'honorer', 'jabatan' => 'Operator', 'phone' => '628222222222',
        'status_aktif' => false,
    ]);

    $this->admin = User::create([
        'username' => 'kepadmin-'.uniqid(), 'name' => 'Admin Kepegawaian',
        'phone' => '62851'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    // View-only: guru.view saja, tanpa guru.create / guru.update / guru.delete.
    $this->viewer = User::create([
        'username' => 'kepviewer-'.uniqid(), 'name' => 'Petugas Data',
        'phone' => '62852'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->viewer->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->viewer->assignRole('tata-usaha');

    // Tidak memiliki satu pun permission guru.* — tidak boleh membuka direktori.
    $this->guruMapel = User::create([
        'username' => 'kepguru-'.uniqid(), 'name' => 'Guru Mapel',
        'phone' => '62853'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->guruMapel->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->guruMapel->assignRole('guru-mapel');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('halaman daftar guru menampilkan ringkasan, data, dan kendali admin', function (): void {
    $this->actingAs($this->admin)
        ->get('/guru')
        ->assertOk()
        ->assertSee('Data Guru & Pegawai')
        ->assertSee('Guru Satu')
        ->assertSee('Pegawai Dua')
        ->assertSee('Tambah Guru');
});

it('filter status memetakan secara eksplisit ke kolom boolean status_aktif', function (): void {
    Livewire::actingAs($this->admin)
        ->test(EmployeeIndex::class)
        ->set('status', 'aktif')
        ->assertSee('Guru Satu')
        ->assertDontSee('Pegawai Dua')
        ->set('status', 'nonaktif')
        ->assertSee('Pegawai Dua')
        ->assertDontSee('Guru Satu')
        ->set('status', '')
        ->assertSee('Guru Satu')
        ->assertSee('Pegawai Dua');
});

it('filter jenis dan pencarian membatasi daftar, reset mengembalikannya', function (): void {
    Livewire::actingAs($this->admin)
        ->test(EmployeeIndex::class)
        ->set('jenis', 'tendik')
        ->assertSee('Pegawai Dua')
        ->assertDontSee('Guru Satu')
        ->set('search', '9999')
        ->assertDontSee('Pegawai Dua')
        ->assertSee('Guru/pegawai tidak ditemukan')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('jenis', '')
        ->assertSet('status', '')
        ->assertSee('Guru Satu')
        ->assertSee('Pegawai Dua');
});

it('pengguna view-only melihat data tanpa kendali tambah, ubah, hapus, dan sampah', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(EmployeeIndex::class)
        ->assertSee('Guru Satu')
        ->assertSeeHtml(route('guru.show', $this->guruSatu))
        ->assertDontSee('Tambah Guru')
        ->assertDontSee('Buka Sampah')
        ->assertDontSee('Ubah data')
        ->assertDontSeeHtml(route('guru.edit', $this->guruSatu));
});

it('pengguna tanpa permission guru.view tidak dapat membuka direktori guru', function (): void {
    $this->actingAs($this->guruMapel)
        ->get('/guru')
        ->assertForbidden();
});

it('aksi hapus langsung menolak pengguna tanpa permission guru.delete', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(EmployeeIndex::class)
        ->call('delete', $this->guruSatu->id)
        ->assertForbidden();

    expect(Employee::withTrashed()->find($this->guruSatu->id)->trashed())->toBeFalse();
});

it('membuka mode sampah langsung menolak pengguna view-only', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(EmployeeIndex::class)
        ->call('toggleTrashed')
        ->assertForbidden();
});

it('pembaruan properti showTrashed langsung menolak pengguna view-only', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(EmployeeIndex::class)
        ->set('showTrashed', true)
        ->assertForbidden();
});

it('data terhapus tidak terlihat bagi view-only, terlihat bagi pemegang delete di mode sampah', function (): void {
    $this->guruSatu->delete();

    Livewire::actingAs($this->viewer)
        ->test(EmployeeIndex::class)
        ->assertSee('Pegawai Dua')
        ->assertDontSee('Guru Satu');

    Livewire::actingAs($this->admin)
        ->test(EmployeeIndex::class)
        ->call('toggleTrashed')
        ->assertSet('showTrashed', true)
        ->assertSee('Guru Satu')
        ->assertDontSee('Pegawai Dua');
});

it('aksi simpan form baru menolak pengguna tanpa permission guru.create', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(EmployeeForm::class)
        ->set('nama', 'Guru Tanpa Izin')
        ->call('save')
        ->assertForbidden();

    expect(Employee::where('nama', 'Guru Tanpa Izin')->exists())->toBeFalse();
});

it('aksi simpan form ubah menolak pengguna tanpa permission guru.update', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(EmployeeForm::class, ['employee' => $this->guruSatu])
        ->set('nama', 'Nama Diubah Paksa')
        ->call('save')
        ->assertForbidden();

    expect(Employee::find($this->guruSatu->id)->nama)->toBe('Guru Satu');
});

it('form guru menolak jenis dan status kepegawaian yang tidak valid', function (): void {
    Livewire::actingAs($this->admin)
        ->test(EmployeeForm::class)
        ->set('nama', 'Guru Status Aneh')
        ->set('jenis', 'guru_asing')
        ->set('status_kepegawaian', 'karyawan lepas')
        ->call('save')
        ->assertHasErrors(['jenis', 'status_kepegawaian']);

    expect(Employee::where('nama', 'Guru Status Aneh')->exists())->toBeFalse();
});

it('mode sampah: hapus, pulihkan, lalu hapus permanen', function (): void {
    $component = Livewire::actingAs($this->admin)->test(EmployeeIndex::class);

    $component->call('delete', $this->guruSatu->id)
        ->assertSet('showTrashed', false);
    expect(Employee::withTrashed()->find($this->guruSatu->id)->trashed())->toBeTrue();

    $component->call('toggleTrashed')->assertSet('showTrashed', true);
    $component->call('restore', $this->guruSatu->id);
    expect(Employee::withTrashed()->find($this->guruSatu->id)->trashed())->toBeFalse();

    $component->call('delete', $this->guruSatu->id);
    $component->call('forceDelete', $this->guruSatu->id);
    expect(Employee::withTrashed()->find($this->guruSatu->id))->toBeNull();
});

it('foto bertahan saat soft delete dan pulih, hanya terhapus saat hapus permanen', function (): void {
    Storage::fake('public');

    $path = 'guru-foto/'.$this->school->id.'/contoh.jpg';
    Storage::disk('public')->put($path, 'konten-foto');

    $berfoto = Employee::create([
        'nama' => 'Guru Berfoto', 'jenis' => 'guru_mapel', 'status_kepegawaian' => 'pns',
        'status_aktif' => true, 'foto_path' => $path,
    ]);

    $component = Livewire::actingAs($this->admin)->test(EmployeeIndex::class);

    $component->call('delete', $berfoto->id);
    expect(Storage::disk('public')->exists($path))->toBeTrue();

    $component->call('restore', $berfoto->id);
    expect(Storage::disk('public')->exists($path))->toBeTrue()
        ->and(Employee::withTrashed()->find($berfoto->id)->foto_path)->toBe($path);

    $component->call('delete', $berfoto->id);
    $component->call('forceDelete', $berfoto->id);

    expect(Storage::disk('public')->exists($path))->toBeFalse()
        ->and(Employee::withTrashed()->find($berfoto->id))->toBeNull();
});

it('daftar guru mengabaikan data milik sekolah lain', function (): void {
    $lain = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    $asing = Employee::create([
        'school_id' => $lain->id, 'nip_nuptk' => '99999', 'nama' => 'Guru Sekolah Lain',
        'jenis' => 'guru_mapel', 'status_kepegawaian' => 'honorer', 'status_aktif' => true,
    ]);

    Livewire::actingAs($this->admin)
        ->test(EmployeeIndex::class)
        ->assertSee('Guru Satu')
        ->assertDontSee('Guru Sekolah Lain');

    // SchoolScope membuat baris tenant lain tidak terjangkau: delete harus
    // menjawab 404 secara eksplisit, bukan lolos hijau karena exception lain.
    Livewire::actingAs($this->admin)
        ->test(EmployeeIndex::class)
        ->call('delete', $asing->id)
        ->assertNotFound();

    $masihAda = Employee::withoutGlobalScope(SchoolScope::class)
        ->withTrashed()
        ->find($asing->id);

    expect($masihAda)->not->toBeNull()
        ->and($masihAda->trashed())->toBeFalse();
});

it('daftar kosong menampilkan empty state dan reset filter hanya muncul saat filter aktif', function (): void {
    Employee::withTrashed()->forceDelete();

    Livewire::actingAs($this->admin)
        ->test(EmployeeIndex::class)
        ->assertSee('Guru/pegawai tidak ditemukan')
        ->assertDontSee('Reset filter')
        ->set('search', 'tidak-ada')
        ->assertSee('Reset filter');
});

it('tombol impor dan ekspor tidak dirender selama route belum tersedia', function (): void {
    Livewire::actingAs($this->admin)
        ->test(EmployeeIndex::class)
        ->assertDontSee('Impor Data')
        ->assertDontSee('Ekspor Data');
});

it('akun tertaut kehilangan akses saat pegawai dihapus dan kembali saat dipulihkan (QA B-01)', function (): void {
    $akun = User::create([
        'username' => 'gurusatu-akun', 'name' => 'Guru Satu', 'employee_id' => $this->guruSatu->id,
    ]);
    $membership = Membership::create([
        'user_id' => $akun->id, 'school_id' => $this->school->id, 'status' => 'aktif',
    ]);

    $component = Livewire::actingAs($this->admin)->test(EmployeeIndex::class);

    $component->call('delete', $this->guruSatu->id);
    expect($membership->fresh()->status)->toBe('dinonaktifkan');

    $component->call('restore', $this->guruSatu->id);
    expect($membership->fresh()->status)->toBe('aktif');
});

it('pengguna guru.delete tanpa force-delete ditolak saat hapus permanen (QA B-03)', function (): void {
    $penghapus = User::create([
        'username' => 'penghapus-guru-'.uniqid(), 'name' => 'Penghapus Guru',
        'phone' => '62854'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $penghapus->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $penghapus->givePermissionTo(['guru.view', 'guru.delete']);

    $this->guruSatu->delete();

    Livewire::actingAs($penghapus)
        ->test(EmployeeIndex::class)
        ->call('forceDelete', $this->guruSatu->id)
        ->assertForbidden();

    expect(Employee::withTrashed()->find($this->guruSatu->id))->not->toBeNull();
});

it('pegawai dengan jadwal mengajar tidak dapat dihapus permanen (QA B-03)', function (): void {
    $ta = AcademicYear::create([
        'nama' => '2026/2027', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);
    $rombel = Rombel::create(['nama' => 'VII A', 'academic_year_id' => $ta->id, 'tingkat' => '7']);
    $mapel = Subject::create(['kode' => 'MTK', 'nama' => 'Matematika', 'kelompok' => 'wajib']);
    Schedule::create([
        'rombel_id' => $rombel->id, 'academic_year_id' => $ta->id, 'hari' => 'senin',
        'jam_ke' => 1, 'subject_id' => $mapel->id, 'employee_id' => $this->guruSatu->id,
    ]);

    $component = Livewire::actingAs($this->admin)->test(EmployeeIndex::class);
    $component->call('delete', $this->guruSatu->id);
    $component->call('forceDelete', $this->guruSatu->id)
        ->assertSee('tidak dapat dihapus permanen');

    expect(Employee::withTrashed()->find($this->guruSatu->id))->not->toBeNull();
});

it('pegawai yang masih wali kelas tidak dapat dihapus permanen (QA B-03)', function (): void {
    $ta = AcademicYear::create([
        'nama' => '2026/2027', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);
    Rombel::create([
        'nama' => 'VII B', 'academic_year_id' => $ta->id, 'tingkat' => '7',
        'wali_kelas_id' => $this->guruSatu->id,
    ]);

    $component = Livewire::actingAs($this->admin)->test(EmployeeIndex::class);
    $component->call('delete', $this->guruSatu->id);
    $component->call('forceDelete', $this->guruSatu->id)
        ->assertSee('wali kelas');

    expect(Employee::withTrashed()->find($this->guruSatu->id))->not->toBeNull();
});

it('hapus permanen pegawai berakun menonaktifkan membership dan melepas tautan (QA B-03)', function (): void {
    $akun = User::create([
        'username' => 'gurusatu-permanen', 'name' => 'Guru Satu', 'employee_id' => $this->guruSatu->id,
    ]);
    $membership = Membership::create([
        'user_id' => $akun->id, 'school_id' => $this->school->id, 'status' => 'aktif',
    ]);

    $component = Livewire::actingAs($this->admin)->test(EmployeeIndex::class);
    $component->call('delete', $this->guruSatu->id);
    $component->call('forceDelete', $this->guruSatu->id);

    expect(Employee::withTrashed()->find($this->guruSatu->id))->toBeNull()
        ->and($akun->fresh()->employee_id)->toBeNull()
        ->and($membership->fresh()->status)->toBe('dinonaktifkan');
});

it('akun tertaut mengikuti status_aktif yang diubah lewat form (QA B-01)', function (): void {
    $akun = User::create([
        'username' => 'gurusatu-form', 'name' => 'Guru Satu', 'employee_id' => $this->guruSatu->id,
    ]);
    $membership = Membership::create([
        'user_id' => $akun->id, 'school_id' => $this->school->id, 'status' => 'aktif',
    ]);

    Livewire::actingAs($this->admin)
        ->test(EmployeeForm::class, ['employee' => $this->guruSatu])
        ->set('status_aktif', false)
        ->call('save');
    expect($membership->fresh()->status)->toBe('dinonaktifkan');

    Livewire::actingAs($this->admin)
        ->test(EmployeeForm::class, ['employee' => $this->guruSatu->fresh()])
        ->set('status_aktif', true)
        ->call('save');
    expect($membership->fresh()->status)->toBe('aktif');
});
