<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Session\TokenMismatchException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        $middleware->group('admin', [
            'auth',
            'role:admin',
        ]);

        $middleware->group('approver', [
            'auth',
            'role:approver',
        ]);

        $middleware->group('employee', [
            'auth',
            'role:employee',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TokenMismatchException $e, $request) {
            // If request came from Inertia
            if ($request->header('X-Inertia')) {
                return Inertia::render('Unauthorized', [
                    'expired' => true,
                ])->toResponse($request)->setStatusCode(419);
            }

            // Fallback: redirect to login
            return redirect()->route('login')->with('message', 'Your session expired. Please log in again.');
        });
    })->create();
