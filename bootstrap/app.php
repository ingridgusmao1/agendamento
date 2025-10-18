<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// seus middlewares
use App\Http\Middleware\EnsureUserType;

// middlewares nativos de auth/guest (Laravel)
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aliases de middleware (equivalem ao antigo Kernel::$routeMiddleware)
        $middleware->alias([
            'auth'             => Authenticate::class,             // protege rotas
            'guest'            => RedirectIfAuthenticated::class,  // redireciona se já logado
            'ensure.usertype'  => EnsureUserType::class,           // seu middleware custom
        ]);

        // Exemplos (descomentando caso precise no futuro):
        // $middleware->appendToGroup('api', [
        //     \Illuminate\Routing\Middleware\SubstituteBindings::class,
        // ]);
        // $middleware->prependToGroup('api', [
        //     \Illuminate\Http\Middleware\HandleCors::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
