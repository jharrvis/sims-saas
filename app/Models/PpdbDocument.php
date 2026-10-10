<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Berkas pendaftar PPDB — tenant scope mengikuti applicant.
 */
class PpdbDocument extends Model
{
    protected $fillable = [
        'ppdb_applicant_id',
        'jenis',
        'file_path',
        'nama_asli',
        'terverifikasi',
    ];

    protected function casts(): array
    {
        return [
            'terverifikasi' => 'boolean',
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
