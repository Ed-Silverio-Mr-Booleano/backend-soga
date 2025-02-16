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
        /** middleware yang akan mengarahkan user yang belum terautentikasi */
        $middleware->redirectGuestsTo(fn() => route('guest'));
        $middleware->validateCsrfTokens(except: [
        'api/*',
        'http://localhost:3000/*',
        'http://*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

    /*$middleware->validateCsrfTokens(except: [
        'stripe/*',
        'http://example.com/foo/bar',
        'http://example.com/foo/*',
    ]); */
