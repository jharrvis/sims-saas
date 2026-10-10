<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Langganan aktif per sekolah.
 */
class Subscription extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'plan_id',
        'status',
        'periode',
        'trial_berakhir_pada',
        'dibatalkan_pada',
        'periode_berakhir_pada',
    ];

    protected function casts(): array
    {
        return [
            'trial_berakhir_pada' => 'datetime',
            'dibatalkan_pada' => 'datetime',
            'periode_berakhir_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return HasMany<SubscriptionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    /**
     * @return HasMany<BillingInvoice, $this>
     */
    public function billingInvoices(): HasMany
    {
        return $this->hasMany(BillingInvoice::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['trial', 'active'], true);
    }
}
