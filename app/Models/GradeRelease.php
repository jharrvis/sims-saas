<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int|null $school_id
 * @property Carbon|null $dirilis_pada
 */
class GradeRelease extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'semester_id',
        'rombel_id',
        'dirilis_pada',
        'dirilis_oleh',
    ];

    protected function casts(): array
    {
        return [
            'dirilis_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Semester, $this>
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * @return BelongsTo<Rombel, $this>
     */
    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class);
    }
}
