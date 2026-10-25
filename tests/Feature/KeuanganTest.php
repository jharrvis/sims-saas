<?php

declare(strict_types=1);

use App\Domains\Keuangan\Services\KeuanganService;
use App\Livewire\Keuangan\InvoiceIndex;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Module;
use App\Models\Payment;
use App\Models\School;
use App\Models\Student;
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
        'nama' => 'Sekolah Keu', 'slug' => 'keu-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $keu = Module::where('kode', 'keuangan')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $keu->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'keuadmin', 'name' => 'Keu Admin', 'phone' => '628550000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->feeType = FeeType::create([
        'nama' => 'SPP', 'nominal' => 500000, 'periode' => 'bulanan', 'aktif' => true,
    ]);

    $this->siswa = Student::create(['nis' => '8001', 'nama' => 'Siswa Bayar', 'jk' => 'L', 'status' => 'aktif']);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul keuangan nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/keuangan/tagihan')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('generate tagihan massal lalu bayar bertahap sampai lunas', function (): void {
    // Generate
    $res = $this->actingAs($this->admin)->postJson('/api/v1/keuangan/tagihan/generate', [
        'fee_type_id' => $this->feeType->id,
        'periode_label' => '2026-07',
        'jatuh_tempo' => '2026-07-10',
    ])->assertCreated();

    expect($res->json('count'))->toBe(1);

    // Generate lagi periode sama → tidak duplikat
    $res2 = $this->actingAs($this->admin)->postJson('/api/v1/keuangan/tagihan/generate', [
        'fee_type_id' => $this->feeType->id,
        'periode_label' => '2026-07',
    ])->assertCreated();
    expect($res2->json('count'))->toBe(0);

    $invoice = Invoice::first();
    expect($invoice->status)->toBe('unpaid');

    // Bayar sebagian
    $this->actingAs($this->admin)->postJson("/api/v1/keuangan/tagihan/{$invoice->id}/bayar", [
        'nominal' => 200000,
        'tgl_bayar' => '2026-07-05',
        'metode' => 'tunai',
    ])->assertCreated();

    expect($invoice->fresh()->status)->toBe('partial');
    expect($invoice->fresh()->sisa())->toBe(300000.0);

    // Bayar melebihi sisa → ditolak
    $this->actingAs($this->admin)->postJson("/api/v1/keuangan/tagihan/{$invoice->id}/bayar", [
        'nominal' => 400000,
        'tgl_bayar' => '2026-07-06',
        'metode' => 'transfer',
    ])->assertStatus(422);

    // Lunasi
    $pay = $this->actingAs($this->admin)->postJson("/api/v1/keuangan/tagihan/{$invoice->id}/bayar", [
        'nominal' => 300000,
        'tgl_bayar' => '2026-07-06',
        'metode' => 'qris',
    ])->assertCreated();

    expect($pay->json('no_kuitansi'))->toStartWith('KWT/');
    expect($invoice->fresh()->status)->toBe('paid');
    expect(Payment::count())->toBe(2);
});

it('ringkasan dan tunggakan terhitung benar', function (): void {
    $service = app(KeuanganService::class);
    $service->generateInvoices($this->feeType->id, '2026-07', '2026-07-10');
    $service->generateInvoices($this->feeType->id, '2026-08', '2026-08-10');

    $invoice = Invoice::where('periode_label', '2026-07')->first();
    $service->recordPayment($invoice, [
        'nominal' => 500000, 'tgl_bayar' => '2026-07-05', 'metode' => 'tunai',
    ], $this->admin->id);

    $summary = $service->summary();

    expect($summary['total_tagihan'])->toBe(1000000.0)
        ->and($summary['terkumpul'])->toBe(500000.0)
        ->and($summary['tunggakan'])->toBe(500000.0)
        ->and($summary['jumlah_unpaid'])->toBe(1);

    $this->actingAs($this->admin)->getJson('/api/v1/keuangan/laporan/tunggakan')
        ->assertOk()
        ->assertJsonPath('total', 1);
});

it('daftar tagihan memakai struktur design system management (Batch 6)', function (): void {
    Livewire::actingAs($this->admin)->test(InvoiceIndex::class)
        ->assertSee('Daftar Tagihan')
        ->assertSeeHtml('management-page-header')
        ->assertSee('Belum Lunas')
        ->assertDontSee('<i data-lucide');
});
