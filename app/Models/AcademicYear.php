<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $school_id
 */
class AcademicYear extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'nama',
        'tgl_mulai',
        'tgl_selesai',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'tgl_mulai' => 'date',
            'tgl_selesai' => 'date',
            'aktif' => 'boolean',
        ];
    }
}
