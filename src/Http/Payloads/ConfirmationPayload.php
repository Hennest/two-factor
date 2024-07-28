<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Http\Payloads;

final class ConfirmationPayload
{
    public function __construct(
        public string $code,
    ) {
    }
}
