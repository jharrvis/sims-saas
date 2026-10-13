<?php

use App\Domains\Akademik\Services\DashboardService;
use App\Domains\Impor\Http\Controllers\ImportTemplateController;
use App\Domains\Kepatuhan\Http\Controllers\DsarDownloadController;
use App\Domains\Kesiswaan\Http\Controllers\StudentExportController;
use App\Domains\Pwa\Http\Controllers\PushController;
use App\Domains\Pwa\Http\Controllers\PwaController;
use App\Http\Controllers\CetakController;
use App\Http\Controllers\HelpController;
use App\Livewire\Akademik\AttendanceIndex;
use App\Livewire\Akademik\GradeComponentIndex;
use App\Livewire\Akademik\GradeImport;
use App\Livewire\Akademik\GradeInput;
use App\Livewire\Akademik\GradeLeger;
use App\Livewire\Akademik\PromoteStudents;
use App\Livewire\Akademik\RombelForm;
use App\Livewire\Akademik\RombelIndex;
use App\Livewire\Akademik\RombelShow;
use App\Livewire\Akademik\ScheduleForm;
use App\Livewire\Akademik\ScheduleIndex;
use App\Livewire\Akademik\SubjectForm;
use App\Livewire\Akademik\SubjectIndex;
use App\Livewire\Alumni\AlumniForm;
use App\Livewire\Alumni\AlumniIndex;
use App\Livewire\Alumni\AlumniShow;
use App\Livewire\Aset\AssetForm;
use App\Livewire\Aset\AssetIndex;
use App\Livewire\Aset\AssetShow;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\PilihPeran;
use App\Livewire\Auth\PlatformLogin;
use App\Livewire\Auth\SetupTwoFactor;
use App\Livewire\Auth\StepUpVerify;
use App\Livewire\Auth\TenantSwitcher;
use App\Livewire\Auth\VerifyMfa;
use App\Livewire\Bk\CounselingForm;
use App\Livewire\Bk\CounselingIndex;
use App\Livewire\Broadcast\BroadcastForm;
use App\Livewire\Broadcast\BroadcastIndex;
use App\Livewire\Broadcast\BroadcastShow;
use App\Livewire\Ekskul\AchievementIndex;
use App\Livewire\Ekskul\EkskulForm;
use App\Livewire\Ekskul\EkskulIndex;
use App\Livewire\Ekskul\EkskulShow;
use App\Livewire\ERapor\RaporIndex;
use App\Livewire\ERapor\RaporShow;
use App\Livewire\Kantin\Kasir;
use App\Livewire\Kantin\ProductIndex;
use App\Livewire\Kantin\TransactionIndex;
use App\Livewire\Kepegawaian\EmployeeForm;
use App\Livewire\Kepegawaian\EmployeeImport;
use App\Livewire\Kepegawaian\EmployeeIndex;
use App\Livewire\Kesiswaan\GuardianForm;
use App\Livewire\Kesiswaan\GuardianIndex;
use App\Livewire\Kesiswaan\GuardianShow;
use App\Livewire\Kesiswaan\StudentForm;
use App\Livewire\Kesiswaan\StudentImport;
use App\Livewire\Kesiswaan\StudentIndex;
use App\Livewire\Kesiswaan\StudentShow;
use App\Livewire\Keuangan\InvoiceIndex;
use App\Livewire\Keuangan\InvoiceShow;
use App\Livewire\Langganan\Index as LanggananIndex;
use App\Livewire\Laporan\ReportIndex;
use App\Livewire\Lms\AssignmentForm;
use App\Livewire\Lms\AssignmentIndex;
use App\Livewire\Lms\AssignmentShow;
use App\Livewire\Onboarding\DaftarSekolah;
use App\Livewire\Organisasi\OrganizationForm;
use App\Livewire\Organisasi\OrganizationIndex;
use App\Livewire\Organisasi\OrganizationShow;
use App\Livewire\Pengaturan\AcademicYearIndex;
use App\Livewire\Pengaturan\DsarIndex;
use App\Livewire\Pengaturan\HolidayIndex;
use App\Livewire\Pengaturan\ImportHistory;
use App\Livewire\Pengaturan\ModuleIndex;
use App\Livewire\Pengaturan\PasswordSetup;
use App\Livewire\Pengaturan\SchoolProfile;
use App\Livewire\Pengaturan\WebhookIndex;
use App\Livewire\Pengumuman\AnnouncementForm;
use App\Livewire\Pengumuman\AnnouncementIndex;
use App\Livewire\Perpus\BookForm;
use App\Livewire\Perpus\BookIndex;
use App\Livewire\Perpus\LoanIndex;
use App\Livewire\Platform\AuditIndex as PlatformAudit;
use App\Livewire\Platform\Dashboard as PlatformDashboard;
use App\Livewire\Platform\InvoiceIndex as PlatformInvoices;
use App\Livewire\Platform\ModuleIndex as PlatformModules;
use App\Livewire\Platform\PlanIndex as PlatformPlans;
use App\Livewire\Platform\SubscriptionIndex as PlatformSubscriptions;
use App\Livewire\Platform\TenantCreate as PlatformTenantCreate;
use App\Livewire\Platform\TenantIndex as PlatformTenants;
use App\Livewire\Platform\TenantShow as PlatformTenantShow;
use App\Livewire\Ppdb\PpdbApplicantForm;
use App\Livewire\Ppdb\PpdbApplicantIndex;
use App\Livewire\Ppdb\PpdbApplicantShow;
use App\Livewire\Ppdb\PpdbPeriodIndex;
use App\Livewire\Pwa\Absensi as PwaAbsensi;
use App\Livewire\Pwa\Home as PwaHome;
use App\Livewire\Pwa\Jadwal as PwaJadwal;
use App\Livewire\Pwa\Login as PwaLogin;
use App\Livewire\Pwa\Nilai as PwaNilai;
use App\Livewire\Pwa\Pengumuman as PwaPengumuman;
use App\Livewire\Pwa\PengumumanShow as PwaPengumumanShow;
use App\Livewire\Pwa\Profil as PwaProfil;
use App\Livewire\Pwa\Tagihan as PwaTagihan;
use App\Livewire\Pwa\Unlinked as PwaUnlinked;
use App\Livewire\Qr\QrCardIndex;
use App\Livewire\Qr\QrChallenge;
use App\Livewire\Survei\SurveyFill;
use App\Livewire\Survei\SurveyForm;
use App\Livewire\Survei\SurveyIndex;
use App\Livewire\Survei\SurveyResult;
use App\Livewire\Tatib\ViolationForm;
use App\Livewire\Tatib\ViolationIndex;
use App\Livewire\Tatib\ViolationTypeIndex;
use App\Livewire\Transportasi\RouteForm;
use App\Livewire\Transportasi\RouteIndex;
use App\Livewire\Transportasi\RouteShow;
use App\Livewire\Uks\HealthVisitForm;
use App\Livewire\Uks\HealthVisitIndex;
use App\Livewire\Uks\MedicineIndex;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function (DashboardService $dash) {
    $user = auth()->user();
    // Operator platform murni (tanpa membership sekolah) → panel platform.
    // Yang punya membership tetap bisa membuka dashboard sekolahnya.
    if ($user?->is_platform && $user->activeMemberships()->doesntExist()) {
        return redirect()->route('platform.dashboard');
    }

    return view('dashboard', ['dash' => $dash->data($user)]);
})->name('dashboard')->middleware(['auth', 'mfa']);

