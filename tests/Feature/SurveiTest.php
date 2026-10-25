<?php

declare(strict_types=1);

use App\Livewire\Survei\SurveyFill;
use App\Livewire\Survei\SurveyForm;
use App\Livewire\Survei\SurveyIndex;
use App\Livewire\Survei\SurveyResult;
use App\Models\Membership;
use App\Models\Module;
use App\Models\School;
use App\Models\Survey;
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
        'nama' => 'Sekolah Survei', 'slug' => 'survei-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);

    app(PermissionSeeder::class)->run();
    app(ModuleSeeder::class)->run();
    RoleSeeder::seedForSchool($this->school->id);

    $mod = Module::where('kode', 'survei')->firstOrFail();
    TenantModule::create([
        'school_id' => $this->school->id, 'module_id' => $mod->id, 'aktif' => true,
    ]);

    app(TenantContext::class)->set($this->school);
    app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);

    $this->admin = User::create([
        'username' => 'surveiadmin', 'name' => 'Survei Admin', 'phone' => '628700000001',
    ]);
    Membership::create(['user_id' => $this->admin->id, 'school_id' => $this->school->id, 'status' => 'aktif']);
    $this->admin->assignRole('admin-sekolah');
});

afterEach(function (): void {
    app(TenantContext::class)->clear();
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
});

it('modul survei nonaktif mengembalikan 403 MODULE_DISABLED', function (): void {
    TenantModule::where('school_id', $this->school->id)->update(['aktif' => false]);

    $this->actingAs($this->admin)->getJson('/api/v1/survei')
        ->assertForbidden()
        ->assertJsonPath('code', 'MODULE_DISABLED');
});

it('membuat survei dengan pertanyaan dan mengisinya', function (): void {
    $surveyId = $this->actingAs($this->admin)->postJson('/api/v1/survei', [
        'judul' => 'Kepuasan Kantin',
        'status' => 'aktif',
        'target_type' => 'siswa',
        'questions' => [
            ['pertanyaan' => 'Bagaimana rasa makanan?', 'tipe' => 'skala'],
            ['pertanyaan' => 'Menu favorit?', 'tipe' => 'pilihan_ganda', 'opsi' => ['Nasi Goreng', 'Mie Ayam']],
        ],
    ])->assertCreated()->json('id');

    $survey = Survey::findOrFail($surveyId);
    $q1 = $survey->questions()->where('tipe', 'skala')->firstOrFail();
    $q2 = $survey->questions()->where('tipe', 'pilihan_ganda')->firstOrFail();

    $this->actingAs($this->admin)->postJson("/api/v1/survei/{$surveyId}/isi", [
        'jawaban' => [
            ['question_id' => $q1->id, 'isi' => '4'],
            ['question_id' => $q2->id, 'isi' => 'Nasi Goreng'],
        ],
    ])->assertCreated();

    $this->actingAs($this->admin)->postJson("/api/v1/survei/{$surveyId}/isi", [
        'jawaban' => [
            ['question_id' => $q1->id, 'isi' => '5'],
            ['question_id' => $q2->id, 'isi' => 'Mie Ayam'],
        ],
    ])->assertCreated();

    $hasil = $this->actingAs($this->admin)->getJson("/api/v1/survei/{$surveyId}/hasil")
        ->assertOk()->json();

    expect($hasil['total_responden'])->toBe(2);

    $skala = collect($hasil['hasil'])->firstWhere('tipe', 'skala');
    expect((float) $skala['agregat']['rata_rata'])->toBe(4.5);

    $pg = collect($hasil['hasil'])->firstWhere('tipe', 'pilihan_ganda');
    expect($pg['agregat']['distribusi'])->toBe(['Nasi Goreng' => 1, 'Mie Ayam' => 1]);
});

it('survei draf atau di luar periode tidak bisa diisi', function (): void {
    $surveyId = $this->actingAs($this->admin)->postJson('/api/v1/survei', [
        'judul' => 'Survei Draf',
        'status' => 'draf',
        'questions' => [['pertanyaan' => 'Q1?', 'tipe' => 'teks']],
    ])->assertCreated()->json('id');

    $this->actingAs($this->admin)->postJson("/api/v1/survei/{$surveyId}/isi", [
        'jawaban' => [],
    ])->assertStatus(422);
});

it('survei terisolasi per sekolah', function (): void {
    Survey::create([
        'judul' => 'Survei A', 'dibuat_oleh' => $this->admin->id,
    ]);

    $other = School::create([
        'nama' => 'Sekolah Lain', 'slug' => 'lain13-'.uniqid(), 'jenjang' => 'SMA',
        'status' => 'aktif', 'status_langganan' => 'active',
    ]);
    app(TenantContext::class)->set($other);

    expect(Survey::count())->toBe(0);
});

it('survei, form survei, isi survei, dan hasil survei memakai design system', function (): void {
    Livewire::actingAs($this->admin)->test(SurveyIndex::class)
        ->assertSee('Survei')
        ->assertSee('management-page-header')
        ->assertSee('data-table')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(SurveyForm::class)
        ->assertSee('Buat Survei')
        ->assertSee('management-form-card')
        ->assertDontSee('max-w-3xl')
        ->assertDontSee('<i data-lucide');

    $survey = Survey::create([
        'judul' => 'Survei Desain', 'status' => 'aktif', 'target_type' => 'siswa',
        'mulai' => now()->subDay(), 'selesai' => now()->addDay(),
        'dibuat_oleh' => $this->admin->id,
    ]);
    $survey->questions()->create([
        'pertanyaan' => 'Bagaimana pelayanan?', 'tipe' => 'skala', 'urutan' => 1, 'wajib' => true,
    ]);

    Livewire::actingAs($this->admin)->test(SurveyFill::class, ['survey' => $survey])
        ->assertSee('Survei Desain')
        ->assertSee('Kirim Jawaban')
        ->assertDontSee('max-w-3xl')
        ->assertDontSee('<i data-lucide');

    Livewire::actingAs($this->admin)->test(SurveyResult::class, ['survey' => $survey])
        ->assertSee('Survei Desain')
        ->assertSee('Total responden')
        ->assertDontSee('<i data-lucide');
});
