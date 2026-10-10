<?php

declare(strict_types=1);

namespace App\Scopes;

use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope isolasi tenant — FAIL-CLOSED (P0-02, ADR-0002).
 *
 * Setiap query model yang memakai trait BelongsToSchool WAJIB berjalan dalam
 * konteks tenant yang valid. Tanpa konteks → exception, bukan query tanpa filter.
 *
 * Akses lintas tenant yang sah (konsolidasi, operator platform) hanya via:
 *   Model::withoutGlobalScope(SchoolScope::class)  +  policy khusus  +  audit log
 *
 * @implements Scope<Model>
 */
final class SchoolScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        $builder->where($model->getTable().'.school_id', $schoolId);
    }
}
