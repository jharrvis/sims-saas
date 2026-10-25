<?php

declare(strict_types=1);

use App\Livewire\Kantin\Kasir;
use App\Livewire\Kantin\ProductIndex;
use App\Livewire\Kantin\TransactionIndex;
use App\Models\CanteenProduct;
use App\Models\CanteenTransaction;
use App\Models\Membership;
use App\Models\Module;
use App\Models\School;
use App\Models\TenantModule;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Kantin', 'slug' => 'kantin-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'kantin')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'kantinadmin', 'name' => 'Kantin Admin', 'phone' => '628690000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->nasi = CanteenProduct::create(['nama' => 'Nasi Goreng', 'harga' => 10000, 'stok' => 5]);
    $this->teh = CanteenProduct::create(['nama' => 'Es Teh', 'harga' => 3000, 'stok' => 10]);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul kantin nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/kantin/produk')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('checkout mengurangi stok, menghitung total dan kembalian', function (): void {
    $res = $this->actingAs($this->admin)->postJson('/api/v1/kantin/checkout', [
        'keranjang' => [
            ['product_id' => $this->nasi->id, 'qty' => 2],
            ['product_id' => $this->teh->id, 'qty' => 1],
        ],
        'bayar' => 30000,
    ])->assertCreated();

    expect((float) $res->json('total'))->toBe(23000.0)
        ->and((float) $res->json('kembalian'))->toBe(7000.0)
        ->and($res->json('items'))->toHaveCount(2)
        ->and($this->nasi->fresh()->stok)->toBe(3)
        ->and($this->teh->fresh()->stok)->toBe(9);
});

it('checkout ditolak bila stok kurang atau bayar kurang', function (): void {
    $this->actingAs($this->admin)->postJson('/api/v1/kantin/checkout', [
        'keranjang' => [['product_id' => $this->nasi->id, 'qty' => 99]],
        'bayar' => 1000000,
    ])->assertStatus(422);

    $this->actingAs($this->admin)->postJson('/api/v1/kantin/checkout', [
        'keranjang' => [['product_id' => $this->nasi->id, 'qty' => 1]],
        'bayar' => 5000,
    ])->assertStatus(422);

    // stok tidak berubah karena transaksi gagal
    expect($this->nasi->fresh()->stok)->toBe(5);
});

it('produk kantin terisolasi per sekolah', function (): void {
    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain12-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    app(TenantContext::class)->set($other);

    expect(CanteenProduct::count())->toBe(0)
        ->and(CanteenTransaction::count())->toBe(0);
});

it('kasir, produk, dan transaksi kantin memakai design system', function (): void {
    Livewire::actingAs($this->admin)->test(Kasir::class)
        ->assertSee('Kasir')
        ->assertSee('management-page-header')
        ->assertSee('Keranjang')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(ProductIndex::class)
        ->assertSee('Produk Kantin')
        ->assertSee('management-page-header')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(TransactionIndex::class)
        ->assertSee('Riwayat Transaksi')
        ->assertSee('data-table')
        ->assertDontSee('<i data-lucide');
});
