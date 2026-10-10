<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MfaSecret extends Model
{
    protected $fillable = [
        'user_id',
        'secret',
        'confirmed_at',
        'recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'secret' => 'encrypted',
            'confirmed_at' => 'datetime',
            'recovery_codes' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }
}
