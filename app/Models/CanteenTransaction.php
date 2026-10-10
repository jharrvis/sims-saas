<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Transaksi kasir kantin.
 */
class CanteenTransaction extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'kode',
        'student_id',
        'total',
        'bayar',
        'kembalian',
        'kasir_id',
        'tgl',
    ];

    protected function casts(): array
    {
        return ['tgl' => 'datetime'];
    }

    /**
     * @return HasMany<CanteenTransactionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CanteenTransactionItem::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Buat transaksi dari keranjang: validasi stok, kurangi stok, catat item.
     *
     * @param  array<int, array{product_id: int, qty: int}>  $keranjang
     */
    public static function checkout(array $keranjang, ?int $studentId, float $bayar, int $kasirId): self
    {
        abort_if($keranjang === [], 422, 'Keranjang kosong.');

        return \DB::transaction(function () use ($keranjang, $studentId, $bayar, $kasirId): self {
            $total = 0.0;
            $items = [];

            foreach ($keranjang as $row) {
                $product = CanteenProduct::lockForUpdate()->findOrFail($row['product_id']);
                $qty = (int) $row['qty'];

                $product->kurangiStok($qty);

                $subtotal = (float) $product->harga * $qty;
                $total += $subtotal;

                $items[] = [
                    'canteen_product_id' => $product->id,
                    'nama' => $product->nama,
                    'harga' => $product->harga,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            abort_if($bayar < $total, 422, 'Uang bayar kurang dari total.');

            $trx = self::create([
                'kode' => 'KTN-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'student_id' => $studentId,
                'total' => $total,
                'bayar' => $bayar,
                'kembalian' => $bayar - $total,
                'kasir_id' => $kasirId,
                'tgl' => now(),
            ]);

            $trx->items()->createMany($items);

            return $trx;
        });
    }
}
