<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Organisasi sekolah (OSIS, MPK, Pramuka, dsb).
 */
class Organization extends Model
{
    use BelongsToSchool;

    public const JENIS = [
        'osis' => 'OSIS',
        'mpk' => 'MPK',
        'pramuka' => 'Pramuka',
        'paskibra' => 'Paskibra',
        'pmr' => 'PMR',
        'rohis' => 'Rohis',
        'lainnya' => 'Lainnya',
    ];

    protected $fillable = [
        'nama',
        'jenis',
        'pembina_id',
        'aktif',
    ];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function pembina(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'pembina_id');
    }

    /**
     * @return BelongsToMany<Student, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'organization_members')
            ->withPivot(['jabatan', 'periode', 'status'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<OrganizationActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(OrganizationActivity::class);
    }
}
