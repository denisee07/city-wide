<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Gracefully handle stale CSRF tokens (the "Page Expired" 419 error) by
        // sending the user back to the page with a friendly message instead of
        // showing a cryptic error screen.
        $exceptions->renderable(function (HttpException $e, $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }
            return response()->view('errors.expired', [], 419);
        });

        $exceptions->renderable(function (Throwable $e) {
            if (! \App\Support\DbOutage::isConnectionFailure($e)) {
                return null; // not an outage — let Laravel handle it normally
            }

            // The database is unreachable. Report 503 rather than 500 so
            // browsers and uptime checks treat it as transient and stop
            // caching a hard failure, and render a page that explains what
            // is happening instead of leaking a stack trace.
            return response()->view('errors.unavailable', [
                'reason' => config('app.debug') ? $e->getMessage() : null,
            ], 503)->header('Retry-After', '30');
        });
    })->create();
