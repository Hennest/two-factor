<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Contracts;

use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use Psr\SimpleCache\InvalidArgumentException;

interface TwoFactorManagerInterface
{
    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function enableTwoFactor(TwoFactorAuthenticatable $user, bool $force = false): void;

    public function disableTwoFactor(TwoFactorAuthenticatable $user): void;

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws InvalidArgumentException
     * @throws SecretKeyTooShortException
     */
    public function confirmTwoFactor(TwoFactorAuthenticatable $user, string $code): bool;

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     * @throws InvalidArgumentException
     */
    public function validateSecretKey(TwoFactorAuthenticatable $user, string $twoFactorCode): bool;

    public function validateRecoveryCode(TwoFactorAuthenticatable $user, string|null $recoveryCode = null): false|string;
}
