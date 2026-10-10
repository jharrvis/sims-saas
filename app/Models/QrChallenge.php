<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tantangan QR dinamis (Dok 09 §3.3): 128-bit, sekali pakai, 60 detik.
 */
class QrChallenge extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'student_id',
        'token_hash',
        'expires_at',
        'used_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function isValid(): bool
    {
        /** @var Carbon $expiresAt */
        $expiresAt = $this->expires_at;

        return $this->used_at === null && $expiresAt->isFuture();
    }
}
