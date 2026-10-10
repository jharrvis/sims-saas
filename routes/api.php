<?php

use App\Domains\Identity\Http\Controllers\AuthController;
use App\Domains\Kesiswaan\Http\Controllers\StudentController;
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

        // Kesiswaan — permission spesifik per aksi (Dok 05)
        Route::get('siswa', [StudentController::class, 'index'])->middleware('permission:siswa.view');
        Route::post('siswa', [StudentController::class, 'store'])->middleware('permission:siswa.create');
        Route::get('siswa/{student}', [StudentController::class, 'show'])->middleware('permission:siswa.view');
        Route::put('siswa/{student}', [StudentController::class, 'update'])->middleware('permission:siswa.update');
        Route::patch('siswa/{student}', [StudentController::class, 'update'])->middleware('permission:siswa.update');
        Route::delete('siswa/{student}', [StudentController::class, 'destroy'])->middleware('permission:siswa.delete');
    });
});
