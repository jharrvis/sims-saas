<?php

declare(strict_types=1);

use App\Models\Membership;
use App\Models\School;
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
        ->assertSee('Mohon tunggu sebentar.')
        ->assertDontSee('Butuh bantuan?');
});
