<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Katalog modul — level PLATFORM, tanpa school_id.
 */
class Module extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'kategori',
        'termasuk_paket',
        'harga_addon_bulanan',
        'prasyarat',
        'versi',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'termasuk_paket' => 'array',
            'prasyarat' => 'array',
            'aktif' => 'boolean',
        ];
    }

    public function isInti(): bool
    {
        return $this->kategori === 'inti';
    }

    /**
     * @return HasOne<TenantModule, $this>
     */
    public function tenantModule(): HasOne
    {
        return $this->hasOne(TenantModule::class, 'module_id');
    }
}
