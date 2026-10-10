<?php

use App\Domains\Identity\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Publik (rate-limited)
    Route::post('auth/otp/request', [AuthController::class, 'requestOtp'])
        ->middleware('throttle:10,1');
    Route::post('auth/otp/verify', [AuthController::class, 'verifyOtp'])
        ->middleware('throttle:10,1');
    Route::post('auth/mfa/verify', [AuthController::class, 'verifyMfa'])
        ->middleware('throttle:10,1');

    // Terotentikasi
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/tenant/switch', [AuthController::class, 'switchTenant']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
    });
});
