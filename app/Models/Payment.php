<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int|null $school_id
 * @property Carbon|null $tgl_bayar
 */
class Payment extends Model
{
    use BelongsToSchool;

    public const METODES = ['tunai' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS', 'va' => 'Virtual Account', 'ewallet' => 'E-Wallet'];

    protected $fillable = [
        'school_id',
        'invoice_id',
        'no_kuitansi',
        'nominal',
        'tgl_bayar',
        'metode',
        'bukti_path',
        'catatan',
        'input_by',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'tgl_bayar' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
