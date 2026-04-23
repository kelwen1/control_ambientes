<?php

use Illuminate\Console\Scheduling\Schedule;
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
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'admin.or.coordinatorL' => \App\Http\Middleware\AdminOrCoordinatorLMiddleware::class,
            'catalog.access' => \App\Http\Middleware\CatalogOrReadOnlyCoordinatorMiddleware::class,
            'coordinator.viewonly' => \App\Http\Middleware\CoordinatorViewOnlyMiddleware::class,
            'instructor' => \App\Http\Middleware\InstructorOnlyMiddleware::class,
            'force.https' => \App\Http\Middleware\ForceHttps::class,
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Aplicar headers de seguridad a todas las rutas web
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('reservas:finalizar-vencidas')->dailyAt('01:00');
    })
    ->create();
