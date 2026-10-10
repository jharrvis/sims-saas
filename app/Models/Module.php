<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
