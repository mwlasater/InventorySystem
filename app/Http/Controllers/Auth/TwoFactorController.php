<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function __construct(private TwoFactorAuthService $twoFactor) {}

    /**
     * Management page: enrollment, confirmation, recovery codes, or disable,
     * depending on the user's current 2FA state.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $qrSvg = null;

        // Mid-enrollment (secret set but not yet confirmed): render the QR so the
        // user can scan it and confirm a code.
        if ($user->two_factor_secret && ! $user->two_factor_confirmed_at) {
            $qrSvg = $this->twoFactor->qrCodeSvg($user, $user->two_factor_secret);
        }

        return view('auth.two-factor', [
            'user' => $user,
            'qrSvg' => $qrSvg,
            'recoveryCodes' => $user->two_factor_secret ? $user->two_factor_recovery_codes : null,
        ]);
    }

    /**
     * Begin enrollment: generate a secret + recovery codes (unconfirmed).
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        $user->forceFill([
            'two_factor_secret' => $this->twoFactor->generateSecretKey(),
            'two_factor_recovery_codes' => $this->twoFactor->generateRecoveryCodes(),
            'two_factor_confirmed_at' => null,
        ])->save();

        return redirect()->route('two-factor.show')
            ->with('status', 'Scan the QR code with your authenticator app, then confirm a code below.');
    }

    /**
     * Finish enrollment by verifying a code against the pending secret.
     */
    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $user = $request->user();

        if (! $user->two_factor_secret || $user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.show');
        }

        if (! $this->twoFactor->verify($user->two_factor_secret, $request->code)) {
            throw ValidationException::withMessages([
                'code' => 'That code is invalid. Make sure your device clock is correct and try again.',
            ]);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();
        AuditLog::record('two_factor_enabled', 'users', $user->id);

        return redirect()->route('two-factor.show')
            ->with('status', 'Two-factor authentication is now enabled. Save your recovery codes somewhere safe.');
    }

    /**
     * Disable 2FA. Requires the current password to prevent a walk-up takeover
     * of an unlocked session.
     */
    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|string']);
        $user = $request->user();

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'The password is incorrect.',
            ]);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
        AuditLog::record('two_factor_disabled', 'users', $user->id);

        return redirect()->route('two-factor.show')
            ->with('status', 'Two-factor authentication has been disabled.');
    }

    /**
     * Generate a fresh set of recovery codes (only once 2FA is fully enabled).
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.show');
        }

        $user->forceFill([
            'two_factor_recovery_codes' => $this->twoFactor->generateRecoveryCodes(),
        ])->save();

        return redirect()->route('two-factor.show')
            ->with('status', 'New recovery codes generated. Your old codes no longer work.');
    }
}
