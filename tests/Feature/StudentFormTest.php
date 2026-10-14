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
