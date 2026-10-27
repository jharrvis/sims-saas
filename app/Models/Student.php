<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int|null $school_id
 * @property Carbon|null $tgl_lahir
 */
class Student extends Model
{
    use BelongsToSchool, SoftDeletes;

    /** Daftar agama resmi di Indonesia (6 agama yang diakui pemerintah). */
    public const AGAMAS = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

    protected $fillable = [
        'school_id',
        'import_batch_id',
        'nis',
        'nisn',
        'angkatan',
        'nama',
        'jk',
        'tempat_lahir',
        'tgl_lahir',
        'agama',
        'alamat',
        'phone',
        'foto_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tgl_lahir' => 'date',
        ];
    }

    /**
     * URL publik foto siswa (null bila belum ada foto — pakai avatar inisial).
     */
    public function fotoUrl(): ?string
    {
        return $this->foto_path
            ? Storage::disk('public')->url($this->foto_path)
            : null;
    }

    /**
     * @return BelongsToMany<Guardian, $this>
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student')
            ->withPivot('hubungan')
            ->withTimestamps();
    }

    /**
     * Kartu QR login terbaru (aktif maupun riwayat).
     *
     * @return HasOne<QrLoginCard, $this>
     */
    public function qrCard(): HasOne
    {
        return $this->hasOne(QrLoginCard::class)->latestOfMany();
    }

    /**
     * @return BelongsToMany<Rombel, $this>
     */
    public function rombels(): BelongsToMany
    {
        return $this->belongsToMany(Rombel::class, 'class_student')
            ->withPivot(['academic_year_id', 'status'])
            ->withTimestamps();
    }
}
