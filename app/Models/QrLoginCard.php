<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kartu QR login cetak per siswa (Dok 09 §3.3).
 * Token plaintext hanya diketahui saat penerbitan; yang disimpan hash-nya.
 */
class QrLoginCard extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'student_id',
        'token_hash',
        'aktif',
        'issued_at',
        'revoked_at',
        'issued_by',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'issued_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function isValid(): bool
    {
        return $this->aktif && $this->revoked_at === null;
    }
}
