<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Contracts;

use Random\RandomException;

interface RecoveryCodeInterface
{
    /**
     * @throws RandomException
     */
    public function generate(): string;

    /**
     * @return array<array-key, string>
     * @throws RandomException
     */
    public function generateCodes(): array;

    /**
     * @param array<array-key, string> $recoveryCodes
     */
    public function validate(array $recoveryCodes, string $code): false|string;
}
