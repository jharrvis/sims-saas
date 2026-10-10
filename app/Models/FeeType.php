<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $school_id
 */
class FeeType extends Model
{
    use BelongsToSchool;

    public const PERIODES = ['bulanan', 'semesteran', 'tahunan', 'sekali'];

    protected $fillable = [
        'school_id',
        'nama',
        'nominal',
        'periode',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'aktif' => 'boolean',
        ];
    }
}
