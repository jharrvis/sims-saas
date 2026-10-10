<?php

declare(strict_types=1);

namespace App\Domains\Identity\Services;

use RuntimeException;

/**
 * Dilempar saat permintaan OTP melebihi rate limit (Dok 09 §3.1).
 */
final class OtpRateLimited extends RuntimeException {}
