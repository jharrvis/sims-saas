<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat mutasi aset.
 */
class AssetMutation extends Model
{
    use BelongsToSchool;

    public const JENIS = [
        'masuk' => 'Barang Masuk',
        'pindah' => 'Pindah Lokasi',
        'kondisi' => 'Ubah Kondisi',
        'keluar' => 'Barang Keluar',
    ];

    protected $fillable = [
        'asset_id',
        'jenis',
        'dari',
        'ke',
        'catatan',
        'dicatat_oleh',
        'tgl',
    ];

    protected function casts(): array
    {
        return ['tgl' => 'date'];
    }

    /**
     * @return BelongsTo<Asset, $this>
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
