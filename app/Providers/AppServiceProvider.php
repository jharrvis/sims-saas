<?php

namespace App\Providers;

use App\Domains\Identity\Contracts\WaSender;
use App\Domains\Identity\Services\LogWaSender;
use App\Domains\Pwa\Services\PwaContext;
use App\Tenancy\TenantContext;
use Illuminate\Support\ServiceProvider;
use PragmaRX\Google2FA\Google2FA;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
        $this->app->singleton(PwaContext::class);

        // Provider WA: LogWaSender untuk dev; Meta Cloud API untuk produksi (Dok 11)
        $this->app->bind(WaSender::class, LogWaSender::class);

        $this->app->singleton(Google2FA::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
