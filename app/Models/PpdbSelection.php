<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hasil seleksi pendaftar PPDB — tenant scope mengikuti applicant.
 */
class PpdbSelection extends Model
{
    protected $fillable = [
        'ppdb_applicant_id',
        'nilai_tes',
        'nilai_wawancara',
        'nilai_akhir',
        'peringkat',
        'keputusan',
        'dinilai_oleh',
    ];

    protected function casts(): array
    {
        return [
            'nilai_tes' => 'decimal:2',
            'nilai_wawancara' => 'decimal:2',
            'nilai_akhir' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<PpdbApplicant, $this>
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(PpdbApplicant::class, 'ppdb_applicant_id');
    }
}
