<?php

declare(strict_types=1);

use App\Livewire\Kesiswaan\StudentIndex;
use App\Models\AcademicYear;
use App\Models\Membership;
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
        'nama' => 'Sekolah Toolbar', 'slug' => 'tb-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $ta = AcademicYear::create([
        'nama' => '2026/2027', 'aktif' => true,
        'tgl_mulai' => '2026-07-01', 'tgl_selesai' => '2027-06-30',
    ]);

    $this->rombelA = Rombel::create(['nama' => 'VII A', 'academic_year_id' => $ta->id, 'tingkat' => '7']);
    $this->rombelB = Rombel::create(['nama' => 'IX B', 'academic_year_id' => $ta->id, 'tingkat' => '9']);

    foreach (range(1, 2) as $i) {
        $s = Student::create(['nis' => "A$i", 'nama' => "Siswa A$i", 'jk' => 'L', 'status' => 'aktif']);
        $s->rombels()->attach($this->rombelA->id, ['academic_year_id' => $ta->id, 'status' => 'aktif']);
    }
    foreach (range(1, 3) as $i) {
        $s = Student::create(['nis' => "B$i", 'nama' => "Siswa B$i", 'jk' => 'P', 'status' => 'aktif']);
        $s->rombels()->attach($this->rombelB->id, ['academic_year_id' => $ta->id, 'status' => 'aktif']);
    }

    $this->viewer = User::create([
        'username' => 'viewer-'.uniqid(),
        'name' => 'Guru Viewer',
        'phone' => '62812'.random_int(1000000, 9999999),
    ]);
    Membership::create([
        'user_id' => $this->viewer->id,
        'school_id' => $this->school->id,
        'status' => 'aktif',
    ]);
    $this->viewer->assignRole('guru-mapel');

    $this->admin = User::create([
        'username' => 'admin-'.uniqid(),
        'name' => 'Admin Sekolah',
        'phone' => '62813'.random_int(1000000, 9999999),
    ]);
    Membership::create([
        'user_id' => $this->admin->id,
        'school_id' => $this->school->id,
        'status' => 'aktif',
    ]);
    $this->admin->assignRole('admin-sekolah');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('link export + kartu memakai rombel yang dipilih (bukan rombel terakhir di dropdown)', function (): void {
    Livewire::test(StudentIndex::class)
        ->set('rombel', (string) $this->rombelA->id)
        ->assertSeeHtml('rombel_id='.$this->rombelA->id)
        ->assertDontSeeHtml('rombel_id='.$this->rombelB->id);
});

it('tanpa filter kelas, link export tanpa rombel_id dan tombol kartu nonaktif', function (): void {
    Livewire::test(StudentIndex::class)
        ->assertDontSeeHtml('rombel_id=')
        ->assertSee('Pilih kelas dulu');
});

it('dropdown export menampilkan keterangan jumlah + filter yang aktif', function (): void {
    Livewire::test(StudentIndex::class)
        ->assertSee('Export 5 siswa')
        ->assertSee('Semua kelas')
        ->set('rombel', (string) $this->rombelA->id)
        ->assertSee('Export 2 siswa')
        ->assertSee('Rombel VII A')
        ->set('search', 'Siswa A1')
        ->assertSee('Export 1 siswa')
        ->assertSee('Cari');
});

it('reset filter membersihkan filter dan pilihan siswa', function (): void {
    Livewire::test(StudentIndex::class)
        ->set('search', 'Siswa A')
        ->set('status', 'aktif')
        ->set('rombel', (string) $this->rombelA->id)
        ->set('selected', [1])
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('status', '')
        ->assertSet('rombel', '')
        ->assertSet('selected', []);
});

it('pengguna view-only tidak dapat membuka sampah melalui aksi langsung', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(StudentIndex::class)
        ->call('toggleTrashed')
        ->assertForbidden();
});

it('pembaruan properti showTrashed langsung menolak pengguna view-only', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(StudentIndex::class)
        ->set('showTrashed', true)
        ->assertForbidden();
});

it('data terhapus tidak terlihat bagi view-only, terlihat bagi pemegang delete di mode sampah', function (): void {
    Student::where('nama', 'Siswa A1')->firstOrFail()->delete();

    Livewire::actingAs($this->viewer)
        ->test(StudentIndex::class)
        ->assertSee('Siswa A2')
        ->assertDontSee('Siswa A1');

    Livewire::actingAs($this->admin)
        ->test(StudentIndex::class)
        ->call('toggleTrashed')
        ->assertSet('showTrashed', true)
        ->assertSee('Siswa A1')
        ->assertDontSee('Siswa A2');
});

it('pengguna view-only hanya melihat aksi massal export', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(StudentIndex::class)
        ->set('selected', [Student::query()->value('id')])
        ->assertSee('Export Excel')
        ->assertDontSee('Ubah status')
        ->assertDontSee('Pindah rombel')
        ->assertDontSee('Pindahkan ke sampah')
        ->assertDontSee('Buka Sampah')
        ->assertDontSee('Tambah Siswa');
});

it('halaman admin tetap tampil ketika fitur impor belum tersedia', function (): void {
    $this->actingAs($this->admin)
        ->get('/siswa')
        ->assertOk()
        ->assertDontSee('Impor Data');
});
