<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Langganan Web Push per perangkat (Dok 03 §5.3/5.4, FR-1304).
 */
class PushSubscription extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh',
        'auth',
        'user_agent',
        'terakhir_dipakai_at',
    ];

    protected function casts(): array
    {
        return [
            'terakhir_dipakai_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
