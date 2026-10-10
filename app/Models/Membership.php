<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Keanggotaan user pada tenant (ADR-0004).
 */
class Membership extends Model
{
    protected $fillable = [
        'user_id',
        'school_id',
        'status',
        'berakhir_pada',
    ];

    protected function casts(): array
    {
        return [
            'berakhir_pada' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Membership>  $query
     */
    public function scopeAktif(Builder $query): void
    {
        $query->where('status', 'aktif')
            ->where(fn (Builder $q) => $q->whereNull('berakhir_pada')->orWhere('berakhir_pada', '>', now()));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
