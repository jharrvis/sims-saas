<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int|null $school_id
 * @property Carbon|null $tgl_lahir
 */
class Student extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'nis',
        'nisn',
        'nama',
        'jk',
        'tempat_lahir',
        'tgl_lahir',
        'agama',
        'alamat',
        'phone',
        'foto_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tgl_lahir' => 'date',
        ];
    }

    /**
     * @return BelongsToMany<Guardian, $this>
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student')
            ->withPivot('hubungan')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Rombel, $this>
     */
    public function rombels(): BelongsToMany
    {
        return $this->belongsToMany(Rombel::class, 'class_student')
            ->withPivot(['academic_year_id', 'status'])
            ->withTimestamps();
    }
}
