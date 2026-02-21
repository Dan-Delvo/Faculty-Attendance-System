<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects routes that require an authenticated web-guard user.
 * Applies to: faculty role (guard_name = 'web').
 */
class EnsureFacultyAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('web')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('faculty.login')
                ->withErrors(['email' => 'Please log in to access the faculty area.']);
        }

        // Ensure the web guard is the default for this request lifecycle
        Auth::shouldUse('web');

        return $next($request);
    }
}
