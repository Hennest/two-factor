<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Services;

use Hennest\TwoFactor\Contracts\RecoveryCodeInterface;
use Random\RandomException;

final readonly class RecoveryCode implements RecoveryCodeInterface
{
    private const NUMBER_OF_TIMES_TO_GENERATE = 8;

    public function __construct(
        private int|null $numCodes
    ) {
    }

    /**
     * @throws RandomException
     */
    public function generate(): string
    {
        return implode('-', [
            $this->generateSegment(),
            $this->generateSegment(),
            $this->generateSegment(),
            $this->generateSegment(),
        ]);
    }

    /**
     * @return array<array-key, string>
     * @throws RandomException
     */
    public function generateCodes(int|null $numCodes = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $this->numCodes ?? $numCodes; $i++) {
            $codes[] = $this->generate();
        }

        return $codes;
    }

    /**
     * @param array<array-key, string> $recoveryCodes
     */
    public function validate(array $recoveryCodes, string $code): false|string
    {
        foreach ($recoveryCodes as $recoveryCode) {
            if (hash_equals($recoveryCode, $code)) {
                return $code;
            }
        }

        return false;
    }

    /**
     * @throws RandomException
     */
    private function generateSegment(): string
    {
        return str_pad(
            string: (string) random_int(0, 999999),
            length: 6,
            pad_string: '0',
            pad_type: STR_PAD_LEFT
        );
    }
}
