<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan wali kelas untuk rapor per siswa per semester (add-on E-Rapor).
 */
class RaporNote extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'student_id',
        'semester_id',
        'catatan_wali',
        'updated_by',
    ];

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Semester, $this>
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
