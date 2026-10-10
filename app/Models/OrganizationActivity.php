<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kegiatan organisasi sekolah.
 */
class OrganizationActivity extends Model
{
    use BelongsToSchool;

    public const STATUS = [
        'rencana' => 'Rencana',
        'berjalan' => 'Berjalan',
        'selesai' => 'Selesai',
        'batal' => 'Batal',
    ];

    protected $fillable = [
        'organization_id',
        'nama',
        'tgl_mulai',
        'tgl_selesai',
        'deskripsi',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tgl_mulai' => 'date',
            'tgl_selesai' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
