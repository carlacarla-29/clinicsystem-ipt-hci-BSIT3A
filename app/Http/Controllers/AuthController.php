<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * ============================================================
 *  AuthController
 * ============================================================
 *
 * GUIDE: What does this controller do?
 * --------------------------------------
 * Handles all authentication logic for the Clinic System:
 *   1. showLogin()  → Renders the login page (GET /login)
 *   2. login()      → Validates credentials and starts a session (POST /login)
 *   3. logout()     → Destroys the session and redirects to login (POST /logout)
 *
 * HOW LARAVEL AUTH WORKS (simple flow):
 *   User submits email + password
 *     → Auth::attempt() checks the `users` table
 *     → If match: session is created, user is "logged in"
 *     → If no match: redirect back with an error
 *
 * SECURITY NOTES:
 *   - Passwords are NEVER stored in plain text. Laravel hashes them with bcrypt.
 *   - Auth::attempt() automatically compares the plain password against the hash.
 *   - Session regeneration after login prevents session fixation attacks.
 *   - Logout uses POST (not GET) to require a CSRF token, preventing logout CSRF attacks.
 * ============================================================
 */
class AuthController extends Controller
{
    /**
     * Show the login form.
     *
     * This method only runs if the user is NOT logged in.
     * (Protected by the 'guest' middleware in web.php)
     *
     * @return View
     */
    public function showLogin(): View
    {
        // Renders: resources/views/auth/login.blade.php
        return view('auth.login');
    }

    /**
     * Handle the login form submission.
     *
     * STEP-BY-STEP:
     *   1. Validate the incoming request fields.
     *   2. Attempt authentication with the provided credentials.
     *   3. On success: regenerate session and redirect to dashboard.
     *   4. On failure: redirect back with a generic error message.
     *      (Generic message is intentional — don't tell attackers which field is wrong.)
     *
     * @param  Request $request  The incoming HTTP POST request from the login form.
     * @return RedirectResponse
     */
    public function login(Request $request): RedirectResponse
    {
        // Step 1: Validate form inputs before doing anything else.
        // 'email' must be a valid email format.
        // 'password' must be present (we don't check length here — that's registration's job).
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Step 2: Attempt to log the user in.
        // Auth::attempt() does three things automatically:
        //   a) Queries: SELECT * FROM users WHERE email = ?
        //   b) Compares: bcrypt(input_password) === stored_hashed_password
        //   c) If match: stores user ID in the session
        //
        // The second argument (true) enables "remember me" — keeps the user
        // logged in across browser restarts using a long-lived cookie.
        // For the clinic system we default to true (clinic computers stay logged in).
        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            // Step 3a: Regenerate the session ID to prevent session fixation.
            // This is a security best practice — always do this after login.
            $request->session()->regenerate();

            // Redirect to whatever page they were trying to access before login,
            // or fall back to the dashboard if they came directly to /login.
            return redirect()->intended(route('dashboard'));
        }

        // Step 3b: Authentication failed.
        // withErrors() flashes an error to the session, available in Blade as:
        //   @error('email') {{ $message }} @enderror
        // withInput() re-populates the form so the user doesn't retype their email.
        return back()
            ->withErrors(['email' => 'The provided credentials do not match our records.'])
            ->withInput($request->only('email', 'remember'));
    }

    /**
     * Log the user out.
     *
     * STEP-BY-STEP:
     *   1. Call Auth::logout() — clears the authenticated user from the session.
     *   2. Invalidate the session — destroys all session data.
     *   3. Regenerate the CSRF token — prevents the old token from being reused.
     *   4. Redirect to the login page.
     *
     * WHY POST? — Logout must be a POST request (not a simple link/GET)
     * because GET requests don't carry a CSRF token. Without this, any
     * malicious website could embed <img src="/logout"> and log your users out.
     *
     * In your Blade layout, use:
     *   <form method="POST" action="{{ route('logout') }}">
     *       @csrf
     *       <button type="submit">Logout</button>
     *   </form>
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        // Step 1: Remove the authenticated user from the current session.
        Auth::logout();

        // Step 2: Destroy all session data (cart, flash messages, everything).
        $request->session()->invalidate();

        // Step 3: Generate a new CSRF token for the now-anonymous session.
        $request->session()->regenerateToken();

        // Step 4: Send the user to the login page.
        return redirect()->route('login');
    }
}
