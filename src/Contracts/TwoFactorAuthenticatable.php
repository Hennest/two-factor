<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Contracts;

interface TwoFactorAuthenticatable
{
    public function getAuthIdentifierUsernameName(): string;

    public function getAuthIdentifierUsername(): string;

    public function hasEnabledTwoFactorAuthentication(): bool;

    public function twoFactorSecret(): string|null;

    public function twoFactorRecoveryCodes(): string|null;

    public function replaceRecoveryCode(string $code): void;

    public function twoFactorQrCode(): string|null;

    public function twoFactorQrCodeUri(): string|null;
}
