<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isInstalled = file_exists(storage_path('installed'));

        // If not installed and trying to access any route other than installer routes and static assets
        if (!$isInstalled) {
            config(['session.driver' => 'file']);

            if (empty(config('app.key'))) {
                config(['app.key' => 'base64:u3G8p2aZ4KzJ7tYwX1vB6mC9nF5eH8sQ3dE6wR8tY0U=']);
            }

            if (!$request->is('install*') && !$request->is('build*') && !$request->is('up') && !$request->is('favicon.ico')) {
                return redirect()->route('installer.welcome');
            }
        }

        // If already installed and trying to access installer routes, redirect to login
        if ($isInstalled && $request->is('install*')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}