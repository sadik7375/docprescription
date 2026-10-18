<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            \App\Http\Middleware\CheckIfInstalled::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function ($response, Throwable $e, Request $request) {
            $statusCode = $response->getStatusCode();

            // 1. Session Expired (419 Token Mismatch) or Unauthenticated (401)
            if (in_array($statusCode, [419, 401])) {
                if ($request->header('X-Inertia')) {
                    return Inertia::location(route('login'));
                }
                return redirect()->guest(route('login'))->with('error', 'Your session has expired. Please log in again.');
            }

            // 2. Forbidden / Permission Denied (403)
            if ($statusCode === 403) {
                // Not logged in at all -> redirect to login
                if (!$request->user()) {
                    if ($request->header('X-Inertia')) {
                        return Inertia::location(route('login'));
                    }
                    return redirect()->guest(route('login'))->with('error', 'Please log in to continue.');
                }

                $user = $request->user();
                $message = $e->getMessage() ?: 'You do not have permission to access that area.';

                // If patient attempts doctor/staff/admin route -> redirect to Patient Portal
                if ($user->isPatient()) {
                    return redirect()->route('dashboard')->with('error', 'Patients do not have access to clinical administration tools.');
                }

                // If doctor or staff without permission -> redirect to dashboard with flash error
                if ($user->isDoctor() || $user->isStaff()) {
                    return redirect()->route('dashboard')->with('error', $message);
                }

                // Super admin redirect
                if ($user->isSuperAdmin()) {
                    return redirect()->route('admin.dashboard')->with('error', $message);
                }
            }

            return $response;
        });
    })->create();