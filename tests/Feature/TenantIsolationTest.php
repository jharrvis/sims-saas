<?php

declare(strict_types=1);

use App\Concerns\BelongsToSchool;
use App\Exceptions\TenantContextMissing;
use App\Models\School;
use App\Scopes\SchoolScope;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * @property int|null $school_id
 * @property string|null $nama
 */
class ProbeModel extends Model
{
    use BelongsToSchool;

    protected $table = 'probe_models';

    protected $guarded = [];
}

beforeEach(function (): void {
    Schema::create('probe_models', function ($table): void {
        $table->id();
        $table->foreignId('school_id')->nullable()->index();
        $table->string('nama')->nullable();
        $table->timestamps();
    });

    // Pastikan tidak ada konteks tenant yang bocor antar test
    app(TenantContext::class)->clear();
});

afterEach(function (): void {
    Schema::dropIfExists('probe_models');
    app(TenantContext::class)->clear();
});

function buatSekolah(string $nama, string $slug): School
{
    return School::create([
        'nama' => $nama,
        'slug' => $slug,
        'jenjang' => 'SD',
        'status' => 'aktif',
        'status_langganan' => 'active',
    ]);
}

it('melempar TenantContextMissing saat query tanpa konteks tenant (fail-closed)', function (): void {
    ProbeModel::query()->get();
})->throws(TenantContextMissing::class);

it('hanya menampilkan data tenant aktif', function (): void {
    $a = buatSekolah('Sekolah A', 'sekolah-a');
    $b = buatSekolah('Sekolah B', 'sekolah-b');

    ProbeModel::withoutGlobalScope(SchoolScope::class)->create(['school_id' => $a->id, 'nama' => 'milik A']);
    ProbeModel::withoutGlobalScope(SchoolScope::class)->create(['school_id' => $b->id, 'nama' => 'milik B']);

    app(TenantContext::class)->set($a);

    $hasil = ProbeModel::all();

    expect($hasil)->toHaveCount(1);
    expect($hasil->first()?->nama)->toBe('milik A');
});

it('mengisi school_id otomatis saat create dalam konteks tenant', function (): void {
    $a = buatSekolah('Sekolah A', 'sekolah-a2');

    app(TenantContext::class)->set($a);

    $probe = ProbeModel::create(['nama' => 'baru']);

    expect($probe->school_id)->toBe($a->id);
});

it('data tenant B tidak bocor ke konteks tenant A (negatif)', function (): void {
    $a = buatSekolah('Sekolah A', 'sekolah-a3');
    $b = buatSekolah('Sekolah B', 'sekolah-b3');

    ProbeModel::withoutGlobalScope(SchoolScope::class)->create(['school_id' => $b->id, 'nama' => 'rahasia B']);

    app(TenantContext::class)->set($a);

    expect(ProbeModel::where('nama', 'rahasia B')->exists())->toBeFalse();
});
