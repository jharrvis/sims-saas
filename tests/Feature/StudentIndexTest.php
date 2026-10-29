<?php

declare(strict_types=1);

use App\Livewire\Kesiswaan\StudentIndex;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Membership;
use App\Models\Rombel;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Route;
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
    Livewire::actingAs($this->admin)
        ->test(StudentIndex::class)
        ->set('rombel', (string) $this->rombelA->id)
        ->assertSeeHtml('rombel_id='.$this->rombelA->id)
        ->assertDontSeeHtml('rombel_id='.$this->rombelB->id);
});

it('tanpa filter kelas, link export tanpa rombel_id dan tombol kartu nonaktif', function (): void {
    Livewire::actingAs($this->admin)
        ->test(StudentIndex::class)
        ->assertDontSeeHtml('rombel_id=')
        ->assertSee('Pilih kelas dulu');
});

it('dropdown export menampilkan keterangan jumlah + filter yang aktif', function (): void {
    Livewire::actingAs($this->admin)
        ->test(StudentIndex::class)
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

it('pengguna view-only tidak melihat aksi massal maupun menu export (QA B-04)', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(StudentIndex::class)
        ->set('selected', [Student::query()->value('id')])
        ->assertDontSee('Export Excel')
        ->assertDontSee('Excel (.xlsx)')
        ->assertDontSee('Ubah status')
        ->assertDontSee('Pindah rombel')
        ->assertDontSee('Pindahkan ke sampah')
        ->assertDontSee('Buka Sampah')
        ->assertDontSee('Tambah Siswa');
});

it('route ekspor menolak pengguna tanpa siswa.export (QA B-04)', function (): void {
    $this->actingAs($this->viewer)
        ->get('/siswa/ekspor/excel')
        ->assertForbidden();

    $this->actingAs($this->viewer)
        ->get('/siswa/ekspor/pdf')
        ->assertForbidden();
});

it('route ekspor terdaftar dengan middleware permission siswa.export (QA B-04)', function (): void {
    // Jalur "diizinkan" tidak diuji via unduhan nyata di sini: vendor lokal
    // main tidak memuat maatwebsite/excel (paket itu hanya ada di lock
    // server). Izin admin dibuktikan lewat uji export massal di bawah.
    foreach (['siswa.ekspor.excel', 'siswa.ekspor.pdf'] as $namaRoute) {
        $middleware = Route::getRoutes()->getByName($namaRoute)?->gatherMiddleware() ?? [];
        expect($middleware)->toContain('permission:siswa.export');
    }
});

it('export massal menolak pengguna tanpa siswa.export (QA B-04)', function (): void {
    Livewire::actingAs($this->viewer)
        ->test(StudentIndex::class)
        ->set('selected', [Student::query()->value('id')])
        ->set('bulkAksi', 'export')
        ->call('bulkTerapkan')
        ->assertForbidden();
});

it('export massal mengarahkan pemegang siswa.export ke unduhan (QA B-04)', function (): void {
    $studentId = Student::query()->value('id');

    Livewire::actingAs($this->admin)
        ->test(StudentIndex::class)
        ->set('selected', [$studentId])
        ->set('bulkAksi', 'export')
        ->call('bulkTerapkan')
        ->assertRedirect(route('siswa.ekspor.excel').'?ids[]='.$studentId);
});

it('halaman admin tetap tampil ketika fitur impor belum tersedia', function (): void {
    $this->actingAs($this->admin)
        ->get('/siswa')
        ->assertOk()
        ->assertDontSee('Impor Data');
});

it('hapus permanen siswa tanpa data turunan berhasil bagi pemegang force-delete (QA B-02)', function (): void {
    $siswa = Student::where('nama', 'Siswa A1')->firstOrFail();
    $siswa->delete();

    Livewire::actingAs($this->admin)
        ->test(StudentIndex::class)
        ->call('forceDelete', $siswa->id);

    expect(Student::withTrashed()->find($siswa->id))->toBeNull();
});

it('pengguna siswa.delete tanpa force-delete ditolak saat hapus permanen (QA B-02)', function (): void {
    $penghapus = User::create([
        'username' => 'penghapus-'.uniqid(), 'name' => 'Penghapus Saja',
        'phone' => '62814'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $penghapus->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $penghapus->givePermissionTo(['siswa.view', 'siswa.delete']);

    $siswa = Student::where('nama', 'Siswa A1')->firstOrFail();
    $siswa->delete();

    Livewire::actingAs($penghapus)
        ->test(StudentIndex::class)
        ->call('forceDelete', $siswa->id)
        ->assertForbidden();

    expect(Student::withTrashed()->find($siswa->id))->not->toBeNull();
});

it('siswa dengan catatan absensi tidak dapat dihapus permanen (QA B-02)', function (): void {
    $siswa = Student::where('nama', 'Siswa A1')->firstOrFail();
    Attendance::create([
        'student_id' => $siswa->id, 'rombel_id' => $this->rombelA->id,
        'tanggal' => '2026-10-05', 'status' => 'H',
    ]);
    $siswa->delete();

    Livewire::actingAs($this->admin)
        ->test(StudentIndex::class)
        ->call('forceDelete', $siswa->id)
        ->assertSee('tidak dapat dihapus permanen');

    expect(Student::withTrashed()->find($siswa->id))->not->toBeNull()
        ->and(Attendance::where('student_id', $siswa->id)->count())->toBe(1);
});

it('hapus permanen massal melewati siswa yang masih berdata turunan (QA B-02)', function (): void {
    $bersih = Student::where('nama', 'Siswa A1')->firstOrFail();
    $berdata = Student::where('nama', 'Siswa A2')->firstOrFail();
    Attendance::create([
        'student_id' => $berdata->id, 'rombel_id' => $this->rombelA->id,
        'tanggal' => '2026-10-05', 'status' => 'H',
    ]);
    $bersih->delete();
    $berdata->delete();

    Livewire::actingAs($this->admin)
        ->test(StudentIndex::class)
        ->call('toggleTrashed')
        ->set('selected', [$bersih->id, $berdata->id])
        ->set('bulkAksi', 'hapus_permanen')
        ->call('bulkTerapkan');

    expect(Student::withTrashed()->find($bersih->id))->toBeNull()
        ->and(Student::withTrashed()->find($berdata->id))->not->toBeNull();
});
