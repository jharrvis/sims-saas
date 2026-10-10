<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tagihan langganan sekolah ke platform.
 * Deviasi Dok 02: bernama `billing_invoices` karena `invoices`
 * sudah dipakai tagihan SPP sekolah.
 */
class BillingInvoice extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'nama_sekolah_snapshot',
        'npsn_snapshot',
        'subscription_id',
        'nomor',
        'periode_dari',
        'periode_sampai',
        'subtotal',
        'diskon',
        'pajak',
        'total',
        'status',
        'jatuh_tempo',
        'dibayar_pada',
        'metode_bayar',
        'midtrans_order_id',
        'snap_token',
        'payment_url',
    ];

    protected function casts(): array
    {
        return [
            'periode_dari' => 'date',
            'periode_sampai' => 'date',
            'jatuh_tempo' => 'date',
            'dibayar_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
