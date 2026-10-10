<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pemakaian obat UKS.
 */
class MedicineUsage extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'medicine_id',
        'health_visit_id',
        'jumlah',
        'tgl',
        'dicatat_oleh',
    ];

    protected function casts(): array
    {
        return ['tgl' => 'date'];
    }

    /**
     * @return BelongsTo<Medicine, $this>
     */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * @return BelongsTo<HealthVisit, $this>
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(HealthVisit::class, 'health_visit_id');
    }
}
