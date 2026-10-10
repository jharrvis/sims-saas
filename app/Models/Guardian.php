<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int|null $school_id
 */
class Guardian extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'nik',
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
