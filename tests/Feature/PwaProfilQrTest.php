<?php

declare(strict_types=1);

use App\Domains\Pwa\Services\PwaContext;
use App\Livewire\Pwa\Profil as PwaProfil;
use App\Models\AcademicYear;
use App\Models\Guardian;
use App\Models\Rombel;
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
        'nama' => 'Sekolah PWA QR', 'slug' => 'pwa-qr-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->ta = AcademicYear::create([
        'nama' => '2026/2027', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);
    $this->rombel = Rombel::create([
        'academic_year_id' => $this->ta->id, 'tingkat' => '10', 'nama' => 'X-1', 'kapasitas' => 36,
    ]);

    $this->siswa = Student::create([
        'nis' => '9001', 'nama' => 'Siswa PWA', 'jk' => 'L', 'status' => 'aktif',
        'phone' => '628111000001',
    ]);
    $this->siswa->rombels()->attach($this->rombel->id, [
        'academic_year_id' => $this->ta->id, 'status' => 'aktif',
    ]);

    $this->wali = Guardian::create([
        'nik' => '123', 'nama' => 'Wali PWA', 'hubungan' => 'ayah',
        'no_wa' => '628222000001',
    ]);
    $this->wali->students()->attach($this->siswa->id, ['hubungan' => 'ayah']);
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

function bindPwaContextUntuk(User $user): void
{
    $ctx = new PwaContext;
    $ctx->setUser($user);
    app()->instance(PwaContext::class, $ctx);
}

it('menampilkan QR absensi siswa di profil PWA', function (): void {
    $user = User::create([
        'username' => 'pwa-628111000001', 'name' => 'Siswa PWA', 'phone' => '628111000001',
        'student_id' => $this->siswa->id,
    ]);
    bindPwaContextUntuk($user);

    Livewire::actingAs($user)->test(PwaProfil::class)
        ->assertSee('QR Absensi')
        ->assertSee('Tunjukkan ke scanner di sekolah')
        ->assertSee('NIS 9001')
        ->assertSee('width="220"', false)
        ->assertSee('qr-absensi');
});

it('menampilkan QR anak aktif untuk orang tua', function (): void {
    $user = User::create([
        'username' => 'pwa-628222000001', 'name' => 'Wali PWA', 'phone' => '628222000001',
    ]);
    $user->guardians()->attach($this->wali->id);
    bindPwaContextUntuk($user);

    Livewire::actingAs($user)->test(PwaProfil::class)
        ->assertSee('QR Absensi')
        ->assertSee('NIS 9001');
});

it('tidak menampilkan QR bila pengguna belum terhubung ke siswa', function (): void {
    $user = User::create([
        'username' => 'pwa-tanpa-siswa', 'name' => 'Tamu', 'phone' => '628333000001',
    ]);
    bindPwaContextUntuk($user);

    Livewire::actingAs($user)->test(PwaProfil::class)
        ->assertDontSee('QR Absensi')
        ->assertDontSee('Tunjukkan ke scanner di sekolah');
});

it('menampilkan pesan bila NIS siswa belum diisi', function (): void {
    $siswaTanpaNis = Student::create([
        'nis' => '', 'nama' => 'Siswa Tanpa NIS', 'jk' => 'P', 'status' => 'aktif',
    ]);
    $user = User::create([
        'username' => 'pwa-tanpa-nis', 'name' => 'Siswa Tanpa NIS', 'phone' => '628444000001',
        'student_id' => $siswaTanpaNis->id,
    ]);
    bindPwaContextUntuk($user);

    Livewire::actingAs($user)->test(PwaProfil::class)
        ->assertSee('QR belum tersedia')
        ->assertDontSee('Tunjukkan ke scanner di sekolah');
});
