<?php

namespace App\Http\Controllers;

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
            // If successful, regenerate session for security
            $request->session()->regenerate();

            return redirect()->intended('/admin/parents-students')
                ->with('status', 'You are now logged in!');
        }

        // If login fails, redirect back with an error
        return back()->withErrors([
            'email' => 'Invalid credentials provided.',
        ])->onlyInput('email');
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
