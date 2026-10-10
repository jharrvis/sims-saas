<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Item transaksi kasir kantin.
 */
class CanteenTransactionItem extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'canteen_transaction_id',
        'canteen_product_id',
        'nama',
        'harga',
        'qty',
        'subtotal',
    ];

    /**
     * @return BelongsTo<CanteenTransaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(CanteenTransaction::class, 'canteen_transaction_id');
    }
}
