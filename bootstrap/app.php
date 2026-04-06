<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // ── Guard-aware authentication ──────────────────────────────────────
        // auth.admin  → checks the 'admin' guard (super_admin / admin / hr_staff)
        // auth.faculty → checks the 'web' guard (faculty)
        $middleware->alias([
            'auth.admin'         => \App\Http\Middleware\EnsureAdminAuthenticated::class,
            'auth.faculty'       => \App\Http\Middleware\EnsureFacultyAuthenticated::class,
            'check.role'         => \App\Http\Middleware\CheckRole::class,
            'check.permission'   => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
