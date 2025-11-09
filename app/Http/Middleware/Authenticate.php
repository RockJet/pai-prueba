<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // This is the core logic.
        // If the request is NOT expecting JSON (i.e., a standard web request),
        // it redirects to the 'login' route.
        if (! $request->expectsJson()) {
            return route('login');
        }

        // If it IS an API request, it should return null, allowing the
        // exception handler to throw a 401 Unauthorized response (JSON).
        return null;
    }
}
