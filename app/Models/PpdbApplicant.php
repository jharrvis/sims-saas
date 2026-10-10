<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int|null $school_id
 * @property Carbon|null $tgl_lahir
 */
class PpdbApplicant extends Model
{
    use BelongsToSchool;

    public const STATUS = [
        'draft' => 'Draf',
        'submitted' => 'Terkirim',
        'verified' => 'Terverifikasi',
        'accepted' => 'Diterima',
        'rejected' => 'Ditolak',
        'enrolled' => 'Daftar Ulang',
    ];

    protected $fillable = [
        'school_id',
        'ppdb_period_id',
        'no_pendaftaran',
        'nama',
        'nisn',
        'jk',
        'tgl_lahir',
        'asal_sekolah',
        'nama_ortu',
        'no_hp_ortu',
        'status',
        'catatan',
        'student_id',
    ];

    protected function casts(): array
    {
        return [
            'tgl_lahir' => 'date',
        ];
    }

    /**
     * @return BelongsTo<PpdbPeriod, $this>
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(PpdbPeriod::class, 'ppdb_period_id');
    }

    /**
     * @return HasMany<PpdbDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(PpdbDocument::class, 'ppdb_applicant_id');
    }

    /**
     * @return HasOne<PpdbSelection, $this>
     */
    public function selection(): HasOne
    {
        return $this->hasOne(PpdbSelection::class, 'ppdb_applicant_id');
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function labelStatus(): string
    {
        return self::STATUS[$this->status];
    }
}
