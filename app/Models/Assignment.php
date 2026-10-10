<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tugas online LMS Mini.
 */
class Assignment extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'subject_id',
        'rombel_id',
        'judul',
        'deskripsi',
        'deadline',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return ['deadline' => 'datetime'];
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return BelongsTo<Rombel, $this>
     */
    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class);
    }

    /**
     * @return HasMany<AssignmentSubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function isTerlambat(): bool
    {
        return now()->greaterThan($this->deadline);
    }
}
