<?php

declare(strict_types=1);

use App\Livewire\Pengumuman\AnnouncementForm;
use App\Livewire\Pengumuman\AnnouncementIndex;
use App\Models\Announcement;
use App\Models\Membership;
use App\Models\Module;
use App\Models\School;
use App\Models\TenantModule;
use App\Models\User;
use App\Scopes\SchoolScope;
use App\Tenancy\TenantContext;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Umum', 'slug' => 'umum-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'pengumuman')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'umumadmin', 'name' => 'Umum Admin', 'phone' => '628560000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul pengumuman nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/pengumuman')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('membuat pengumuman sebagai draf lalu menayangkan', function (): void {
    $res = $this->actingAs($this->admin)->postJson('/api/v1/pengumuman', [
        'judul' => 'Upacara Bendera',
        'isi' => 'Senin pukul 07.00 di lapangan.',
        'target_type' => 'semua',
        'tayang_dari' => now()->toDateString(),
    ])->assertCreated();

    expect($res->json('status'))->toBe('draf');

    $id = $res->json('id');
    $this->actingAs($this->admin)->postJson("/api/v1/pengumuman/{$id}/publish")
        ->assertOk()
        ->assertJsonPath('status', 'tayang');
});

it('daftar tayang hanya menampilkan yang sedang tayang', function (): void {
    Announcement::create([
        'judul' => 'Sedang Tayang', 'isi' => 'x', 'target_type' => 'semua',
        'status' => 'tayang', 'tayang_dari' => now()->subDay()->toDateString(),
        'tayang_sampai' => now()->addDay()->toDateString(),
    ]);
    Announcement::create([
        'judul' => 'Masih Draf', 'isi' => 'x', 'target_type' => 'semua',
        'status' => 'draf', 'tayang_dari' => now()->toDateString(),
    ]);
    Announcement::create([
        'judul' => 'Sudah Kedaluwarsa', 'isi' => 'x', 'target_type' => 'semua',
        'status' => 'tayang', 'tayang_dari' => now()->subDays(10)->toDateString(),
        'tayang_sampai' => now()->subDay()->toDateString(),
    ]);

    $data = $this->actingAs($this->admin)->getJson('/api/v1/pengumuman')->assertOk()->json('data');

    expect(collect($data)->pluck('judul')->all())->toBe(['Sedang Tayang']);
});

it('filter penting_saja hanya menampilkan yang penting', function (): void {
    Announcement::create([
        'judul' => 'Biasa', 'isi' => 'x', 'target_type' => 'semua',
        'status' => 'tayang', 'tayang_dari' => now()->toDateString(),
    ]);
    Announcement::create([
        'judul' => 'Darurat', 'isi' => 'x', 'target_type' => 'semua', 'penting' => true,
        'status' => 'tayang', 'tayang_dari' => now()->toDateString(),
    ]);

    $data = $this->actingAs($this->admin)->getJson('/api/v1/pengumuman?penting_saja=1')->assertOk()->json('data');

    expect(collect($data)->pluck('judul')->all())->toBe(['Darurat']);
});

it('pengumuman terisolasi per sekolah', function (): void {
    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    Announcement::create([
        'judul' => 'Milik Sekolah A', 'isi' => 'x', 'target_type' => 'semua',
        'status' => 'tayang', 'tayang_dari' => now()->toDateString(),
    ]);

    app(TenantContext::class)->set($other);

    expect(Announcement::count())->toBe(0);
    expect(Announcement::withoutGlobalScope(SchoolScope::class)->count())->toBe(1);
});

it('pusat bantuan me-render markdown menjadi HTML', function (): void {
    $this->withoutVite();
    $this->actingAs($this->admin)->get('/bantuan')->assertOk()->assertSee('Pusat Bantuan');
    $this->actingAs($this->admin)->get('/bantuan/absensi')->assertOk()->assertSee('Absensi', false);
    $this->actingAs($this->admin)->get('/bantuan/topik-tidak-ada')->assertNotFound();
});

it('pengumuman memakai struktur design system management (Batch 7)', function (): void {
    Livewire::actingAs($this->admin)->test(AnnouncementIndex::class)
        ->assertSee('Pengumuman')
        ->assertSeeHtml('management-page-header')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(AnnouncementForm::class)
        ->assertSee('Buat Pengumuman')
        ->assertSeeHtml('management-form-card')
        ->assertDontSee('max-w-3xl');
});
