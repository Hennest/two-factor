<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Traits;

use Hennest\QrCode\Configuration\Dimension;
use Hennest\QrCode\Exceptions\InvalidMarginException;
use Hennest\QrCode\Exceptions\InvalidSizeException;
use Hennest\QrCode\Services\QRCodeInterface;
use Hennest\TwoFactor\Services\RecoveryCode;
use Hennest\TwoFactor\Services\TwoFactor;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Random\RandomException;

/**
 * @mixin Authenticatable|Model
 *
 * @property-read string|null $two_factor_secret
 * @property-read string|null $two_factor_recovery_codes
 * @property-read string|null $two_factor_confirmed_at
 */
trait HasTwoFactorAuthentication
{
    public function getAuthIdentifierUsernameName(): string
    {
        return 'email';
    }

    public function getAuthIdentifierUsername(): string
    {
        return $this->{$this->getAuthIdentifierUsernameName()};
    }

    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return null !== $this->two_factor_secret && null !== $this->two_factor_confirmed_at;
    }

    public function twoFactorSecret(): string|null
    {
        return $this->two_factor_secret ? decrypt($this->two_factor_secret) : null;
    }

    public function twoFactorRecoveryCodes(): string|null
    {
        return $this->two_factor_recovery_codes ? decrypt($this->two_factor_recovery_codes) : null;
    }

    /**
     * @throws RandomException
     */
    public function replaceRecoveryCode(string $code): void
    {
        if (null === $this->twoFactorRecoveryCodes()) {
            return;
        }

        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(str_replace(
                search: $code,
                replace: app(RecoveryCode::class)->generate(),
                subject: $this->twoFactorRecoveryCodes()
            )),
        ])->save();
    }

    /**
     * @throws InvalidSizeException
     * @throws InvalidMarginException
     */
    public function twoFactorQrCode(): string|null
    {
        if (null === $this->twoFactorQrCodeUri()) {
            return null;
        }

        return app(QRCodeInterface::class)->generate(
            content: $this->twoFactorQrCodeUri(),
            dimension: new Dimension(
                size: 192,
            )
        )->toSvg();
    }

    public function twoFactorQrCodeUri(): string|null
    {
        if (null === $this->twoFactorSecret()) {
            return null;
        }

        return app(TwoFactor::class)->qrCodeUrl(
            companyName: parse_url(config('app.url'), PHP_URL_HOST),
            companyEmail: $this->getAuthIdentifierUsername(),
            secret: $this->twoFactorSecret()
        );
    }
}
