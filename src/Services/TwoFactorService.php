<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Services;

use Hennest\TwoFactor\Contracts\RecoveryCodeInterface;
use Hennest\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Hennest\TwoFactor\Contracts\TwoFactorInterface;
use Hennest\TwoFactor\Contracts\TwoFactorServiceInterface;
use Illuminate\Database\Eloquent\Model;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;

final readonly class TwoFactorService implements TwoFactorServiceInterface
{
    public function __construct(
        private TwoFactorInterface $twoFactor,
        private RecoveryCodeInterface $recoveryCode,
    ) {
    }

    /**
     * @param TwoFactorAuthenticatable&Model $user
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws RandomException
     * @throws SecretKeyTooShortException
     */
    public function enableTwoFactor(TwoFactorAuthenticatable $user, bool $force = false): void
    {
        if ($user->hasEnabledTwoFactorAuthentication() && false === $force) {
            return;
        }

        $user->forceFill([
            'two_factor_secret' => encrypt($this->twoFactor->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode($this->recoveryCode->generateCodes())),
        ])->save();
    }

    /**
     * @param TwoFactorAuthenticatable&Model $user
     */
    public function disableTwoFactor(TwoFactorAuthenticatable $user): void
    {
        if ( ! $user->hasEnabledTwoFactorAuthentication()) {
            return;
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    /**
     * @param TwoFactorAuthenticatable&Model $user
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws InvalidArgumentException
     * @throws SecretKeyTooShortException
     */
    public function confirmTwoFactor(TwoFactorAuthenticatable $user, string $code): bool
    {
        if ( ! $user->twoFactorSecret()) {
            return false;
        }

        if ( ! $this->validateSecretKey($user, $code)) {
            return false;
        }

        return $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     * @throws InvalidArgumentException
     */
    public function validateSecretKey(TwoFactorAuthenticatable $user, string $twoFactorCode): bool
    {
        if ( ! $user->twoFactorSecret()) {
            return false;
        }

        return $this->twoFactor->verify(
            secret: $user->twoFactorSecret(),
            oneTimeCode: $twoFactorCode
        );
    }

    public function validateRecoveryCode(TwoFactorAuthenticatable $user, string|null $recoveryCode = null): false|string
    {
        if ( ! $recoveryCode || ! $user->twoFactorRecoveryCodes()) {
            return false;
        }

        return $this->recoveryCode->validate(
            recoveryCodes: json_decode($user->twoFactorRecoveryCodes()),
            code: $recoveryCode
        );
    }
}
