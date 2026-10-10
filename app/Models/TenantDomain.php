<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantDomain extends Model
{
    protected $fillable = [
        'school_id',
        'domain',
        'tipe',
        'terverifikasi',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'terverifikasi' => 'boolean',
            'aktif' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
