<?php

use App\Http\Controllers\HelpController;
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
use App\Livewire\Bk\CounselingForm;
use App\Livewire\Bk\CounselingIndex;
use App\Livewire\ERapor\RaporIndex;
use App\Livewire\ERapor\RaporShow;
use App\Livewire\Kepegawaian\EmployeeForm;
use App\Livewire\Kepegawaian\EmployeeIndex;
use App\Livewire\Kesiswaan\StudentForm;
use App\Livewire\Kesiswaan\StudentIndex;
use App\Livewire\Kesiswaan\StudentShow;
use App\Livewire\Keuangan\InvoiceIndex;
use App\Livewire\Keuangan\InvoiceShow;
use App\Livewire\Laporan\ReportIndex;
use App\Livewire\Pengaturan\AcademicYearIndex;
use App\Livewire\Pengaturan\ModuleIndex;
use App\Livewire\Pengaturan\SchoolProfile;
use App\Livewire\Pengumuman\AnnouncementForm;
use App\Livewire\Pengumuman\AnnouncementIndex;
use App\Livewire\Perpus\BookForm;
use App\Livewire\Perpus\BookIndex;
use App\Livewire\Perpus\LoanIndex;
use App\Livewire\Ppdb\PpdbApplicantForm;
use App\Livewire\Ppdb\PpdbApplicantIndex;
use App\Livewire\Ppdb\PpdbApplicantShow;
use App\Livewire\Ppdb\PpdbPeriodIndex;
use App\Livewire\Tatib\ViolationForm;
use App\Livewire\Tatib\ViolationIndex;
use App\Livewire\Tatib\ViolationTypeIndex;
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

    // Pusat Bantuan — dokumentasi end-user (selalu tersedia)
    Route::prefix('bantuan')->name('bantuan.')->group(function (): void {
        Route::get('/', [HelpController::class, 'index'])->name('index');
        Route::get('/{topik}', [HelpController::class, 'show'])->name('show');
    });

    // Pengumuman — modul add-on (feature flag)
    Route::prefix('pengumuman')->name('pengumuman.')->middleware('module:pengumuman')->group(function (): void {
        Route::get('/', AnnouncementIndex::class)->name('index')->middleware('permission:pengumuman.view');
        Route::get('/buat', AnnouncementForm::class)->name('create')->middleware('permission:pengumuman.create');
        Route::get('/{announcement}/ubah', AnnouncementForm::class)->name('edit')->middleware('permission:pengumuman.update');
    });

    // Laporan — modul inti
    Route::prefix('laporan')->name('laporan.')->group(function (): void {
        Route::get('/', ReportIndex::class)->name('index')->middleware('permission:laporan.view');
    });

    // Pengaturan
    Route::prefix('pengaturan')->name('pengaturan.')->group(function (): void {
        Route::get('/', SchoolProfile::class)->name('profil')->middleware('permission:sekolah.view');
        Route::get('/tahun-ajaran', AcademicYearIndex::class)->name('tahun-ajaran')->middleware('permission:tahun-ajaran.view');
        Route::get('/modul', ModuleIndex::class)->name('modul')->middleware('permission:sekolah.view');
    });

    // E-Rapor — modul add-on (feature flag)
    Route::prefix('erapor')->name('erapor.')->middleware('module:e_rapor')->group(function (): void {
        Route::get('/', RaporIndex::class)->name('index')->middleware('permission:erapor.view');
        Route::get('/siswa/{student}/semester/{semester}', RaporShow::class)->name('show')->middleware('permission:erapor.view');
    });

    // Tata Tertib — modul add-on (feature flag)
    Route::prefix('tatib')->name('tatib.')->middleware('module:tata_tertib')->group(function (): void {
        Route::get('/', ViolationIndex::class)->name('index')->middleware('permission:tatib.view');
        Route::get('/catat', ViolationForm::class)->name('create')->middleware('permission:tatib.create');
        Route::get('/jenis', ViolationTypeIndex::class)->name('jenis')->middleware('permission:tatib.view');
        Route::get('/{violation}/ubah', ViolationForm::class)->name('edit')->middleware('permission:tatib.update');
    });

    // BK & Konseling — modul add-on (feature flag)
    Route::prefix('bk')->name('bk.')->middleware('module:bk_konseling')->group(function (): void {
        Route::get('/', CounselingIndex::class)->name('index')->middleware('permission:bk.view');
        Route::get('/jadwalkan', CounselingForm::class)->name('create')->middleware('permission:bk.create');
        Route::get('/{session}/ubah', CounselingForm::class)->name('edit')->middleware('permission:bk.update');
    });

    // Perpustakaan — modul add-on (feature flag)
    Route::prefix('perpus')->name('perpus.')->middleware('module:perpustakaan')->group(function (): void {
        Route::get('/', BookIndex::class)->name('index')->middleware('permission:perpus.view');
        Route::get('/tambah', BookForm::class)->name('create')->middleware('permission:perpus.buku');
        Route::get('/pinjam', LoanIndex::class)->name('pinjam')->middleware('permission:perpus.pinjam');
        Route::get('/{book}/ubah', BookForm::class)->name('edit')->middleware('permission:perpus.buku');
    });
});
