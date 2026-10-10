<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

/**
 * Produk kantin/koperasi sekolah.
 */
class CanteenProduct extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'nama',
        'kategori',
        'harga',
        'stok',
        'aktif',
    ];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function kurangiStok(int $qty): void
    {
        abort_if($qty < 1, 422, 'Jumlah harus minimal 1.');
        abort_if($qty > $this->stok, 422, "Stok {$this->nama} tidak mencukupi.");

        $this->decrement('stok', $qty);
    }
}
