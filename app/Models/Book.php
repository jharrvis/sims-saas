<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Katalog buku perpustakaan sekolah.
 */
class Book extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'kode',
        'judul',
        'pengarang',
        'penerbit',
        'tahun_terbit',
        'isbn',
        'kategori',
        'jumlah',
    ];

    /**
     * @return HasMany<BookLoan, $this>
     */
    public function loans(): HasMany
    {
        return $this->hasMany(BookLoan::class);
    }

    public function getTersediaAttribute(): int
    {
        $dipinjam = $this->loans()->where('status', 'dipinjam')->count();

        return max(0, $this->jumlah - $dipinjam);
    }
}
