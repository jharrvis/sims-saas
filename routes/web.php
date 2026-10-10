<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\SetupTwoFactor;
use App\Livewire\Auth\TenantSwitcher;
use App\Livewire\Auth\VerifyMfa;
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
});
