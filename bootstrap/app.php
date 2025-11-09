<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
             \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class
        ]);

        // 3. Middlewares Asignables (auth, guest, etc.)
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            // Aquí puedes añadir tus propios middlewares de alias, p. ej.:
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
