<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Resolve the authenticated user from either the admin or web guard
        $user = User::findOrFail(
            Auth::guard('admin')->check() ? Auth::guard('admin')->user()->id : Auth::guard('web')->user()->id
        );

        return [
            ...parent::share($request),
            'auth' => [
                'user'  => $user,
                'roles' => $user ? $user->getRoleNames()->toArray() : [],
            ],
            'flash' => [
                'success' => session('success'),
                'error'   => session('error'),
            ],
        ];
    }
}
