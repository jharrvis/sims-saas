<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int|null $school_id
 */
class Rombel extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'tingkat',
        'nama',
        'kapasitas',
        'wali_kelas_id',
    ];

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'wali_kelas_id');
    }

    /**
     * @return BelongsToMany<Student, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'class_student')
            ->withPivot(['academic_year_id', 'status'])
            ->withTimestamps();
    }
}
