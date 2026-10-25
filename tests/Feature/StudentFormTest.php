<?php

declare(strict_types=1);

use App\Livewire\Kesiswaan\StudentForm;
use App\Models\School;
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
    Livewire::test(StudentForm::class)
        ->set('nis', '9901')
        ->set('nama', 'Siswa Agama')
        ->set('jk', 'L')
        ->set('agama', 'Atheis')
        ->call('save')
        ->assertHasErrors(['agama']);
});
