<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Override batas kuota per tenant oleh operator platform.
 * Tidak berlaku untuk masa trial (Dok 02 §8.13).
 */
class TenantQuotaOverride extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'metrik',
        'batas_custom',
        'alasan',
        'dibuat_oleh',
        'berlaku_sampai',
    ];

    protected function casts(): array
    {
        return [
            'berlaku_sampai' => 'datetime',
        ];
    }

    public function isBerlaku(): bool
    {
        /** @var Carbon|null $sampai */
        $sampai = $this->berlaku_sampai;

        return $sampai === null || $sampai->isFuture();
    }
}
