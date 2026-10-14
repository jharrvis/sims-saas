<?php

declare(strict_types=1);

use App\Livewire\Kesiswaan\StudentIndex;
use App\Models\AcademicYear;
use App\Models\Rombel;
use App\Models\School;
use App\Models\Student;
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
