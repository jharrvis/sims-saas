<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

/**
 * Pemakaian kuota tenant per metrik per periode (YYYY-MM).
 */
class TenantUsage extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'metrik',
        'nilai',
        'periode',
    ];
}
