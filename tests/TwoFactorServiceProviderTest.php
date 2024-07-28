<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Tests;

use Hennest\TwoFactor\Providers\TwoFactorServiceProvider;
use Orchestra\Testbench\TestCase;

final class TwoFactorServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            TwoFactorServiceProvider::class,
        ];
    }
}
