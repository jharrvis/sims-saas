<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Aset/barang inventaris sekolah.
 */
class Asset extends Model
{
    use BelongsToSchool;

    public const KONDISI = [
        'baik' => 'Baik',
        'rusak_ringan' => 'Rusak Ringan',
        'rusak_berat' => 'Rusak Berat',
        'hilang' => 'Hilang',
    ];

    protected $fillable = [
        'asset_category_id',
        'kode',
        'nama',
        'kondisi',
        'lokasi',
        'tgl_beli',
        'harga_beli',
        'sumber_dana',
    ];

    protected function casts(): array
    {
        return ['tgl_beli' => 'date'];
    }

    /**
     * @return BelongsTo<AssetCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    /**
     * @return HasMany<AssetMutation, $this>
     */
    public function mutations(): HasMany
    {
        return $this->hasMany(AssetMutation::class);
    }

    /**
     * Catat mutasi sekaligus perbarui kondisi/lokasi bila relevan.
     *
     * @param  array{dari?: ?string, ke?: ?string, catatan?: ?string, tgl?: ?string}  $data
     */
    public function catatMutasi(string $jenis, array $data, int $oleh): AssetMutation
    {
        $mutation = $this->mutations()->create([
            'jenis' => $jenis,
            'dari' => $data['dari'] ?? null,
            'ke' => $data['ke'] ?? null,
            'catatan' => $data['catatan'] ?? null,
            'dicatat_oleh' => $oleh,
            'tgl' => $data['tgl'] ?? now()->toDateString(),
        ]);

        match ($jenis) {
            'pindah' => $this->update(['lokasi' => $data['ke'] ?? $this->lokasi]),
            'kondisi' => $this->update(['kondisi' => $data['ke'] ?? $this->kondisi]),
            default => null,
        };

        return $mutation;
    }
}
