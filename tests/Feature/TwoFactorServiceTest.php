<?php

declare(strict_types=1);

use Hennest\TwoFactor\Contracts\TwoFactorManagerInterface;
use Hennest\TwoFactor\Tests\Models\User;

test('validation of secrete key is ignored when two_factor_secret is not set', function (): void {
    $user = User::factory()->create();

    $twoFactorService = app(TwoFactorManagerInterface::class);

    expect($twoFactorService->validateSecretKey($user, '123456'))->toBeFalse();
});
