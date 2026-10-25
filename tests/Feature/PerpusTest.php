<?php

declare(strict_types=1);

use App\Livewire\Perpus\BookForm;
use App\Livewire\Perpus\BookIndex;
use App\Livewire\Perpus\LoanIndex;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Membership;
use App\Models\Module;
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
        'nama' => 'Sekolah Perpus', 'slug' => 'perpus-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'perpustakaan')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'perpusadmin', 'name' => 'Perpus Admin', 'phone' => '628620000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->siswa = Student::create(['nis' => '9801', 'nama' => 'Siswa Perpus', 'jk' => 'L', 'status' => 'aktif']);
    $this->book = Book::create(['kode' => 'BK-001', 'judul' => 'Matematika Dasar', 'jumlah' => 2]);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul perpustakaan nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/perpus/buku')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('peminjaman mengurangi stok tersedia dan menolak saat habis', function (): void {
    $s2 = Student::create(['nis' => '9802', 'nama' => 'Siswa Dua', 'jk' => 'P', 'status' => 'aktif']);
    $s3 = Student::create(['nis' => '9803', 'nama' => 'Siswa Tiga', 'jk' => 'L', 'status' => 'aktif']);

    foreach ([$this->siswa, $s2] as $s) {
        $this->actingAs($this->admin)->postJson('/api/v1/perpus/pinjam', [
            'book_id' => $this->book->id, 'student_id' => $s->id,
        ])->assertCreated();
    }

    expect($this->book->fresh()->tersedia)->toBe(0);

    $this->actingAs($this->admin)->postJson('/api/v1/perpus/pinjam', [
        'book_id' => $this->book->id, 'student_id' => $s3->id,
    ])->assertUnprocessable()->assertJsonPath('code', 'STOK_HABIS');
});

it('pengembalian tepat waktu tanpa denda; terlambat kena denda', function (): void {
    $loan = BookLoan::create([
        'book_id' => $this->book->id, 'student_id' => $this->siswa->id,
        'tgl_pinjam' => now()->subDays(3)->toDateString(),
        'tgl_jatuh_tempo' => now()->addDays(4)->toDateString(),
    ]);

    $this->actingAs($this->admin)->postJson("/api/v1/perpus/pinjam/{$loan->id}/kembali")
        ->assertOk()->assertJsonPath('status', 'kembali')->assertJsonPath('denda', 0);

    $late = BookLoan::create([
        'book_id' => $this->book->id, 'student_id' => $this->siswa->id,
        'tgl_pinjam' => now()->subDays(10)->toDateString(),
        'tgl_jatuh_tempo' => now()->subDays(3)->toDateString(),
    ]);

    $this->actingAs($this->admin)->postJson("/api/v1/perpus/pinjam/{$late->id}/kembali")
        ->assertOk()->assertJsonPath('status', 'terlambat')
        ->assertJsonPath('denda', 3 * BookLoan::DENDA_PER_HARI);
});

it('buku terisolasi per sekolah', function (): void {
    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain5-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    app(TenantContext::class)->set($other);

    expect(Book::count())->toBe(0);
    expect(BookLoan::count())->toBe(0);
});

it('perpus memakai struktur design system management (Batch 9)', function (): void {
    Livewire::actingAs($this->admin)->test(BookIndex::class)
        ->assertSee('Katalog Buku')
        ->assertSeeHtml('management-page-header')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(BookForm::class)
        ->assertSee('Tambah Buku')
        ->assertSeeHtml('management-form-card')
        ->assertDontSee('max-w-3xl');

    Livewire::actingAs($this->admin)->test(LoanIndex::class)
        ->assertSee('Peminjaman Buku')
        ->assertSeeHtml('management-page-header')
        ->assertDontSee('<i data-lucide');
});
