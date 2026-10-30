<?php

declare(strict_types=1);

use App\Models\Membership;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Shell', 'slug' => 'shell-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'shelladmin', 'name' => 'Shell Admin', 'phone' => '628590000023',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('menampilkan overlay loading global dan tombol bantuan melayang, bukan kartu sidebar', function (): void {
    $this->actingAs($this->admin)
        ->get('/guru')
        ->assertOk()
        ->assertSee('data-sims-loading', false)
        ->assertSee('sims-help-fab', false)
        ->assertSee('Menyiapkan formulir...')
        ->assertSee('Menyiapkan data untuk Anda')
        ->assertDontSee('Butuh bantuan?');
});

it('flash sesi diteruskan ke toast lewat jembatan shell', function (): void {
    $this->actingAs($this->admin)
        ->withSession(['sukses' => 'Data guru tersimpan.'])
        ->get('/guru')
        ->assertOk()
        ->assertSee('__simsFlashToasts', false)
        ->assertSee('Data guru tersimpan.')
        ->assertSee('data-flash-alert', false);
});

it('konfirmasi memakai modal modern (data-sims-confirm), bukan dialog browser', function (): void {
    Student::create(['nis' => 'U1', 'nama' => 'Siswa Uji', 'jk' => 'L', 'status' => 'aktif']);

    $this->actingAs($this->admin)
        ->get('/siswa')
        ->assertOk()
        ->assertSee('data-sims-confirm', false)
        ->assertDontSee('wire:confirm', false);
});

it('tidak ada wire:confirm tersisa di seluruh view', function (): void {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(resource_path('views'), FilesystemIterator::SKIP_DOTS)
    );

    foreach ($files as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            expect(file_get_contents($file->getPathname()))
                ->not->toContain('wire:confirm');
        }
    }
});
