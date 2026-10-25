<?php

use App\Http\Middleware\EnsureTenantResolved;
use App\Http\Middleware\PwaContextMiddleware;
use App\Http\Middleware\QrSessionLimit;
use App\Http\Middleware\RequireMfa;
use App\Http\Middleware\RequireModule;
use App\Http\Middleware\RequirePlatform;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\SetSpatieTeam;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Di balik Cloudflare (TLS terminasi di edge): percayai header
        // X-Forwarded-* agar URL yang dibuat ber-scheme https (hindari
        // mixed content pada redirect Livewire dkk).
        $middleware->trustProxies(at: '*');

        // ResolveTenant & SetSpatieTeam HARUS di grup web (setelah session
        // dimulai) — bukan global. Sebagai global middleware, keduanya
        // berjalan sebelum session sehingga $request->user() selalu null
        // dan fallback membership tidak pernah bekerja di domain pusat.
        // Tetap berjalan SEBELUM route middleware `auth` (P0-02).
        $middleware->web(append: [
            ResolveTenant::class,
            SetSpatieTeam::class,
        ]);

        // Keanggotaan grup web saja tidak menentukan urutan eksekusi: ketika
        // keduanya tidak ada di middleware priority list, router menempatkannya
        // SETELAH SubstituteBindings. Akibatnya route model binding
        // (GET /guru/{employee}, /siswa/{id}, /kelas/{id}, dst.) melempar
        // TenantContextMissing sebelum tenant sempat diresolusi, sementara
        // route index tetap 200 karena tidak punya binding.
        // Dua baris berikut menempatkan keduanya tepat setelah StartSession
        // sehingga urutan efektifnya:
        // StartSession -> ResolveTenant -> SetSpatieTeam -> SubstituteBindings
        // ($request->user() sudah tersedia, dan `permission` tetap terakhir).
        $middleware->appendToPriorityList(
            StartSession::class,
            ResolveTenant::class,
        );

        $middleware->appendToPriorityList(
            ResolveTenant::class,
            SetSpatieTeam::class,
        );

        // API: resolusi tenant + team harus terjadi SEBELUM SubstituteBindings
        // agar route model binding ter-scope tenant yang benar.
        $middleware->api(prepend: [
            EnsureTenantResolved::class,
        ]);

        // Tamu yang membuka area PWA diarahkan ke login PWA, bukan login admin.
        Authenticate::redirectUsing(fn (Request $request) => $request->is('pwa', 'pwa/*')
            ? route('pwa.login')
            : route('login'));

        // (ResolveTenant & SetSpatieTeam dipindah ke grup web — lihat di atas.)
        $middleware->alias([
            'mfa' => RequireMfa::class,
            'platform' => RequirePlatform::class,
            'pwa.context' => PwaContextMiddleware::class,
            'qr.limit' => QrSessionLimit::class,
            'tenant.resolved' => EnsureTenantResolved::class,
            'module' => RequireModule::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

// Folder public terpisah dari base path (mis. deploy HestiaCP: app di
// private/, docroot di public_html/public) — diaktifkan via PUBLIC_PATH.
$publicPath = $_SERVER['PUBLIC_PATH'] ?? $_ENV['PUBLIC_PATH'] ?? null;

if (is_string($publicPath) && $publicPath !== '') {
    $app->usePublicPath($publicPath);
}

return $app;
