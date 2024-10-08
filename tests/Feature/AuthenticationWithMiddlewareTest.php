<?php

declare(strict_types=1);

use Hennest\TwoFactor\Tests\Models\User;

use function Pest\Laravel\from;
use function Pest\Laravel\post;

test('users are redirected to two factor authentication challenge when two factor authentication is enabled', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret()
        ->withTwoFactorConfirmedAt()
        ->create();

    $response = post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('two-factor::authentication.create'));
});

test('user is not redirected to two factor authentication challenge if two factor authentication is not enabled', function (): void {
    $user = User::factory()->create();

    $response = from(route('login'))->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertSuccessful();
});

test('authentication fails when credentials is invalid and two factor authentication is enabled', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret()
        ->withTwoFactorConfirmedAt()
        ->create();

    $response = from(route('login'))->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response
        ->assertSessionHasErrors(['email'])
        ->assertRedirect(route('login'));
});

test('two factor can preserve remember me selection', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret()
        ->withTwoFactorConfirmedAt()
        ->create();

    $response = from(route('login'))->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
        'remember' => true,
    ]);

    $response
        ->assertSessionHas('2fa:user:remember', true)
        ->assertRedirect(route('two-factor::authentication.create'));
});
