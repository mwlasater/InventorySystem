<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Second step of login for users with 2FA enabled. The first step
 * (LoginController) verified the password and stashed the pending user id in the
 * session WITHOUT authenticating; this controller completes login only after a
 * valid TOTP code or recovery code.
 */
class TwoFactorChallengeController extends Controller
{
    public function __construct(private TwoFactorAuthService $twoFactor) {}

    public function show(Request $request)
    {
        if (! $request->session()->has('auth.2fa.user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request)
    {
        $userId = $request->session()->get('auth.2fa.user_id');

        if (! $userId || ! ($user = User::find($userId))) {
            return redirect()->route('login')->withErrors([
                'code' => 'Your login session expired. Please sign in again.',
            ]);
        }

        $request->validate([
            'code' => 'required_without:recovery_code|nullable|string',
            'recovery_code' => 'required_without:code|nullable|string',
        ]);

        $passed = $request->filled('recovery_code')
            ? $this->twoFactor->consumeRecoveryCode($user, $request->recovery_code)
            : $this->twoFactor->verify($user->two_factor_secret, (string) $request->code);

        if (! $passed) {
            AuditLog::record('two_factor_failed', 'users', $user->id);

            throw ValidationException::withMessages([
                'code' => 'The provided two-factor code was invalid.',
            ]);
        }

        return $this->completeLogin($request, $user);
    }

    private function completeLogin(Request $request, User $user)
    {
        $remember = (bool) $request->session()->pull('auth.2fa.remember', false);
        $request->session()->forget('auth.2fa.user_id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->resetFailedAttempts();
        $user->update(['last_login_at' => now()]);
        AuditLog::record('login', 'users', $user->id);

        if ($user->force_password_change) {
            return redirect()->route('password.force-change');
        }

        return redirect()->intended(route('dashboard'));
    }
}
