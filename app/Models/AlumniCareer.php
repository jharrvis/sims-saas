<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat karier alumni (tracer study).
 */
class AlumniCareer extends Model
{
    use BelongsToSchool;

    public const STATUS = [
        'kuliah' => 'Kuliah',
        'kerja' => 'Bekerja',
        'wirausaha' => 'Wirausaha',
        'lainnya' => 'Lainnya',
    ];

    protected $fillable = [
        'alumni_id',
        'status',
        'institusi',
        'jurusan_posisi',
        'tahun_mulai',
        'keterangan',
    ];

    /**
     * @return BelongsTo<Alumni, $this>
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }
}
