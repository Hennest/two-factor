<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Providers;

use Hennest\TwoFactor\Contracts\RecoveryCodeInterface;
use Hennest\TwoFactor\Contracts\TwoFactorAuthenticatorInterface;
use Hennest\TwoFactor\Contracts\TwoFactorManagerInterface;
use Hennest\TwoFactor\Services\RecoveryCode;
use Hennest\TwoFactor\Services\TwoFactorAuthenticator;
use Hennest\TwoFactor\Services\TwoFactorManager;
use Illuminate\Support\ServiceProvider;

final class TwoFactorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/two-factor.php',
            'two-factor'
        );

        /** @var array{
         *     auth: array{
         *         window: int,
         *         forbid_old_otp: bool,
         *     },
         *     recovery_codes: array{number_of_codes: int}|null,
         * } $config
         */
        $config = config('two-factor');

        $this->app->singleton(
            TwoFactorManagerInterface::class,
            TwoFactorManager::class
        );

        $this->app->singleton(
            TwoFactorAuthenticatorInterface::class,
            TwoFactorAuthenticator::class
        );

        $this->app->when(TwoFactorAuthenticator::class)
            ->needs('$window')
            ->give($config['auth']['window'] ?? 0);

        $this->app->when(TwoFactorAuthenticator::class)
            ->needs('$forbidOldOtp')
            ->give($config['auth']['forbid_old_otp'] ?? false);

        $this->app->singleton(
            RecoveryCodeInterface::class,
            RecoveryCode::class
        );

        $this->app->when(RecoveryCode::class)
            ->needs('$numCodes')
            ->give($config['recovery_codes']['number_of_codes'] ?? 8);
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
