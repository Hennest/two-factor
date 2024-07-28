<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Contracts;

use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use Psr\SimpleCache\InvalidArgumentException;

interface TwoFactorInterface
{
    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function generateSecretKey(): string;

    public function qrCodeUrl(string $companyName, string $companyEmail, string $secret): string;

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     * @throws InvalidArgumentException
     */
    public function verify(string $secret, string $oneTimeCode, int|null $oldTimeStamp = null): bool;
}
