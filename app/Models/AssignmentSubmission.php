<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pengumpulan tugas oleh siswa.
 */
class AssignmentSubmission extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'jawaban',
        'tgl_kumpul',
        'nilai',
        'feedback',
    ];

    protected function casts(): array
    {
        return ['tgl_kumpul' => 'datetime'];
    }

    /**
     * @return BelongsTo<Assignment, $this>
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function isTerlambat(): bool
    {
        /** @var Carbon $tglKumpul */
        $tglKumpul = $this->tgl_kumpul;

        return $tglKumpul->greaterThan($this->assignment->deadline);
    }
}
