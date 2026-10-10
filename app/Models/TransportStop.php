<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Titik penjemputan pada rute.
 */
class TransportStop extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'transport_route_id',
        'nama',
        'urutan',
        'jam_berangkat',
    ];

    protected function casts(): array
    {
        return ['jam_berangkat' => 'datetime:H:i'];
    }

    /**
     * @return BelongsTo<TransportRoute, $this>
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'transport_route_id');
    }
}
