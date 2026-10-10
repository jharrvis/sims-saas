<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\School;
use App\Scopes\SchoolScope;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait untuk semua model operasional milik tenant.
 *
 * - Mendaftarkan SchoolScope (fail-closed).
 * - Mengisi school_id otomatis dari konteks aktif saat create.
 * - Akses lintas tenant: Model::withoutGlobalScope(SchoolScope::class)
 *   — SATU-SATUNYA jalan; wajib dipasangkan policy + audit log.
 *
 * @property int|null $school_id
 */
trait BelongsToSchool
{
    public static function bootBelongsToSchool(): void
    {
        static::addGlobalScope(new SchoolScope);

        static::creating(function (Model $model): void {
            $schoolId = app(TenantContext::class)->schoolId();

            if ($model->getAttribute('school_id') === null && $schoolId !== null) {
                $model->setAttribute('school_id', $schoolId);
            }
        });
    }

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
