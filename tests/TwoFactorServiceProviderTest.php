<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Tests;

use Hennest\QRCode\Providers\QRCodeServiceProvider;
use Hennest\TwoFactor\Providers\TwoFactorServiceProvider;
use Hennest\TwoFactor\Tests\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;

use function Orchestra\Testbench\package_path;

abstract class TwoFactorServiceProviderTest extends TestCase
{
    use WithWorkbench;

    /**
     * @param Application $app
     * @return array<int, class-string<ServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        $app['config']->set('two-factor.auth.model', User::class);

        return [
            QRCodeServiceProvider::class,
            TwoFactorServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(
            package_path('tests/database/migrations'),
        );
    }
}
