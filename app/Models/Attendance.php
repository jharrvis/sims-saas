<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int|null $school_id
 * @property Carbon|null $tanggal
 * @property Carbon|null $locked_at
 */
class Attendance extends Model
{
    use BelongsToSchool;

    public const STATUS = ['H' => 'Hadir', 'I' => 'Izin', 'S' => 'Sakit', 'A' => 'Alpa'];

    protected $fillable = [
        'school_id',
        'student_id',
        'rombel_id',
        'tanggal',
        'status',
        'keterangan',
        'input_by',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'locked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Rombel, $this>
     */
    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class);
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }
}
