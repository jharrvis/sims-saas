<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int|null $school_id
 * @property Carbon|null $tgl_buka
 * @property Carbon|null $tgl_tutup
 */
class PpdbPeriod extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'nama',
        'tgl_buka',
        'tgl_tutup',
        'kuota',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'tgl_buka' => 'date',
            'tgl_tutup' => 'date',
            'aktif' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return HasMany<PpdbApplicant, $this>
     */
    public function applicants(): HasMany
    {
        return $this->hasMany(PpdbApplicant::class, 'ppdb_period_id');
    }

    public function isOpen(): bool
    {
        $today = today();

        return $this->aktif
            && $today->greaterThanOrEqualTo($this->tgl_buka)
            && $today->lessThanOrEqualTo($this->tgl_tutup);
    }
}
