<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserType;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',        // <<-- adiciona as rotas de API
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aliases de middleware (substitui o antigo Kernel::$routeMiddleware)
        $middleware->alias([
            'ensure.usertype' => EnsureUserType::class,
        ]);

        // Exemplos, se algum dia quiser mexer em grupos:
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
