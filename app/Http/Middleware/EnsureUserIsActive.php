<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deactivating a user has to take effect immediately, not at their next login
 * — an admin who removes someone expects them out of the system now, even if
 * that person has a live session or a "remember me" cookie.
 *
 * So every authenticated request re-checks the flag and logs the session out
 * the moment the account is switched off.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user !== null && ! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'That account has been deactivated. Please contact an administrator.',
            ]);
        }

        return $next($request);
    }
}
