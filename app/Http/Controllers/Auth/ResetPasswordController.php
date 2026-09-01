<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * "Forgot my password" — step two: the emailed link lands here.
 *
 * Password::reset does the verifying: the token has to match the hash stored
 * for that email, be inside the expiry window, and it is deleted on use, so a
 * link works exactly once.
 */
class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            // Confirmed => must match password_confirmation. PasswordRule
            // defaults come from AuthServiceProvider, so this form and the
            // admin "add user" form agree on what a good password is.
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            [
                'email' => $request->input('email'),
                'password' => $request->input('password'),
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $request->input('token'),
                // A deactivated account stays deactivated: no token of theirs
                // can be spent.
                'deactivated_at' => null,
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    // Invalidate "remember me" cookies issued before the reset —
                    // if the reason for resetting was a compromised password,
                    // an old cookie shouldn't survive it.
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                ActivityLogger::systemEvent(
                    'password.reset',
                    "Password reset completed for {$user->name}",
                    $user,
                    ['email' => $user->email],
                );
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Your password has been reset. You can now log in.');
        }

        return back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
    }
}
