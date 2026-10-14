<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * @property int|null $school_id
 */
class Employee extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'import_batch_id',
        'nip_nuptk',
        'nama',
        'phone',
        'jenis',
        'status_kepegawaian',
        'jabatan',
        'foto_path',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Rombel, $this>
     */
    public function rombelsAsWali(): HasMany
    {
        return $this->hasMany(Rombel::class, 'wali_kelas_id');
    }

    public function labelJenis(): string
    {
        return match ($this->jenis) {
            'guru_mapel' => 'Guru Mapel',
            'guru_kelas' => 'Guru Kelas',
            default => 'Tendik',
        };
    }

    public function fotoUrl(): ?string
    {
        return $this->foto_path
            ? Storage::disk('public')->url($this->foto_path)
            : null;
    }
}
