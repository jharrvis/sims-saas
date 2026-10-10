<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar saat query model tenant dijalankan tanpa konteks tenant yang valid.
 *
 * Ini adalah fondasi isolasi fail-closed (P0-02, ADR-0002): query TIDAK PERNAH
 * berjalan tanpa filter school_id. Akses lintas tenant yang sah hanya via
 * Model::withoutTenantScope() + policy + audit log.
 */
final class TenantContextMissing extends RuntimeException {}
