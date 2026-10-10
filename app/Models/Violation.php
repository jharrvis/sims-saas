<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan pelanggaran siswa.
 */
class Violation extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'student_id',
        'violation_type_id',
        'tanggal',
        'keterangan',
        'tindak_lanjut',
        'dilaporkan_oleh',
    ];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<ViolationType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ViolationType::class, 'violation_type_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilaporkan_oleh');
    }
}
