<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $school_id
 */
class AcademicYear extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'nama',
        'tgl_mulai',
        'tgl_selesai',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'tgl_mulai' => 'date',
            'tgl_selesai' => 'date',
            'aktif' => 'boolean',
        ];
    }

    /**
     * Apakah tahun ajaran masih terbuka untuk perubahan data (US-026).
     *
     * Satu-satunya penanda buka/tutup yang ada di skema adalah flag aktif:
     * tidak ada kolom/status "ditutup". Tahun ajaran aktif berarti terbuka;
     * tanpa id, id tak ditemukan, atau tahun ajaran tidak aktif berarti
     * tertutup. Kueri terskop tenant lewat SchoolScope.
     */
    public static function masihTerbuka(?int $id): bool
    {
        if ($id === null) {
            return false;
        }

        return static::whereKey($id)->where('aktif', true)->exists();
    }
}
