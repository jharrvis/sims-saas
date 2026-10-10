<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log pengiriman broadcast per penerima.
 */
class BroadcastLog extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'broadcast_id',
        'penerima',
        'status',
        'error',
        'dikirim_pada',
    ];

    protected function casts(): array
    {
        return ['dikirim_pada' => 'datetime'];
    }

    /**
     * @return BelongsTo<Broadcast, $this>
     */
    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(Broadcast::class);
    }
}
