<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Peminjaman buku: pinjam → kembali/terlambat (+ denda).
 */
class BookLoan extends Model
{
    use BelongsToSchool;

    public const STATUS = [
        'dipinjam' => 'Dipinjam',
        'kembali' => 'Kembali',
        'terlambat' => 'Terlambat',
    ];

    /** Denda per hari keterlambatan (rupiah). */
    public const DENDA_PER_HARI = 1000;

    protected $fillable = [
        'book_id',
        'student_id',
        'tgl_pinjam',
        'tgl_jatuh_tempo',
        'tgl_kembali',
        'status',
        'denda',
        'dipinjamkan_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pinjam' => 'date',
            'tgl_jatuh_tempo' => 'date',
            'tgl_kembali' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Book, $this>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Tandai kembali: hitung keterlambatan + denda otomatis.
     */
    public function tandaiKembali(): void
    {
        $today = now()->toDateString();
        /** @var Carbon $tempo */
        $tempo = $this->tgl_jatuh_tempo;

        $terlambatHari = $today > $tempo->toDateString()
            ? (int) $tempo->diffInDays($today)
            : 0;

        $this->update([
            'tgl_kembali' => $today,
            'status' => $terlambatHari > 0 ? 'terlambat' : 'kembali',
            'denda' => $terlambatHari * self::DENDA_PER_HARI,
        ]);
    }
}
