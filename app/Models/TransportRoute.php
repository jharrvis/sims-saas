<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Rute antar-jemput sekolah.
 */
class TransportRoute extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'nama',
        'sopir',
        'no_kendaraan',
        'kapasitas',
        'aktif',
    ];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    /**
     * @return HasMany<TransportStop, $this>
     */
    public function stops(): HasMany
    {
        return $this->hasMany(TransportStop::class)->orderBy('urutan');
    }

    /**
     * @return BelongsToMany<Student, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'transport_members')
            ->withPivot(['transport_stop_id', 'status'])
            ->withTimestamps();
    }

    public function isPenuh(): bool
    {
        return $this->kapasitas > 0
            && $this->members()->wherePivot('status', 'aktif')->count() >= $this->kapasitas;
    }
}
