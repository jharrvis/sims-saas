<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Ekstrakurikuler sekolah.
 */
class Extracurricular extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'nama',
        'jadwal',
        'pembina_id',
        'aktif',
    ];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function pembina(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'pembina_id');
    }

    /**
     * @return BelongsToMany<Student, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'extracurricular_members')
            ->withPivot(['tanggal_gabung', 'status'])
            ->withTimestamps();
    }
}
