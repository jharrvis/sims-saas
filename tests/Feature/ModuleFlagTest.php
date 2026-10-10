<?php

declare(strict_types=1);

use App\Models\Module;
use App\Models\School;
use App\Models\TenantModule;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    // Route dummy untuk menguji middleware module
    Route::get('/_uji-modul-inti', fn () => response()->json(['ok' => true]))
        ->middleware('module:kesiswaan');
    Route::get('/_uji-modul-addon', fn () => response()->json(['ok' => true]))
        ->middleware('module:keuangan');

    $this->school = School::create([
        'nama' => 'Sekolah Modul', 'slug' => 'mod-1', 'jenjang' => 'SD',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    $this->user = User::create([
        'username' => 'moduser', 'name' => 'Mod User', 'phone' => '628770000001',
    ]);

    $this->keuangan = Module::create([
        'kode' => 'keuangan', 'nama' => 'Keuangan & SPP', 'kategori' => 'addon', 'aktif' => true,
    ]);
    $this->kesiswaan = Module::create([
        'kode' => 'kesiswaan', 'nama' => 'Kesiswaan', 'kategori' => 'inti', 'aktif' => true,
    ]);
});

it('modul inti selalu lolos tanpa flag', function (): void {
    app(TenantContext::class)->set($this->school);

    $this->actingAs($this->user)->getJson('/_uji-modul-inti')->assertOk();

    app(TenantContext::class)->clear();
});

it('modul addon nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    app(TenantContext::class)->set($this->school);

    // Belum ada flag → ditolak
    $this->actingAs($this->user)->getJson('/_uji-modul-addon')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');

    // Aktifkan → lolos
    TenantModule::create([
        'school_id' => $this->school->id,
        'module_id' => $this->keuangan->id,
        'aktif' => true,
    ]);

    $this->actingAs($this->user)->getJson('/_uji-modul-addon')->assertOk();

    // Nonaktifkan → ditolak lagi
    TenantModule::where('school_id', $this->school->id)
        ->where('module_id', $this->keuangan->id)
        ->update(['aktif' => false, 'dinonaktifkan_pada' => now()]);

    $this->actingAs($this->user)->getJson('/_uji-modul-addon')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');

    app(TenantContext::class)->clear();
});
