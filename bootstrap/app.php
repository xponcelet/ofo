<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Confiance envers tous les proxies
        $middleware->trustProxies(at: '*');

        //  Middleware ajoutés au groupe "web"
        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocale::class);
        $middleware->web(append: [
            \App\Http\Middleware\EncryptCookies::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        //  Enregistrement des alias de middleware
        $middleware->alias([
            // Authentification & sécurité
            'auth' => \App\Http\Middleware\Authenticate::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

            // Middleware custom admin
            'is_admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Gestion personnalisée des exceptions (si besoin plus tard)
    })
    ->create();
