<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Exceptions\TenantContextMissing;
use App\Models\School;

/**
 * Konteks tenant aktif — didaftarkan sebagai singleton.
 *
 * SchoolScope (fail-closed) membaca dari sini. Middleware ResolveTenant
 * mengisinya; operator platform berjalan tanpa konteks (null).
 */
final class TenantContext
{
    private ?School $school = null;

    public function set(School $school): void
    {
        $this->school = $school;
    }

    public function clear(): void
    {
        $this->school = null;
    }

    public function school(): ?School
    {
        return $this->school;
    }

    public function schoolId(): ?int
    {
        return $this->school?->id;
    }

    /**
     * @throws TenantContextMissing
     */
    public function requireSchoolId(): int
    {
        $id = $this->schoolId();

        if ($id === null) {
            throw new TenantContextMissing('Tenant context belum terpasang.');
        }

        return $id;
    }
}
