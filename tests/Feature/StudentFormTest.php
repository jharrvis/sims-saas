<?php

declare(strict_types=1);

use App\Livewire\Kesiswaan\StudentForm;
use App\Models\AcademicYear;
use App\Models\Membership;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Form', 'slug' => 'form-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'formadmin', 'name' => 'Admin Form', 'phone' => '628990000101',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('form siswa menampilkan area drag and drop foto', function (): void {
    Livewire::test(StudentForm::class)
        ->assertSee('Seret')
        ->assertSeeHtml('foto-input')
        ->assertSeeHtml('dataTransfer');
});

it('form siswa memakai struktur design system management', function (): void {
    Livewire::test(StudentForm::class)
        ->assertSee('Kembali ke Data Siswa')
        ->assertSeeHtml('management-form-card')
        ->assertSeeHtml('management-form-grid')
        ->assertDontSee('max-w-3xl')
        ->assertDontSee('bg-boxdark');
});

it('dropdown agama berisi 6 agama resmi Indonesia', function (): void {
    Livewire::test(StudentForm::class)
        ->assertSee('— Pilih Agama —')
        ->assertSee('Islam')
        ->assertSee('Kristen')
        ->assertSee('Katolik')
        ->assertSee('Hindu')
        ->assertSee('Buddha')
        ->assertSee('Konghucu');
});

it('agama di luar daftar resmi ditolak saat simpan', function (): void {
    Livewire::actingAs($this->admin)->test(StudentForm::class)
        ->set('nis', '9901')
        ->set('nama', 'Siswa Agama')
        ->set('jk', 'L')
        ->set('agama', 'Atheis')
        ->call('save')
        ->assertHasErrors(['agama']);
});

it('pengguna view-only tidak bisa simpan form siswa (perbaikan QA P0)', function (): void {
    $viewOnly = User::create([
        'username' => 'formview', 'name' => 'View Only', 'phone' => '628990000102',
    ]);
    Membership::create(['user_id' => $viewOnly->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $viewOnly->assignRole('kepala-sekolah');

    Livewire::actingAs($viewOnly)->test(StudentForm::class)
        ->set('nis', '9902')
        ->set('nama', 'Siswa View')
        ->set('jk', 'L')
        ->call('save')
        ->assertForbidden();
});

it('submit valid berhasil setelah otorisasi dan tahun ajaran aktif (perbaikan QA P0)', function (): void {
    AcademicYear::create([
        'nama' => '2026/2027', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);

    Livewire::actingAs($this->admin)->test(StudentForm::class)
        ->set('nis', '9903')
        ->set('nama', 'Siswa Valid')
        ->set('jk', 'L')
        ->set('agama', 'Islam')
        ->call('save')
        ->assertRedirect(route('siswa.index'));

    $this->assertDatabaseHas('students', ['nis' => '9903', 'school_id' => $this->school->id]);
});

it('NISN yang bukan 10 digit ditolak (QA B-07)', function (): void {
    Livewire::actingAs($this->admin)->test(StudentForm::class)
        ->set('nis', '9910')
        ->set('nama', 'Siswa NISN Pendek')
        ->set('jk', 'L')
        ->set('nisn', '12345')
        ->call('save')
        ->assertHasErrors(['nisn']);

    expect(Student::where('nis', '9910')->exists())->toBeFalse();
});

it('NISN duplikat di sekolah yang sama ditolak (QA B-07)', function (): void {
    Student::create([
        'nis' => '9911', 'nama' => 'Siswa Pertama', 'jk' => 'P', 'nisn' => '0011223344',
    ]);

    Livewire::actingAs($this->admin)->test(StudentForm::class)
        ->set('nis', '9912')
        ->set('nama', 'Siswa Kedua')
        ->set('jk', 'L')
        ->set('nisn', '0011223344')
        ->call('save')
        ->assertHasErrors(['nisn']);

    expect(Student::where('nis', '9912')->exists())->toBeFalse();
});
