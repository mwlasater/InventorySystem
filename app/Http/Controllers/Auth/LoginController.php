<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Max login attempts per IP+username before throttling, and the decay window.
     */
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // IP+username throttle, complementary to the per-account lockout below:
        // this blunts distributed guessing and username enumeration that a
        // single-account lockout can't see.
        $this->ensureIsNotRateLimited($request);

        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if ($user && $user->isLocked()) {
            return back()->withErrors([
                'username' => 'Account is locked. Try again after '.$user->locked_until->diffForHumans(),
            ])->withInput($request->only('username'));
        }

        if ($user && ! $user->is_active) {
            return back()->withErrors([
                'username' => 'Your account has been deactivated.',
            ])->withInput($request->only('username'));
        }

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password], $request->boolean('remember'))
            || Auth::attempt(['email' => $request->username, 'password' => $request->password], $request->boolean('remember'))) {

            RateLimiter::clear($this->throttleKey($request));
            $authUser = Auth::user();

            // Password is correct, but 2FA users must pass the second factor
            // before they're actually authenticated. Stash the pending user and
            // drop the auth session until the challenge succeeds.
            if ($authUser->hasTwoFactorEnabled()) {
                Auth::logout();
                $request->session()->put('auth.2fa.user_id', $authUser->id);
                $request->session()->put('auth.2fa.remember', $request->boolean('remember'));

                return redirect()->route('two-factor.challenge');
            }

            $request->session()->regenerate();
            $authUser->resetFailedAttempts();
            $authUser->update(['last_login_at' => now()]);

            AuditLog::record('login', 'users', $authUser->id);

            if ($authUser->force_password_change) {
                return redirect()->route('password.force-change');
            }

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($this->throttleKey($request), self::DECAY_SECONDS);

        if ($user) {
            $user->incrementFailedAttempts();
            AuditLog::record('failed_login', 'users', $user->id);
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('username'));
    }

    /**
     * Throttle key is per-username + per-IP so one attacker can't lock out an
     * entire IP's worth of legitimate users, and so guessing many usernames
     * from one host is still rate-limited.
     */
    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('username')).'|'.$request->ip());
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), self::MAX_ATTEMPTS)) {
            return;
        }

        if ($user = User::where('username', $request->username)->orWhere('email', $request->username)->first()) {
            AuditLog::record('login_throttled', 'users', $user->id);
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'username' => "Too many login attempts. Please try again in {$seconds} ".str('second')->plural($seconds).'.',
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::record('logout', 'users', Auth::id());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
