<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Jejak pengingat dunning per tenant (anti-kirim-ganda).
 */
class DunningLog extends Model
{
    protected $fillable = [
        'school_id',
        'jenis',
        'referensi',
        'channel',
        'dikirim_pada',
    ];

    protected function casts(): array
    {
        return [
            'dikirim_pada' => 'datetime',
        ];
    }
}
