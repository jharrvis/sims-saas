<?php

use App\Domains\Akademik\Http\Controllers\AkademikController;
use App\Domains\Akademik\Http\Controllers\GradeController;
use App\Domains\Akademik\Http\Controllers\RombelController;
use App\Domains\Identity\Http\Controllers\AuthController;
use App\Domains\Kepegawaian\Http\Controllers\EmployeeController;
use App\Domains\Kesiswaan\Http\Controllers\StudentController;
use App\Domains\Keuangan\Http\Controllers\KeuanganController;
use App\Domains\Ppdb\Http\Controllers\PpdbController;
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

        // Akademik: nilai
        Route::get('nilai/komponen', [GradeController::class, 'componentIndex'])->middleware('permission:nilai.komponen');
        Route::post('nilai/komponen', [GradeController::class, 'componentStore'])->middleware('permission:nilai.komponen');
        Route::post('nilai', [GradeController::class, 'store'])->middleware('permission:nilai.input');
        Route::get('nilai/leger', [GradeController::class, 'leger'])->middleware('permission:nilai.rekap');
        Route::get('nilai/rapor', [GradeController::class, 'rapor'])->middleware('permission:nilai.view');
        Route::post('nilai/rilis', [GradeController::class, 'release'])->middleware('permission:nilai.release');
        Route::get('nilai/siswa', [GradeController::class, 'forStudent'])->middleware('permission:nilai.view');

        // PPDB — modul add-on
        Route::prefix('ppdb')->middleware('module:ppdb')->group(function (): void {
            Route::get('periode', [PpdbController::class, 'periodIndex'])->middleware('permission:ppdb.periode.view');
            Route::post('periode', [PpdbController::class, 'periodStore'])->middleware('permission:ppdb.periode.create');
            Route::get('pendaftar', [PpdbController::class, 'applicantIndex'])->middleware('permission:ppdb.pendaftar.view');
            Route::post('pendaftar', [PpdbController::class, 'applicantStore'])->middleware('permission:ppdb.pendaftar.create');
            Route::get('pendaftar/{applicant}', [PpdbController::class, 'applicantShow'])->middleware('permission:ppdb.pendaftar.view');
            Route::post('pendaftar/{applicant}/status', [PpdbController::class, 'applicantStatus'])->middleware('permission:ppdb.pendaftar.verify|ppdb.pendaftar.approve');
            Route::post('pendaftar/{applicant}/seleksi', [PpdbController::class, 'applicantSelect'])->middleware('permission:ppdb.seleksi.input');
            Route::post('pendaftar/{applicant}/daftar-ulang', [PpdbController::class, 'applicantEnroll'])->middleware('permission:ppdb.pendaftar.enroll');
        });

        // Keuangan — modul add-on
        Route::prefix('keuangan')->middleware('module:keuangan')->group(function (): void {
            Route::get('jenis-tagihan', [KeuanganController::class, 'feeTypeIndex'])->middleware('permission:keuangan.tagihan.view');
            Route::post('jenis-tagihan', [KeuanganController::class, 'feeTypeStore'])->middleware('permission:keuangan.tagihan.create');
            Route::get('tagihan', [KeuanganController::class, 'invoiceIndex'])->middleware('permission:keuangan.tagihan.view');
            Route::post('tagihan/generate', [KeuanganController::class, 'invoiceGenerate'])->middleware('permission:keuangan.tagihan.generate');
            Route::get('tagihan/{invoice}', [KeuanganController::class, 'invoiceShow'])->middleware('permission:keuangan.tagihan.view');
            Route::post('tagihan/{invoice}/bayar', [KeuanganController::class, 'paymentStore'])->middleware('permission:keuangan.bayar.create');
            Route::get('laporan/ringkasan', [KeuanganController::class, 'summary'])->middleware('permission:keuangan.laporan.view');
            Route::get('laporan/tunggakan', [KeuanganController::class, 'arrears'])->middleware('permission:keuangan.laporan.view');
        });
    });
});
