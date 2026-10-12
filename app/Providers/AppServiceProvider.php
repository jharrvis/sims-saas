<?php

namespace App\Providers;

use App\Domains\Identity\Contracts\WaSender;
use App\Domains\Identity\Services\FonnteWaSender;
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

        // Provider WA: LogWaSender untuk dev; Fonnte untuk produksi
        // (keputusan pemilik produk 12 Okt 2026).
        $this->app->bind(WaSender::class, function (): WaSender {
            return config('wa.driver') === 'fonnte'
                ? new FonnteWaSender
                : new LogWaSender;
        });

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