// Logout domain sekolah.
Route::post('/keluar', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout')->middleware('auth');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', Login::class)->name('login');
    Route::get('/daftar', DaftarSekolah::class)->name('daftar')->middleware('throttle:10,1');
    // D-1: pintu khusus operator platform.
    Route::get('/platform/login', PlatformLogin::class)->name('platform.login');
});

// Verifikasi MFA — dapat diakses pra-login (sesi mfa_pending_user_id)
// maupun oleh user terautentikasi yang sesinya belum terverifikasi
// (mis. login ulang via cookie remember-me). Otorisasi ditangani komponen.
Route::get('/mfa/verify', VerifyMfa::class)->name('mfa.verify');

// Step-up authentication (D-1): verifikasi ulang sebelum masuk platform
// dari konteks sekolah.
Route::get('/stepup/verify', StepUpVerify::class)->name('stepup.verify')->middleware(['auth']);

Route::middleware(['auth', 'mfa'])->group(function (): void {
    Route::get('/2fa/setup', SetupTwoFactor::class)->name('2fa.setup');
    Route::get('/pilih-sekolah', TenantSwitcher::class)->name('tenant.switch');
    Route::get('/pilih-peran', PilihPeran::class)->name('pilih-peran');

    // Langganan sekolah — paket & tagihan milik tenant sendiri
    Route::get('/langganan', LanggananIndex::class)->name('langganan')->middleware('permission:langganan.view');

    // Kesiswaan — modul inti (selalu aktif)
    Route::prefix('siswa')->name('siswa.')->group(function (): void {
        Route::get('/', StudentIndex::class)->name('index')->middleware('permission:siswa.view');
        Route::get('/impor', StudentImport::class)->name('impor')->middleware('permission:siswa.import');
        Route::get('/impor/template', [ImportTemplateController::class, 'siswa'])->name('impor.template')->middleware('permission:siswa.import');
        Route::get('/ekspor/excel', [StudentExportController::class, 'excel'])->name('ekspor.excel')->middleware('permission:siswa.view');
        Route::get('/ekspor/pdf', [StudentExportController::class, 'pdf'])->name('ekspor.pdf')->middleware('permission:siswa.view');
        Route::get('/kartu/rombel', [CetakController::class, 'kartuPelajarRombel'])->name('kartu.rombel')->middleware('permission:siswa.view');
        Route::get('/{student}/kartu', [CetakController::class, 'kartuPelajar'])->name('kartu')->middleware('permission:siswa.view');
        Route::get('/tambah', StudentForm::class)->name('create')->middleware('permission:siswa.create');
        Route::get('/{student}/ubah', StudentForm::class)->name('edit')->middleware('permission:siswa.update');
        Route::get('/{student}', StudentShow::class)->name('show')->middleware('permission:siswa.view');
    });

    // Wali murid — modul inti (selalu aktif)
    Route::prefix('wali')->name('wali.')->group(function (): void {
        Route::get('/', GuardianIndex::class)->name('index')->middleware('permission:wali-murid.view');
        Route::get('/tambah', GuardianForm::class)->name('create')->middleware('permission:wali-murid.create');
        Route::get('/{guardian}/ubah', GuardianForm::class)->name('edit')->middleware('permission:wali-murid.update');
        Route::get('/{guardian}', GuardianShow::class)->name('show')->middleware('permission:wali-murid.view');
    });

    // QR Login siswa (Dok 09 S3.3) — kartu cetak + tantangan layar kelas
    Route::prefix('qr')->name('qr.')->group(function (): void {
        Route::get('/kartu', QrCardIndex::class)->name('kartu')->middleware('permission:qr.view');
        Route::get('/kartu/cetak', [PwaController::class, 'qrCetak'])->name('cetak')->middleware('permission:qr.view');
        Route::get('/tantangan', QrChallenge::class)->name('tantangan')->middleware('permission:qr.manage');
    });

    // Kepegawaian
    Route::prefix('guru')->name('guru.')->group(function (): void {
        Route::get('/', EmployeeIndex::class)->name('index')->middleware('permission:guru.view');
        Route::get('/impor', EmployeeImport::class)->name('impor')->middleware('permission:guru.import');
        Route::get('/impor/template', [ImportTemplateController::class, 'guru'])->name('impor.template')->middleware('permission:guru.import');
        Route::get('/tambah', EmployeeForm::class)->name('create')->middleware('permission:guru.create');
        Route::get('/{employee}/ubah', EmployeeForm::class)->name('edit')->middleware('permission:guru.update');
    });

    // Kelas (rombel)
    Route::prefix('kelas')->name('kelas.')->group(function (): void {
        Route::get('/', RombelIndex::class)->name('index')->middleware('permission:kelas.view');
        Route::get('/kenaikan', PromoteStudents::class)->name('kenaikan')->middleware('permission:kelas.kelola');
        Route::get('/tambah', RombelForm::class)->name('create')->middleware('permission:kelas.create');
        Route::get('/{rombel}/ubah', RombelForm::class)->name('edit')->middleware('permission:kelas.update');
        Route::get('/{rombel}', RombelShow::class)->name('show')->middleware('permission:kelas.view');
    });

    // Akademik
    Route::prefix('mapel')->name('mapel.')->group(function (): void {
        Route::get('/', SubjectIndex::class)->name('index')->middleware('permission:jadwal.view');
        Route::get('/tambah', SubjectForm::class)->name('create')->middleware('permission:jadwal.create');
        Route::get('/{subject}/ubah', SubjectForm::class)->name('edit')->middleware('permission:jadwal.update');
    });

    Route::prefix('jadwal')->name('jadwal.')->group(function (): void {
        Route::get('/', ScheduleIndex::class)->name('index')->middleware('permission:jadwal.view');
        Route::get('/tambah', ScheduleForm::class)->name('create')->middleware('permission:jadwal.create');
        Route::get('/cetak', [CetakController::class, 'jadwal'])->name('cetak')->middleware('permission:jadwal.view');
    });

    Route::get('/absensi', AttendanceIndex::class)->name('absensi.index')->middleware('permission:absensi.view');

    Route::prefix('nilai')->name('nilai.')->group(function (): void {
        Route::get('/input', GradeInput::class)->name('input')->middleware('permission:nilai.input');
        Route::get('/impor', GradeImport::class)->name('impor')->middleware('permission:nilai.import');
        Route::get('/impor/template', [ImportTemplateController::class, 'nilai'])->name('impor.template')->middleware('permission:nilai.import');
        Route::get('/leger', GradeLeger::class)->name('leger')->middleware('permission:nilai.rekap');
        Route::get('/leger/cetak', [CetakController::class, 'legerNilai'])->name('leger.cetak')->middleware('permission:nilai.rekap');
        Route::get('/komponen', GradeComponentIndex::class)->name('komponen')->middleware('permission:nilai.komponen');
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
        Route::get('/kalender', HolidayIndex::class)->name('kalender')->middleware('permission:kalender.manage');
        Route::get('/impor', ImportHistory::class)->name('impor')->middleware('permission:impor.riwayat');
        Route::get('/modul', ModuleIndex::class)->name('modul')->middleware('permission:sekolah.view');
        Route::get('/dsar', DsarIndex::class)->name('dsar')->middleware('permission:dsar.view');
        Route::get('/dsar/{dsarCase}/unduh', DsarDownloadController::class)->name('dsar.unduh')->middleware('permission:dsar.manage');
        Route::get('/webhook', WebhookIndex::class)->name('webhook')->middleware('permission:webhook.manage');
        Route::get('/keamanan', PasswordSetup::class)->name('keamanan');
    });

    // E-Rapor — modul add-on (feature flag)
    Route::prefix('erapor')->name('erapor.')->middleware('module:e_rapor')->group(function (): void {
        Route::get('/', RaporIndex::class)->name('index')->middleware('permission:erapor.view');
        Route::get('/siswa/{student}/semester/{semester}', RaporShow::class)->name('show')->middleware('permission:erapor.view');
        Route::get('/siswa/{student}/semester/{semester}/cetak', [CetakController::class, 'rapor'])->name('cetak')->middleware('permission:erapor.cetak');
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

    // Ekstrakurikuler & Prestasi — modul add-on (feature flag)
    Route::prefix('ekskul')->name('ekskul.')->middleware('module:ekstrakurikuler')->group(function (): void {
        Route::get('/', EkskulIndex::class)->name('index')->middleware('permission:ekskul.view');
        Route::get('/tambah', EkskulForm::class)->name('create')->middleware('permission:ekskul.create');
        Route::get('/prestasi', AchievementIndex::class)->name('prestasi')->middleware('permission:prestasi.view');
        Route::get('/{ekskul}/ubah', EkskulForm::class)->name('edit')->middleware('permission:ekskul.update');
        Route::get('/{ekskul}', EkskulShow::class)->name('show')->middleware('permission:ekskul.view');
    });

    // LMS Mini — modul add-on (feature flag)
    Route::prefix('lms')->name('lms.')->middleware('module:lms_mini')->group(function (): void {
        Route::get('/', AssignmentIndex::class)->name('index')->middleware('permission:lms.view');
        Route::get('/buat', AssignmentForm::class)->name('create')->middleware('permission:lms.tugas.create');
        Route::get('/{assignment}/ubah', AssignmentForm::class)->name('edit')->middleware('permission:lms.tugas.update');
        Route::get('/{assignment}', AssignmentShow::class)->name('show')->middleware('permission:lms.view');
    });

    // Broadcast — modul add-on (feature flag)
    Route::prefix('broadcast')->name('broadcast.')->middleware('module:broadcast')->group(function (): void {
        Route::get('/', BroadcastIndex::class)->name('index')->middleware('permission:broadcast.view');
        Route::get('/buat', BroadcastForm::class)->name('create')->middleware('permission:broadcast.create');
        Route::get('/{broadcast}/ubah', BroadcastForm::class)->name('edit')->middleware('permission:broadcast.update');
        Route::get('/{broadcast}', BroadcastShow::class)->name('show')->middleware('permission:broadcast.view');
    });

    // Inventaris & Aset — modul add-on (feature flag)
    Route::prefix('aset')->name('aset.')->middleware('module:inventaris')->group(function (): void {
        Route::get('/', AssetIndex::class)->name('index')->middleware('permission:aset.view');
        Route::get('/tambah', AssetForm::class)->name('create')->middleware('permission:aset.create');
        Route::get('/{asset}/ubah', AssetForm::class)->name('edit')->middleware('permission:aset.update');
        Route::get('/{asset}', AssetShow::class)->name('show')->middleware('permission:aset.view');
    });

    // UKS & Kesehatan — modul add-on (feature flag)
    Route::prefix('uks')->name('uks.')->middleware('module:uks')->group(function (): void {
        Route::get('/', HealthVisitIndex::class)->name('index')->middleware('permission:uks.view');
        Route::get('/catat', HealthVisitForm::class)->name('create')->middleware('permission:uks.create');
        Route::get('/obat', MedicineIndex::class)->name('obat')->middleware('permission:uks.obat');
        Route::get('/{visit}/ubah', HealthVisitForm::class)->name('edit')->middleware('permission:uks.update');
    });

    // Antar-Jemput — modul add-on (feature flag)
    Route::prefix('transportasi')->name('transportasi.')->middleware('module:transportasi')->group(function (): void {
        Route::get('/', RouteIndex::class)->name('index')->middleware('permission:transportasi.view');
        Route::get('/tambah', RouteForm::class)->name('create')->middleware('permission:transportasi.create');
        Route::get('/{route}/ubah', RouteForm::class)->name('edit')->middleware('permission:transportasi.update');
        Route::get('/{route}', RouteShow::class)->name('show')->middleware('permission:transportasi.view');
    });

    // Kantin Digital — modul add-on (feature flag)
    Route::prefix('kantin')->name('kantin.')->middleware('module:kantin')->group(function (): void {
        Route::get('/', ProductIndex::class)->name('index')->middleware('permission:kantin.view');
        Route::get('/kasir', Kasir::class)->name('kasir')->middleware('permission:kantin.kasir');
        Route::get('/transaksi', TransactionIndex::class)->name('transaksi')->middleware('permission:kantin.view');
    });

    // Survei & Evaluasi — modul add-on (feature flag)
    Route::prefix('survei')->name('survei.')->middleware('module:survei')->group(function (): void {
        Route::get('/', SurveyIndex::class)->name('index')->middleware('permission:survei.view');
        Route::get('/buat', SurveyForm::class)->name('create')->middleware('permission:survei.create');
        Route::get('/{survey}/isi', SurveyFill::class)->name('isi')->middleware('permission:survei.isi');
        Route::get('/{survey}/hasil', SurveyResult::class)->name('hasil')->middleware('permission:survei.hasil');
        Route::get('/{survey}/ubah', SurveyForm::class)->name('edit')->middleware('permission:survei.update');
    });

    // Alumni & Karier — modul add-on (feature flag)
    Route::prefix('alumni')->name('alumni.')->middleware('module:alumni')->group(function (): void {
        Route::get('/', AlumniIndex::class)->name('index')->middleware('permission:alumni.view');
        Route::get('/tambah', AlumniForm::class)->name('create')->middleware('permission:alumni.create');
        Route::get('/{alumni}/ubah', AlumniForm::class)->name('edit')->middleware('permission:alumni.update');
        Route::get('/{alumni}', AlumniShow::class)->name('show')->middleware('permission:alumni.view');
    });

    // Organisasi — modul add-on (feature flag)
    Route::prefix('organisasi')->name('organisasi.')->middleware('module:organisasi')->group(function (): void {
        Route::get('/', OrganizationIndex::class)->name('index')->middleware('permission:organisasi.view');
        Route::get('/tambah', OrganizationForm::class)->name('create')->middleware('permission:organisasi.create');
        Route::get('/{organization}/ubah', OrganizationForm::class)->name('edit')->middleware('permission:organisasi.update');
        Route::get('/{organization}', OrganizationShow::class)->name('show')->middleware('permission:organisasi.view');
    });
});

// Panel operator platform (SaaS) — tanpa tenant, khusus is_platform
Route::prefix('platform')->name('platform.')->middleware(['auth', 'mfa', 'platform', 'stepup'])->group(function (): void {
    Route::get('/', PlatformDashboard::class)->name('dashboard')->middleware('platform:platform.metrics.view');
    Route::get('/tenants', PlatformTenants::class)->name('tenants')->middleware('platform:platform.tenant.view');
    Route::get('/tenants/baru', PlatformTenantCreate::class)->name('tenants.create')->middleware('platform:platform.tenant.create');
    Route::get('/tenants/{schoolId}', PlatformTenantShow::class)->name('tenants.show')->middleware('platform:platform.tenant.view');
    Route::get('/paket', PlatformPlans::class)->name('plans')->middleware('platform:platform.plan.view');
    Route::get('/modul', PlatformModules::class)->name('modules')->middleware('platform:platform.module.view');
    Route::get('/langganan', PlatformSubscriptions::class)->name('subscriptions')->middleware('platform:platform.tenant.view');
    Route::get('/tagihan', PlatformInvoices::class)->name('invoices')->middleware('platform:platform.tenant.view');
    Route::get('/audit', PlatformAudit::class)->name('audit')->middleware('platform:platform.audit.view');
    Route::post('/keluar', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});

// Berhenti impersonate — di luar middleware `platform` karena user aktif
// saat ini adalah admin sekolah yang di-impersonate (bukan operator).
Route::post('/platform/impersonate/stop', function () {
    $impersonator = session('impersonator_id') ? User::find(session('impersonator_id')) : null;
    abort_unless($impersonator?->is_platform, 403, 'Bukan sesi impersonate.');

    $schoolId = session('impersonate_school_id');

    AuditLog::create([
        'school_id' => $schoolId,
        'user_id' => $impersonator->id,
        'aksi' => 'impersonate.stop',
        'model_type' => User::class,
        'model_id' => (string) auth()->id(),
        'ip_address' => request()->ip(),
    ]);

    auth()->login($impersonator);
    session()->forget(['impersonator_id', 'impersonate_school_id', 'impersonate_expires_at', 'active_school_id']);

    return redirect()->route('platform.tenants.show', $schoolId);
})->middleware(['auth'])->name('platform.impersonate.stop');

// PWA Siswa & Orang Tua — mobile-first, installable (Dok 07: P-01 s.d. P-06)
Route::prefix('pwa')->name('pwa.')->group(function (): void {
    Route::get('/offline', [PwaController::class, 'offline'])->name('offline');

    Route::middleware('guest')->group(function (): void {
        Route::get('/login', PwaLogin::class)->name('login');
        Route::post('/qr/redeem', [PwaController::class, 'qrRedeem'])->name('qr.redeem')->middleware('throttle:20,1');
    });

    Route::middleware(['auth', 'mfa', 'pwa.context', 'qr.limit'])->group(function (): void {
        Route::get('/belum-terhubung', PwaUnlinked::class)->name('unlinked');
        Route::get('/', PwaHome::class)->name('home');
        Route::get('/nilai', PwaNilai::class)->name('nilai');
        Route::get('/jadwal', PwaJadwal::class)->name('jadwal');
        Route::get('/absensi', PwaAbsensi::class)->name('absensi');
        Route::get('/pengumuman', PwaPengumuman::class)->name('pengumuman');
        Route::get('/pengumuman/{announcement}', PwaPengumumanShow::class)->name('pengumuman.show');
        Route::get('/tagihan', PwaTagihan::class)->name('tagihan');
        Route::get('/profil', PwaProfil::class)->name('profil');
        Route::post('/ganti-anak', [PwaController::class, 'switchChild'])->name('switch-child');
        Route::post('/keluar', [PwaController::class, 'logout'])->name('logout');
        Route::get('/push/vapid-key', [PushController::class, 'vapidKey'])->name('push.vapid-key');
        Route::post('/push/subscribe', [PushController::class, 'subscribe'])->name('push.subscribe');
        Route::delete('/push/unsubscribe', [PushController::class, 'unsubscribe'])->name('push.unsubscribe');
    });
});
