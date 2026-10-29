<?php

declare(strict_types=1);

use App\Livewire\Kesiswaan\GuardianForm;
use App\Livewire\Kesiswaan\GuardianIndex;
use App\Livewire\Kesiswaan\GuardianShow;
use App\Models\Guardian;
use App\Models\Membership;
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
        'nama' => 'Sekolah Wali', 'slug' => 'wali-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);
    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->ayah = Guardian::create([
        'nama' => 'Budi Ayah', 'hubungan' => 'ayah', 'no_wa' => '628111111111',
    ]);
    $this->ibu = Guardian::create([
        'nama' => 'Siti Ibu', 'hubungan' => 'ibu', 'no_wa' => '628222222222',
        'pekerjaan' => 'Guru',
    ]);

    $this->siswa = Student::create([
        'nis' => '1001', 'nama' => 'Anak Satu', 'jk' => 'L',
    ]);

    $this->admin = User::create([
        'username' => 'waliadmin-'.uniqid(), 'name' => 'Admin Wali',
        'phone' => '62851'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');

    $this->guruMapel = User::create([
        'username' => 'waliguru-'.uniqid(), 'name' => 'Guru Mapel',
        'phone' => '62853'.random_int(1000000, 9999999),
    ]);
    Membership::create(['user_id' => $this->guruMapel->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->guruMapel->assignRole('guru-mapel');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('halaman daftar wali menampilkan data dan entri menu', function (): void {
    $this->actingAs($this->admin)
        ->get('/wali')
        ->assertOk()
        ->assertSee('Data Wali')
        ->assertSee('Budi Ayah')
        ->assertSee('Siti Ibu')
        ->assertSee('Tambah Wali');
});

it('pencarian dan filter hubungan mempersempit daftar', function (): void {
    Livewire::actingAs($this->admin)
        ->test(GuardianIndex::class)
        ->set('search', 'Siti')
        ->assertSee('Siti Ibu')
        ->assertDontSee('Budi Ayah')
        ->set('search', '')
        ->set('hubungan', 'ayah')
        ->assertSee('Budi Ayah')
        ->assertDontSee('Siti Ibu');
});

it('form membuat wali baru dan memvalidasi nomor WA', function (): void {
    Livewire::actingAs($this->admin)
        ->test(GuardianForm::class)
        ->set('nama', 'Wali Baru')
        ->set('hubungan', 'wali')
        ->call('save')
        ->assertHasErrors(['no_wa'])
        ->set('no_wa', '628333333333')
        ->call('save')
        ->assertRedirect(route('wali.index'));

    $this->assertDatabaseHas('guardians', [
        'nama' => 'Wali Baru', 'hubungan' => 'wali', 'no_wa' => '628333333333',
    ]);
});

it('detail wali menautkan dan melepas siswa', function (): void {
    Livewire::actingAs($this->admin)
        ->test(GuardianShow::class, ['guardian' => $this->ayah])
        ->set('linkStudentId', (string) $this->siswa->id)
        ->set('linkHubungan', 'ayah')
        ->call('linkStudent')
        ->assertSee('Anak Satu');

    $this->assertDatabaseHas('guardian_student', [
        'guardian_id' => $this->ayah->id, 'student_id' => $this->siswa->id,
        'hubungan' => 'ayah',
    ]);

    Livewire::actingAs($this->admin)
        ->test(GuardianShow::class, ['guardian' => $this->ayah])
        ->call('unlinkStudent', $this->siswa->id);

    $this->assertDatabaseMissing('guardian_student', [
        'guardian_id' => $this->ayah->id, 'student_id' => $this->siswa->id,
    ]);
});

it('wali dengan NIK tersimpan terenkripsi dan kembali terbaca', function (): void {
    $wali = Guardian::create([
        'nama' => 'Budi NIK', 'hubungan' => 'ayah', 'no_wa' => '628444444444',
        'nik' => '3273010101900001',
    ]);

    // Nilai mentah di database adalah payload terenkripsi, bukan NIK polos,
    // dan cast membukanya kembali ke nilai asal.
    $this->assertNotSame('3273010101900001', $wali->getRawOriginal('nik'));
    $this->assertSame('3273010101900001', $wali->fresh()->nik);
});

it('pengguna tanpa permission wali-murid.view tidak boleh membuka daftar', function (): void {
    $this->actingAs($this->guruMapel)
        ->get('/wali')
        ->assertForbidden();
});

it('form menolak NIK yang bukan 16 digit (QA B-05)', function (): void {
    Livewire::actingAs($this->admin)
        ->test(GuardianForm::class)
        ->set('nama', 'Wali NIK Pendek')
        ->set('hubungan', 'ayah')
        ->set('no_wa', '628555555555')
        ->set('nik', '12345')
        ->call('save')
        ->assertHasErrors(['nik']);

    expect(Guardian::where('nama', 'Wali NIK Pendek')->exists())->toBeFalse();
});

it('form menolak NIK duplikat di sekolah yang sama (QA B-05)', function (): void {
    Guardian::create([
        'nama' => 'Wali Pertama', 'hubungan' => 'ayah', 'no_wa' => '628666666666',
        'nik' => '3273010101900002',
    ]);

    Livewire::actingAs($this->admin)
        ->test(GuardianForm::class)
        ->set('nama', 'Wali Kedua')
        ->set('hubungan', 'ibu')
        ->set('no_wa', '628777777777')
        ->set('nik', '3273010101900002')
        ->call('save')
        ->assertHasErrors(['nik']);

    expect(Guardian::where('nama', 'Wali Kedua')->exists())->toBeFalse()
        ->and(Guardian::where('nik_hash', Guardian::hashNik('3273010101900002'))->count())->toBe(1);
});

it('nomor WA format 08xx dinormalisasi ke 62xx saat disimpan (QA B-06)', function (): void {
    Livewire::actingAs($this->admin)
        ->test(GuardianForm::class)
        ->set('nama', 'Wali Lokal')
        ->set('hubungan', 'wali')
        ->set('no_wa', '0812-3456-7890')
        ->call('save')
        ->assertRedirect(route('wali.index'));

    $this->assertDatabaseHas('guardians', [
        'nama' => 'Wali Lokal', 'no_wa' => '6281234567890',
    ]);
});

it('form menolak nomor WA yang tidak valid (QA B-06)', function (): void {
    Livewire::actingAs($this->admin)
        ->test(GuardianForm::class)
        ->set('nama', 'Wali Nomor Aneh')
        ->set('hubungan', 'ayah')
        ->set('no_wa', '12345')
        ->call('save')
        ->assertHasErrors(['no_wa']);

    expect(Guardian::where('nama', 'Wali Nomor Aneh')->exists())->toBeFalse();
});

it('form ubah wali memperbarui data dan NIK milik sendiri tidak dianggap duplikat', function (): void {
    $wali = Guardian::create([
        'nama' => 'Wali Ubah', 'hubungan' => 'ayah', 'no_wa' => '628888888888',
        'nik' => '3273010101900003',
    ]);

    Livewire::actingAs($this->admin)
        ->test(GuardianForm::class, ['guardian' => $wali])
        ->set('nama', 'Wali Berubah')
        ->set('pekerjaan', 'Petani')
        ->call('save')
        ->assertRedirect(route('wali.index'));

    $wali->refresh();
    expect($wali->nama)->toBe('Wali Berubah')
        ->and($wali->pekerjaan)->toBe('Petani')
        ->and($wali->nik)->toBe('3273010101900003');
});

it('form menolak nama kosong', function (): void {
    Livewire::actingAs($this->admin)
        ->test(GuardianForm::class)
        ->set('nama', '')
        ->set('no_wa', '628999999999')
        ->call('save')
        ->assertHasErrors(['nama']);
});

it('daftar wali: hapus ke sampah, pulihkan, lalu hapus permanen', function (): void {
    Livewire::actingAs($this->admin)
        ->test(GuardianIndex::class)
        ->call('delete', $this->ibu->id);

    expect($this->ibu->fresh()->trashed())->toBeTrue();

    Livewire::actingAs($this->admin)
        ->test(GuardianIndex::class)
        ->call('restore', $this->ibu->id);

    expect($this->ibu->fresh()->trashed())->toBeFalse();

    Livewire::actingAs($this->admin)
        ->test(GuardianIndex::class)
        ->call('delete', $this->ibu->id)
        ->call('forceDelete', $this->ibu->id);

    expect(Guardian::withTrashed()->find($this->ibu->id))->toBeNull();
});

it('wali sekolah lain tidak muncul di daftar sekolah ini', function (): void {
    $sekolahLain = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(TenantContext::class)->set($sekolahLain);
    Guardian::create(['nama' => 'Wali Sekolah Lain', 'hubungan' => 'ayah', 'no_wa' => '628000000000']);
    app(TenantContext::class)->set($this->school);

    Livewire::actingAs($this->admin)
        ->test(GuardianIndex::class)
        ->assertSee('Budi Ayah')
        ->assertDontSee('Wali Sekolah Lain');
});

it('menautkan siswa sekolah lain ditolak sebagai error validasi (QA B-11)', function (): void {
    $sekolahLain = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain-'.uniqid(), 'jenjang' => 'SMP',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(TenantContext::class)->set($sekolahLain);
    $siswaLain = Student::create(['nis' => '9001', 'nama' => 'Siswa Lain', 'jk' => 'L']);
    app(TenantContext::class)->set($this->school);

    Livewire::actingAs($this->admin)
        ->test(GuardianShow::class, ['guardian' => $this->ayah])
        ->set('linkStudentId', (string) $siswaLain->id)
        ->set('linkHubungan', 'ayah')
        ->call('linkStudent')
        ->assertHasErrors(['linkStudentId']);

    expect($this->ayah->students()->count())->toBe(0);
});

it('menautkan siswa non-aktif ditolak (QA B-11)', function (): void {
    $lulus = Student::create([
        'nis' => '1002', 'nama' => 'Anak Lulus', 'jk' => 'P', 'status' => 'lulus',
    ]);

    Livewire::actingAs($this->admin)
        ->test(GuardianShow::class, ['guardian' => $this->ayah])
        ->set('linkStudentId', (string) $lulus->id)
        ->set('linkHubungan', 'ayah')
        ->call('linkStudent')
        ->assertHasErrors(['linkStudentId']);

    expect($this->ayah->students()->count())->toBe(0);
});

it('pengguna tanpa permission link tidak bisa menautkan siswa', function (): void {
    Livewire::actingAs($this->guruMapel)
        ->test(GuardianShow::class, ['guardian' => $this->ayah])
        ->set('linkStudentId', (string) $this->siswa->id)
        ->set('linkHubungan', 'ayah')
        ->call('linkStudent')
        ->assertForbidden();

    expect($this->ayah->students()->count())->toBe(0);
});

it('kandidat tautan bisa dicari dan dibatasi 50 (QA B-09)', function (): void {
    foreach (range(1, 55) as $i) {
        Student::create([
            'nis' => '2'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
            'nama' => 'Kandidat '.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            'jk' => 'L',
        ]);
    }

    $component = Livewire::actingAs($this->admin)
        ->test(GuardianShow::class, ['guardian' => $this->ayah])
        ->assertSee('Kandidat 49')
        ->assertDontSee('Kandidat 50');

    $component->set('linkSearch', 'Kandidat 55')
        ->assertSee('Kandidat 55')
        ->assertDontSee('Kandidat 49');
});
