<?php

declare(strict_types=1);

use Hennest\TwoFactor\Tests\Models\User;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\from;

test('users can authenticate using one the correct two factor recovery code', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret('JBSWY3DPEHPK3PXP')
        ->withTwoFactorRecoveryCodes([
            'one',
            'two',
            'three',
            'four',
            'five',
            'six',
        ])
        ->create();

    Session::put('2fa:user:id', $user->id);

    $response = from(route('login'))->post(route('two-factor-authentication::store', [
        'recovery_code' => 'one',
    ]));

    $response
        ->assertSessionHasNoErrors()
        ->assertSessionMissing('2fa:user:id');

    assertAuthenticated();

    $response->assertRedirect(route('dashboard'));
});

test('users can not authenticate using the wrong two factor recovery code', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret('JBSWY3DPEHPK3PXP')
        ->withTwoFactorRecoveryCodes([
            'one',
            'two',
            'three',
            'four',
            'five',
            'six',
        ])
        ->create();

    Session::put('2fa:user:id', $user->id);

    $response = from(route('login'))->post(route('two-factor-authentication::store', [
        'recovery_code' => 'wrong-recovery-code',
    ]));

    $response
        ->assertSessionHasErrors(['code'])
        ->assertSessionHas('2fa:user:id');

    assertGuest();

    $response->assertRedirect(route('login'));
});
