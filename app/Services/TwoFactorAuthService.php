<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Thin wrapper around pragmarx/google2fa for TOTP enrollment, verification, and
 * recovery codes. The secret and recovery codes are persisted encrypted via the
 * User model's casts; this service never logs or returns them in cleartext
 * except where the caller needs them (QR provisioning, one-time code display).
 */
class TwoFactorAuthService
{
    private const RECOVERY_CODE_COUNT = 8;

    public function __construct(private Google2FA $google2fa) {}

    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * @return array<int, string>
     */
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, self::RECOVERY_CODE_COUNT))
            ->map(fn () => Str::upper(Str::random(5).'-'.Str::random(5)))
            ->all();
    }

    /**
     * otpauth:// provisioning URI encoded as an inline SVG QR code, ready to
     * drop into a Blade view.
     */
    public function qrCodeSvg(User $user, string $secret): string
    {
        $uri = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email ?: $user->username,
            $secret
        );

        return QrCode::format('svg')->size(200)->margin(1)->generate($uri);
    }

    public function verify(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Returns true and consumes the matching recovery code (removing it from the
     * user's stored set) when $code matches one. Case/format tolerant.
     */
    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $code = Str::upper(trim($code));
        $codes = $user->two_factor_recovery_codes ?? [];

        if (! in_array($code, $codes, true)) {
            return false;
        }

        $user->forceFill([
            'two_factor_recovery_codes' => array_values(array_diff($codes, [$code])),
        ])->save();

        return true;
    }
}
