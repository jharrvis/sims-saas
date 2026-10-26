<?php

declare(strict_types=1);

use App\Livewire\Akademik\SubjectForm;
use App\Livewire\Akademik\SubjectIndex;
use App\Models\Membership;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use App\Support\Navigation;
use App\Tenancy\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->school = School::create([
        'nama' => 'Sekolah Mapel Form', 'slug' => 'mapf-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->subject = Subject::create([
        'kode' => 'MTK', 'nama' => 'Matematika', 'kelompok' => 'wajib', 'kkm_default' => 75,
    ]);

    $this->admin = User::create([
        'username' => 'mapfadmin-'.uniqid(), 'name' => 'Admin Mapel',
        'phone' => '62883'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->kepsek = User::create([
        'username' => 'mapfkepsek-'.uniqid(), 'name' => 'Kepsek Mapel',
        'phone' => '62884'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->kepsek->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->kepsek->assignRole('kepala-sekolah');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('form mapel memakai struktur design system management', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SubjectForm::class)
        ->assertSee('Tambah Mata Pelajaran')
        ->assertSee('Kembali ke Mata Pelajaran')
        ->assertSeeHtml('management-form-card')
        ->assertSeeHtml('management-form-grid')
        ->assertDontSee('bg-boxdark');
});

it('rute tambah dan ubah mapel terdaftar setelah SubjectForm ada', function (): void {
    expect(route('mapel.index'))->toContain('/mapel');
    expect(route('mapel.create'))->toContain('/mapel/tambah');
    expect(route('mapel.edit', $this->subject))->toContain('/mapel/'.$this->subject->id.'/ubah');

    $this->actingAs($this->admin)->get(route('mapel.create'))->assertOk();
    $this->actingAs($this->admin)->get(route('mapel.edit', $this->subject))->assertOk();
});

it('menu sidebar menampilkan Mata Pelajaran untuk pengguna dengan jadwal.view', function (): void {
    $this->actingAs($this->admin);

    $labels = collect(Navigation::school())
        ->flatMap(fn ($group) => $group['items'])
        ->filter(fn ($item) => $item['visible'])
        ->pluck('label');

    expect($labels)->toContain('Mata Pelajaran');
});

it('simpan form mapel menolak pengguna tanpa jadwal.create dan jadwal.update', function (): void {
    Livewire::actingAs($this->kepsek)
        ->test(SubjectForm::class)
        ->set('kode', 'BIN')
        ->set('nama', 'Bahasa Indonesia')
        ->set('kelompok', 'wajib')
        ->set('kkm_default', 75)
        ->call('save')
        ->assertForbidden();

    expect(Subject::where('kode', 'BIN')->exists())->toBeFalse();

    Livewire::actingAs($this->kepsek)
        ->test(SubjectForm::class, ['subject' => $this->subject])
        ->set('nama', 'Nama Diubah Paksa')
        ->call('save')
        ->assertForbidden();

    expect(Subject::find($this->subject->id)->nama)->toBe('Matematika');
});

it('simpan form mapel admin berhasil untuk tambah dan ubah', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SubjectForm::class)
        ->set('kode', 'IPA')
        ->set('nama', 'Ilmu Pengetahuan Alam')
        ->set('kelompok', 'wajib')
        ->set('kkm_default', 70)
        ->call('save')
        ->assertHasNoErrors();

    expect(Subject::where('kode', 'IPA')->exists())->toBeTrue();

    Livewire::actingAs($this->admin)
        ->test(SubjectForm::class, ['subject' => $this->subject])
        ->set('nama', 'Matematika Lanjutan')
        ->call('save')
        ->assertHasNoErrors();

    expect(Subject::find($this->subject->id)->nama)->toBe('Matematika Lanjutan');
});

it('form mapel menolak kode duplikat, kelompok liar, dan kkm di luar rentang', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SubjectForm::class)
        ->set('kode', 'MTK')
        ->set('nama', 'Duplikat')
        ->set('kelompok', 'wajib')
        ->set('kkm_default', 75)
        ->call('save')
        ->assertHasErrors(['kode']);

    Livewire::actingAs($this->admin)
        ->test(SubjectForm::class)
        ->set('kode', 'SEJ')
        ->set('nama', 'Sejarah')
        ->set('kelompok', 'liar')
        ->set('kkm_default', 75)
        ->call('save')
        ->assertHasErrors(['kelompok']);

    Livewire::actingAs($this->admin)
        ->test(SubjectForm::class)
        ->set('kode', 'SEJ')
        ->set('nama', 'Sejarah')
        ->set('kelompok', 'wajib')
        ->set('kkm_default', 101)
        ->call('save')
        ->assertHasErrors(['kkm_default']);

    expect(Subject::where('kode', 'SEJ')->exists())->toBeFalse();
});

it('daftar mapel menampilkan tombol tambah dan ubah sesuai izin', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SubjectIndex::class)
        ->assertSee('Tambah Mapel')
        ->assertSee('Matematika');
});
