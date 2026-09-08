<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
        $middleware->appendToGroup('web', [
            SetLocale::class,
            EnsureUserIsActive::class,
        ]);

        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();

            return match ($user?->role) {
                'super_admin' => route('admin.dashboard'),
                'admin' => route('admin.dashboard'),
                'department_head' => route('department-head.dashboard'),
                'staff' => route('staff.dashboard'),
                default => '/',
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
