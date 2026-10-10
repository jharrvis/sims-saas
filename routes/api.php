<?php

use App\Domains\Akademik\Http\Controllers\AkademikController;
use App\Domains\Akademik\Http\Controllers\RombelController;
use App\Domains\Identity\Http\Controllers\AuthController;
use App\Domains\Kepegawaian\Http\Controllers\EmployeeController;
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

        // Kepegawaian
        Route::get('guru', [EmployeeController::class, 'index'])->middleware('permission:guru.view');
        Route::post('guru', [EmployeeController::class, 'store'])->middleware('permission:guru.create');
        Route::get('guru/{employee}', [EmployeeController::class, 'show'])->middleware('permission:guru.view');
        Route::put('guru/{employee}', [EmployeeController::class, 'update'])->middleware('permission:guru.update');
        Route::patch('guru/{employee}', [EmployeeController::class, 'update'])->middleware('permission:guru.update');
        Route::delete('guru/{employee}', [EmployeeController::class, 'destroy'])->middleware('permission:guru.delete');

        // Kelas (rombel)
        Route::get('kelas', [RombelController::class, 'index'])->middleware('permission:kelas.view');
        Route::post('kelas', [RombelController::class, 'store'])->middleware('permission:kelas.create');
        Route::get('kelas/{rombel}', [RombelController::class, 'show'])->middleware('permission:kelas.view');
        Route::put('kelas/{rombel}', [RombelController::class, 'update'])->middleware('permission:kelas.update');
        Route::patch('kelas/{rombel}', [RombelController::class, 'update'])->middleware('permission:kelas.update');
        Route::delete('kelas/{rombel}', [RombelController::class, 'destroy'])->middleware('permission:kelas.delete');

        // Akademik: mapel
        Route::get('mapel', [AkademikController::class, 'subjectIndex'])->middleware('permission:jadwal.view');
        Route::post('mapel', [AkademikController::class, 'subjectStore'])->middleware('permission:jadwal.create');

        // Akademik: jadwal
        Route::get('jadwal', [AkademikController::class, 'scheduleIndex'])->middleware('permission:jadwal.view');
        Route::post('jadwal', [AkademikController::class, 'scheduleStore'])->middleware('permission:jadwal.create');
        Route::delete('jadwal/{schedule}', [AkademikController::class, 'scheduleDestroy'])->middleware('permission:jadwal.delete');

        // Akademik: absensi
        Route::get('absensi', [AkademikController::class, 'attendanceIndex'])->middleware('permission:absensi.view');
        Route::post('absensi', [AkademikController::class, 'attendanceStore'])->middleware('permission:absensi.create');
        Route::post('absensi/kunci', [AkademikController::class, 'attendanceLock'])->middleware('permission:absensi.kunci');
        Route::get('absensi/rekap', [AkademikController::class, 'attendanceSummary'])->middleware('permission:absensi.view');
    });
});
