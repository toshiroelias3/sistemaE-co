<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registramos nuestro alias de middleware aquí para Laravel 11
        $middleware->alias([
            'admin' => AdminMiddleware::class,
        ]);

        // Mercado Pago no puede enviar el token CSRF; el webhook valida su propia firma
        $middleware->validateCsrfTokens(except: ['webhooks/mercadopago']);

        // Cabeceras de seguridad en todas las respuestas
        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
