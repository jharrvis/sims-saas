<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Survei/evaluasi.
 */
class Survey extends Model
{
    use BelongsToSchool;

    public const STATUS = [
        'draf' => 'Draf',
        'aktif' => 'Aktif',
        'tutup' => 'Ditutup',
    ];

    public const TARGET = [
        'semua' => 'Semua',
        'siswa' => 'Siswa',
        'guru' => 'Guru/Karyawan',
        'orang_tua' => 'Orang Tua',
    ];

    protected $fillable = [
        'judul',
        'deskripsi',
        'mulai',
        'selesai',
        'status',
        'target_type',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'mulai' => 'datetime',
            'selesai' => 'datetime',
        ];
    }

    /**
     * @return HasMany<SurveyQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('urutan');
    }

    /**
     * @return HasMany<SurveyResponse, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function isDapatDiisi(): bool
    {
        if ($this->status !== 'aktif') {
            return false;
        }

        $now = now();
        /** @var Carbon|null $mulai */
        $mulai = $this->mulai;
        /** @var Carbon|null $selesai */
        $selesai = $this->selesai;

        return ($mulai === null || $mulai->lte($now))
            && ($selesai === null || $selesai->gte($now));
    }
}
