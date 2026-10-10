<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\SetupTwoFactor;
use App\Livewire\Auth\TenantSwitcher;
use App\Livewire\Auth\VerifyMfa;
use App\Livewire\Kesiswaan\StudentForm;
use App\Livewire\Kesiswaan\StudentIndex;
use App\Livewire\Kesiswaan\StudentShow;
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
});
