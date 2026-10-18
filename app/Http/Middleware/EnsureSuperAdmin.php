<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->guest(route('login'))->with('error', 'Session expired. Please log in as Super Admin.');
        }

        if (!$request->user()->isSuperAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access. Super Admin credentials required.');
        }

        return $next($request);
    }
}