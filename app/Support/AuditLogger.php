<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\AuditLog;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;

/**
 * Penulis audit log terpusat untuk aksi penting (QA B-20).
 *
 * Payload sengaja ringkas (identitas pokok saja) agar log tidak ikut
 * menyimpan data sensitif seperti NIK atau nomor telepon.
 */
class AuditLogger
{
    /**
     * @param  array<string,mixed>|null  $before
     * @param  array<string,mixed>|null  $after
     */
    public static function log(string $aksi, Model $model, ?array $before = null, ?array $after = null): void
    {
        AuditLog::create([
            'school_id' => $model->getAttribute('school_id') ?? app(TenantContext::class)->schoolId(),
            'user_id' => auth()->id(),
            'aksi' => $aksi,
            'model_type' => $model::class,
            'model_id' => (string) $model->getKey(),
            'before' => $before,
            'after' => $after,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);
    }
}
