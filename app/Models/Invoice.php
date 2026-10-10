<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int|null $school_id
 * @property Carbon|null $jatuh_tempo
 */
class Invoice extends Model
{
    use BelongsToSchool;

    public const STATUS = ['unpaid' => 'Belum Bayar', 'partial' => 'Sebagian', 'paid' => 'Lunas', 'overdue' => 'Jatuh Tempo'];

    protected $fillable = [
        'school_id',
        'student_id',
        'fee_type_id',
        'no_invoice',
        'periode_label',
        'nominal',
        'dibayar',
        'jatuh_tempo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'dibayar' => 'decimal:2',
            'jatuh_tempo' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<FeeType, $this>
     */
    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function sisa(): float
    {
        return (float) $this->nominal - (float) $this->dibayar;
    }

    public function labelStatus(): string
    {
        return self::STATUS[$this->status];
    }
}
