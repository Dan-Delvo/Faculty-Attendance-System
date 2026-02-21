<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects routes that require an authenticated admin-guard user.
 * Applies to: super_admin, admin, hr_staff roles (guard_name = 'admin').
 */
class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('admin')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Please log in to access the admin area.']);
        }

        // Bind the admin guard as the default for this request lifecycle
        Auth::shouldUse('admin');

        return $next($request);
    }
}
