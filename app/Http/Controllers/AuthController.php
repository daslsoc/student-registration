<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class AuthController
 *
 * Handles login (show form + process credentials) and logout.
 * Uses the default 'users' table & session-based auth.
 */
class AuthController extends Controller
{
    /**
     * Show the login form.
     *
     * @return View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Process the login submission.
     *
     * @return RedirectResponse
     */
    public function login(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Attempt login
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            // Credentials are right, but the account may have been switched
            // off. Check after the attempt rather than folding it into the
            // credentials so we can say WHY they can't get in — they already
            // know their own password, so this gives nothing away.
            if (! Auth::user()->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'That account has been deactivated. Please contact an administrator.',
                ])->onlyInput('email');
            }

            // If successful, regenerate session for security
            $request->session()->regenerate();

            return redirect()->intended($this->landingPath(Auth::user()))
                ->with('status', 'You are now logged in!');
        }

        // If login fails, redirect back with an error
        return back()->withErrors([
            'email' => 'Invalid credentials provided.',
        ])->onlyInput('email');
    }

    /**
     * Where to drop someone after login.
     *
     * Roles mean not everyone can see the parents & students list any more, so
     * landing there unconditionally would greet some users with a 403. Walk the
     * admin pages in rough order of usefulness and send them to the first one
     * their role allows; the help page needs no permission, so there is always
     * an answer.
     */
    private function landingPath(User $user): string
    {
        $pages = [
            'view_registrations' => 'admin.parent_student_list',
            'view_unallocated' => 'admin.unallocated',
            'view_allergies' => 'admin.allergies',
            'manage_payment_overrides' => 'admin.payment_override',
            'manage_users' => 'admin.users.index',
            'manage_roles' => 'admin.roles.index',
            'view_audit_log' => 'admin.audit',
        ];

        foreach ($pages as $atom => $route) {
            if ($user->hasPermission($atom)) {
                return route($route);
            }
        }

        return route('admin.help');
    }

    /**
     * Logs the user out and invalidates the session.
     *
     * @return RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidate the session & CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'You have been logged out.');
    }
}
