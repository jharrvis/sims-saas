<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kunjungan siswa ke UKS.
 */
class HealthVisit extends Model
{
    use BelongsToSchool;

    public const RUJUKAN = [
        'tidak' => 'Tidak dirujuk',
        'puskesmas' => 'Puskesmas',
        'rumah_sakit' => 'Rumah Sakit',
    ];

    protected $fillable = [
        'student_id',
        'tgl',
        'keluhan',
        'diagnosa',
        'tindakan',
        'suhu',
        'tekanan_darah',
        'rujukan',
        'ditangani_oleh',
    ];

    protected function casts(): array
    {
        return ['tgl' => 'datetime'];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return HasMany<MedicineUsage, $this>
     */
    public function medicineUsages(): HasMany
    {
        return $this->hasMany(MedicineUsage::class);
    }
}
