<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * "Forgot my password" — step one: email a reset link.
 *
 * Uses Laravel's password broker, so the token is single-use, hashed at rest
 * in password_reset_tokens, and expires (config/auth.php, 60 minutes).
 *
 * The response is deliberately identical whether or not the address belongs to
 * an account: this form is unauthenticated, so anything that distinguishes
 * "no such user" from "link sent" turns it into a way to test which email
 * addresses are admins here. Failures are logged instead, where only we see
 * them.
 */
class ForgotPasswordController extends Controller
{
    /** Shown whatever actually happened, for the reason above. */
    private const NEUTRAL_RESPONSE = 'If that email address belongs to an account, a password reset link is on its way. The link expires in 60 minutes.';

    public function showLinkRequestForm(): View
    {
        return view('auth.passwords.email');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // `deactivated_at => null` is part of the lookup, so a removed account
        // can't be revived by resetting its password. Laravel turns a null
        // value here into a `whereNull`.
        $status = Password::sendResetLink([
            'email' => $request->input('email'),
            'deactivated_at' => null,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            Log::info('Password reset link not sent', [
                'status' => $status,
                'email' => $request->input('email'),
            ]);
        }

        return back()->with('status', self::NEUTRAL_RESPONSE);
    }
}
