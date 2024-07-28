<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Tests\database\factories;

use Hennest\TwoFactor\Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

final class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->email(),
            'password' => self::$password ??= Hash::make('password'),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    public function withTwoFactorSecret(string|null $twoFactorSecret = null): self
    {
        return $this->state(fn (): array => [
            'two_factor_secret' => encrypt($twoFactorSecret ?? 'secret'),
        ]);
    }

    public function withTwoFactorRecoveryCodes(array|null $twoFactorRecoveryCodes = []): self
    {
        return $this->state(fn (): array => [
            'two_factor_recovery_codes' => encrypt(json_encode($twoFactorRecoveryCodes ?: [
                'one',
                'two',
                'three',
                'four',
                'five',
            ])),
        ]);
    }

    public function withTwoFactorConfirmedAt(Carbon|null $twoFactorRecoveryCodes = null): self
    {
        return $this->state(fn (): array => [
            'two_factor_confirmed_at' => $twoFactorRecoveryCodes ?? Carbon::now(),
        ]);
    }
}
