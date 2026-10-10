<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Data alumni sekolah.
 */
class Alumni extends Model
{
    use BelongsToSchool;

    protected $table = 'alumni';

    protected $fillable = [
        'student_id',
        'nama',
        'tahun_lulus',
        'phone',
        'email',
        'alamat',
    ];

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return HasMany<AlumniCareer, $this>
     */
    public function careers(): HasMany
    {
        return $this->hasMany(AlumniCareer::class);
    }

    /**
     * Status karier terakhir (tracer study).
     */
    public function karierTerakhir(): ?AlumniCareer
    {
        return $this->careers()->orderByDesc('tahun_mulai')->orderByDesc('id')->first();
    }
}
