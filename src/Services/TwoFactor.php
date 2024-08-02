<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Services;

use Hennest\TwoFactor\Contracts\TwoFactorInterface;
use Illuminate\Contracts\Cache\Repository;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;
use Psr\SimpleCache\InvalidArgumentException;

final readonly class TwoFactor implements TwoFactorInterface
{
    private const FORBID_OLD_OTP = 0;

    public function __construct(
        private Google2FA $engine,
        private Repository $cache,
    ) {
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function generateSecretKey(): string
    {
        return $this->engine->generateSecretKey();
    }

    public function qrCodeUrl(string $companyName, string $companyEmail, string $secret): string
    {
        return $this->engine->getQRCodeUrl(
            company: $companyName,
            holder: $companyEmail,
            secret: $secret
        );
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     * @throws InvalidArgumentException
     */
    public function verify(string $secret, string $oneTimeCode, int|null $oldTimeStamp = null): bool
    {
        $window = config('two-factor.auth.forbid_old_otp')
            ? self::FORBID_OLD_OTP
            : config('two-factor.auth.window');

        if (is_int($window)) {
            $this->engine->setWindow($window);
        }

        $timestamp = $this->engine->verifyKey(
            secret: $secret,
            key: $oneTimeCode,
            oldTimestamp: $oldTimeStamp ?? $this->cache->get(
                key: 'two-factor::' . md5($oneTimeCode)
            )
        );

        if ( ! $timestamp) {
            return false;
        }

        $this->cache->put(
            key: 'two-factor::' . md5($oneTimeCode),
            value: $this->engine->getTimestamp(),
            ttl: ($this->engine->getWindow() ?: 1) * 60
        );

        return true;
    }
}
