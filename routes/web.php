<?php

use App\Livewire\Akademik\AttendanceIndex;
use App\Livewire\Akademik\GradeInput;
use App\Livewire\Akademik\GradeLeger;
use App\Livewire\Akademik\RombelForm;
use App\Livewire\Akademik\RombelIndex;
use App\Livewire\Akademik\RombelShow;
use App\Livewire\Akademik\ScheduleForm;
use App\Livewire\Akademik\ScheduleIndex;
use App\Livewire\Akademik\SubjectIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\SetupTwoFactor;
use App\Livewire\Auth\TenantSwitcher;
use App\Livewire\Auth\VerifyMfa;
use App\Livewire\Kepegawaian\EmployeeForm;
use App\Livewire\Kepegawaian\EmployeeIndex;
use App\Livewire\Kesiswaan\StudentForm;
use App\Livewire\Kesiswaan\StudentIndex;
use App\Livewire\Kesiswaan\StudentShow;
use App\Livewire\Keuangan\InvoiceIndex;
use App\Livewire\Keuangan\InvoiceShow;
use App\Livewire\Ppdb\PpdbApplicantForm;
use App\Livewire\Ppdb\PpdbApplicantIndex;
use App\Livewire\Ppdb\PpdbApplicantShow;
use App\Livewire\Ppdb\PpdbPeriodIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', Login::class)->name('login');
    Route::get('/mfa/verify', VerifyMfa::class)->name('mfa.verify');
    Route::get('/pilih-sekolah', TenantSwitcher::class)->name('tenant.switch');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/2fa/setup', SetupTwoFactor::class)->name('2fa.setup');

    // Kesiswaan — modul inti (selalu aktif)
    Route::prefix('siswa')->name('siswa.')->group(function (): void {
        Route::get('/', StudentIndex::class)->name('index')->middleware('permission:siswa.view');
        Route::get('/tambah', StudentForm::class)->name('create')->middleware('permission:siswa.create');
        Route::get('/{student}/ubah', StudentForm::class)->name('edit')->middleware('permission:siswa.update');
        Route::get('/{student}', StudentShow::class)->name('show')->middleware('permission:siswa.view');
    });

    // Kepegawaian
    Route::prefix('guru')->name('guru.')->group(function (): void {
        Route::get('/', EmployeeIndex::class)->name('index')->middleware('permission:guru.view');
        Route::get('/tambah', EmployeeForm::class)->name('create')->middleware('permission:guru.create');
        Route::get('/{employee}/ubah', EmployeeForm::class)->name('edit')->middleware('permission:guru.update');
    });

    // Kelas (rombel)
    Route::prefix('kelas')->name('kelas.')->group(function (): void {
        Route::get('/', RombelIndex::class)->name('index')->middleware('permission:kelas.view');
        Route::get('/tambah', RombelForm::class)->name('create')->middleware('permission:kelas.create');
        Route::get('/{rombel}/ubah', RombelForm::class)->name('edit')->middleware('permission:kelas.update');
        Route::get('/{rombel}', RombelShow::class)->name('show')->middleware('permission:kelas.view');
    });

    // Akademik
    Route::get('/mapel', SubjectIndex::class)->name('mapel.index')->middleware('permission:jadwal.view');

    Route::prefix('jadwal')->name('jadwal.')->group(function (): void {
        Route::get('/', ScheduleIndex::class)->name('index')->middleware('permission:jadwal.view');
        Route::get('/tambah', ScheduleForm::class)->name('create')->middleware('permission:jadwal.create');
    });

    Route::get('/absensi', AttendanceIndex::class)->name('absensi.index')->middleware('permission:absensi.view');

    Route::prefix('nilai')->name('nilai.')->group(function (): void {
        Route::get('/input', GradeInput::class)->name('input')->middleware('permission:nilai.input');
        Route::get('/leger', GradeLeger::class)->name('leger')->middleware('permission:nilai.rekap');
    });

    // PPDB — modul add-on (feature flag)
    Route::prefix('ppdb')->name('ppdb.')->middleware('module:ppdb')->group(function (): void {
        Route::get('/', PpdbPeriodIndex::class)->name('index')->middleware('permission:ppdb.periode.view');
        Route::get('/pendaftar/{period}', PpdbApplicantIndex::class)->name('pendaftar.index')->middleware('permission:ppdb.pendaftar.view');
        Route::get('/pendaftar/{period}/tambah', PpdbApplicantForm::class)->name('pendaftar.create')->middleware('permission:ppdb.pendaftar.create');
        Route::get('/pendaftar/detail/{applicant}', PpdbApplicantShow::class)->name('pendaftar.show')->middleware('permission:ppdb.pendaftar.view');
    });

    // Keuangan — modul add-on (feature flag)
    Route::prefix('keuangan')->name('keuangan.')->middleware('module:keuangan')->group(function (): void {
        Route::get('/', InvoiceIndex::class)->name('index')->middleware('permission:keuangan.tagihan.view');
        Route::get('/{invoice}', InvoiceShow::class)->name('show')->middleware('permission:keuangan.tagihan.view');
    });
});
