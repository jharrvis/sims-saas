<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Wali murid (FR-302).
 *
 * Semantik hubungan (QA B-08): kolom `hubungan` di tabel ini adalah
 * HUBUNGAN UTAMA/bawaan wali — ditampilkan di daftar dan statistik, dan
 * menjadi nilai awal saat menautkan siswa. Hubungan yang berlaku terhadap
 * siswa tertentu disimpan di pivot `guardian_student.hubungan` dan itulah
 * yang ditampilkan di detail siswa serta dipakai untuk hak akses orang
 * tua; bila keduanya berbeda, pivot yang menang untuk siswa tersebut.
 *
 * @property int|null $school_id
 */
class Guardian extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'nik',
        'nik_hash',
        'nama',
        'hubungan',
        'pekerjaan',
        'no_wa',
        'email',
        'alamat',
    ];

    protected function casts(): array
    {
        return [
            'nik' => 'encrypted', // Dok 02 §7: NIK terenkripsi
        ];
    }

    protected static function booted(): void
    {
        // nik_hash selalu diturunkan dari NIK polos agar deteksi duplikat
        // (QA B-05) konsisten di semua jalur simpan.
        static::saving(function (Guardian $guardian): void {
            $guardian->nik_hash = self::hashNik($guardian->nik);
        });
    }

    /**
     * HMAC-SHA256 dari NIK polos dengan kunci aplikasi. Deterministik
     * (bisa di-query/diindeks) tetapi tidak bisa dibalik ke NIK asal.
     */
    public static function hashNik(?string $nik): ?string
    {
        if ($nik === null || $nik === '') {
            return null;
        }

        return hash_hmac('sha256', $nik, (string) config('app.key'));
    }

    /**
     * @return BelongsToMany<Student, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
            ->withPivot('hubungan')
            ->withTimestamps();
    }
}
