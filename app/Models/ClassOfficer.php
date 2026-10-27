<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pengurus kelas: ketua, wakil, sekretaris, dan bendahara yang dipilih dari
 * anggota kelas untuk satu tahun ajaran. Satu siswa memegang paling banyak
 * satu jabatan, dan satu jabatan dipegang satu siswa (ditegakkan unique
 * index di migrasi).
 */
class ClassOfficer extends Model
{
    use BelongsToSchool;

    /** Jabatan => label tampil. */
    public const JABATAN = [
        'ketua' => 'Ketua Kelas',
        'wakil' => 'Wakil Ketua Kelas',
        'sekretaris' => 'Sekretaris',
        'bendahara' => 'Bendahara',
    ];

    protected $fillable = [
        'school_id',
        'rombel_id',
        'student_id',
        'academic_year_id',
        'jabatan',
    ];

    /**
     * @return BelongsTo<Rombel, $this>
     */
    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
