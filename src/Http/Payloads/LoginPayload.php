<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Http\Payloads;

final class LoginPayload
{
    public function __construct(
        public string $twoFactorCode,
        public string|null $userId,
        public bool|null $remember = false,
        public string|null $recoveryCode = null,
    ) {
    }
}
