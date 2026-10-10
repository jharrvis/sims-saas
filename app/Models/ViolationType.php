<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master jenis pelanggaran tata tertib + bobot poin.
 */
class ViolationType extends Model
{
    use BelongsToSchool;

    public const KATEGORI = [
        'ringan' => 'Ringan',
        'sedang' => 'Sedang',
        'berat' => 'Berat',
    ];

    protected $fillable = [
        'nama',
        'kategori',
        'poin',
        'aktif',
    ];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    /**
     * @return HasMany<Violation, $this>
     */
    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }
}
