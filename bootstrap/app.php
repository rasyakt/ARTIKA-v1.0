<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // Exclude maintenance bypass cookie from encryption so the pre-boot
        // maintenance.php check in public/index.php can read it correctly.
        $middleware->encryptCookies(except: [
            'laravel_maintenance',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLanguage::class,
            \App\Http\Middleware\ShareRoutePrefix::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            '/logout',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'superadmin' => \App\Http\Middleware\SuperadminMiddleware::class,
            'feature' => \App\Http\Middleware\CheckFeature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
