<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sesi konseling BK. Catatan bersifat rahasia — akses dibatasi
 * permission bk.* (guru BK, admin, kepala sekolah).
 */
class CounselingSession extends Model
{
    use BelongsToSchool;

    public const JENIS = [
        'individu' => 'Individu',
        'kelompok' => 'Kelompok',
        'klasikal' => 'Klasikal',
    ];

    public const STATUS = [
        'terjadwal' => 'Terjadwal',
        'selesai' => 'Selesai',
        'batal' => 'Batal',
    ];

    protected $fillable = [
        'student_id',
        'counselor_id',
        'tanggal',
        'jenis',
        'topik',
        'catatan',
        'tindak_lanjut',
        'status',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }
}
