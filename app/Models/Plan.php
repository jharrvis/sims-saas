<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Paket langganan — level PLATFORM (tanpa school_id).
 */
class Plan extends Model
{
    protected $fillable = [
        'nama',
        'slug',
        'deskripsi',
        'harga_bulanan',
        'harga_tahunan',
        'max_siswa',
        'max_storage_mb',
        'max_user_staf',
        'fitur',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'fitur' => 'array',
            'aktif' => 'boolean',
        ];
    }

    public function hargaPeriode(string $periode): int
    {
        return $periode === 'tahunan' && $this->harga_tahunan
            ? (int) $this->harga_tahunan
            : (int) $this->harga_bulanan;
    }
}
