<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $school_id
 */
class Schedule extends Model
{
    use BelongsToSchool;

    public const HARIS = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

    protected $fillable = [
        'school_id',
        'rombel_id',
        'academic_year_id',
        'hari',
        'jam_ke',
        'subject_id',
        'employee_id',
        'ruang',
    ];

    /**
     * @return BelongsTo<Rombel, $this>
     */
    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class);
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function labelHari(): string
    {
        return ucfirst($this->hari);
    }
}
