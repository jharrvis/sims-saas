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
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tamu yang membuka area PWA diarahkan ke login PWA, bukan login admin.
        Authenticate::redirectUsing(fn (Request $request) => $request->is('pwa', 'pwa/*')
            ? route('pwa.login')
            : route('login'));

        $middleware->append(ResolveTenant::class);
        $middleware->append(SetSpatieTeam::class);
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
