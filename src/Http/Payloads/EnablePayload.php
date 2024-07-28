<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Http\Payloads;

final class EnablePayload
{
    public function __construct(
        public bool $force = false,
    ) {
    }
}
