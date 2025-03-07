<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Check if the user is trying to access a route they are already authenticated for
                if ($this->isTargetingCorrectPanel()) {
                    return $next($request);
                } else {
                    // Redirect based on permissions
                    return redirect($this->determineRedirect());
                }
            }
        }

        return $next($request);
    }

    /**
     * Check if the user is targeting the correct panel based on their permissions.
     *
     * @return bool
     */
    protected function isTargetingCorrectPanel(): bool
    {
        $user = Auth::user();
        $panel = request()->segment(1); // Assumes your admin panel is accessed via '/admin'

        if ($this->userCanManageResources($user) && $panel === 'admin') {
            return true;
        } elseif (! $this->userCanManageResources($user)) {
            return $panel !== 'admin';
        }

        return false;
    }

    /**
     * Determine where to redirect the user based on their permissions.
     *
     * @return string
     */
    protected function determineRedirect(): string
    {
        if ($this->userCanManageResources(Auth::user())) {
            return '/admin';
        } else {
            return '/';
        }
    }

    /**
     * Check if the user has permissions to manage resources.
     *
     * @param $user
     * @return bool
     */
    protected function userCanManageResources($user): bool
    {
        return $user->can('Manage Job Orders') ||
               $user->can('Manage Venue Bookings') ||
               $user->can('Manage Sticker Applications') ||
               $user->can('Manage Users');
    }
}
