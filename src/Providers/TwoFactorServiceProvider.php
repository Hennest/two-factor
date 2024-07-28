<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Providers;

use Hennest\TwoFactor\Contracts\RecoveryCodeInterface;
use Hennest\TwoFactor\Contracts\TwoFactorInterface;
use Hennest\TwoFactor\Contracts\TwoFactorServiceInterface;
use Hennest\TwoFactor\Services\RecoveryCode;
use Hennest\TwoFactor\Services\TwoFactor;
use Hennest\TwoFactor\Services\TwoFactorService;
use Illuminate\Support\ServiceProvider;

final class TwoFactorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/two-factor.php',
            'two-factor'
        );

        $this->app->singleton(
            TwoFactorServiceInterface::class,
            TwoFactorService::class
        );

        $this->app->singleton(
            TwoFactorInterface::class,
            TwoFactor::class
        );

        $this->app->singleton(
            RecoveryCodeInterface::class,
            RecoveryCode::class
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'two-factor');
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'two-factor');

        $this->publishes([
            __DIR__ . '/../../config/two-factor.php' => config_path('two-factor.php'),
        ], 'two-factor-config');

        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'two-factor-migrations');

        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/two-factor'),
        ], 'two-factor-views');

        $this->publishes([
            __DIR__ . '/../../lang' => lang_path('vendor/two-factor'),
        ], 'two-factor-lang');
    }
}
