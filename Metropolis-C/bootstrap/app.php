<?php

use App\Http\Middleware\CityPlannerMiddleware;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsLibraryManager;
<<<<<<< HEAD
=======
use App\Http\Middleware\EnsureUserIsPolicyMaker;
>>>>>>> 9b1fb50cbd837e928587d63b45472150bab40073
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
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'library-manager' => EnsureUserIsLibraryManager::class,
            'city-planner' => CityPlannerMiddleware::class,
            'library-manager' => EnsureUserIsLibraryManager::class,
            'policy-maker' => EnsureUserIsPolicyMaker::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
