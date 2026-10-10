<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Prestasi siswa (akademik / non-akademik, berjenjang).
 */
class Achievement extends Model
{
    use BelongsToSchool;

    public const KATEGORI = [
        'akademik' => 'Akademik',
        'non_akademik' => 'Non-Akademik',
    ];

    public const TINGKAT = [
        'sekolah' => 'Sekolah',
        'kecamatan' => 'Kecamatan',
        'kabupaten' => 'Kabupaten/Kota',
        'provinsi' => 'Provinsi',
        'nasional' => 'Nasional',
        'internasional' => 'Internasional',
    ];

    protected $fillable = [
        'student_id',
        'nama',
        'kategori',
        'tingkat',
        'tanggal',
        'keterangan',
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
}
