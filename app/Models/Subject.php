<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $school_id
 */
class Subject extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'kode',
        'nama',
        'kelompok',
        'kkm_default',
    ];

    protected function casts(): array
    {
        return [
            'kkm_default' => 'decimal:2',
        ];
    }
}
