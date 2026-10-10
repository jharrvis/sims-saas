<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Feature flag modul per tenant.
 *
 * @property int|null $school_id
 */
class TenantModule extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'module_id',
        'aktif',
        'konfigurasi',
        'diaktifkan_pada',
        'dinonaktifkan_pada',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'konfigurasi' => 'array',
            'diaktifkan_pada' => 'datetime',
            'dinonaktifkan_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Module, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
