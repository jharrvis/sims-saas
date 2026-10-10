<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Stok obat UKS.
 */
class Medicine extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'nama',
        'satuan',
        'stok',
        'stok_minimum',
    ];

    /**
     * @return HasMany<MedicineUsage, $this>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(MedicineUsage::class);
    }

    public function isStokRendah(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }

    /**
     * Catat pemakaian obat; kurangi stok.
     */
    public function pakai(int $jumlah, int $oleh, ?int $visitId = null): MedicineUsage
    {
        abort_if($jumlah < 1, 422, 'Jumlah harus minimal 1.');
        abort_if($jumlah > $this->stok, 422, 'Stok obat tidak mencukupi.');

        $this->decrement('stok', $jumlah);

        return $this->usages()->create([
            'health_visit_id' => $visitId,
            'jumlah' => $jumlah,
            'tgl' => now()->toDateString(),
            'dicatat_oleh' => $oleh,
        ]);
    }
}
