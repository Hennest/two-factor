<?php

declare(strict_types=1);

use Hennest\TwoFactor\Tests\Models\User;

use function Pest\Laravel\actingAs;

test('two factor confirmation can be rendered', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret()
        ->create();

    $response = actingAs($user)->get(
        route('two-factor::confirmation.create')
    );

    $response
        ->assertViewIs('two-factor::confirmation.create')
        ->assertViewHas([
            'secretKey',
            'qrCode',
        ])
        ->assertOk();
});

test('users are redirected to two factor activation create page if two_factor_secret key is not set', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)->get(
        route('two-factor::confirmation.create')
    );

    $response->assertRedirect(route('two-factor::activation.create'));
});

test('users are redirected to two factor activation show page if two factor authentication is enabled', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret()
        ->withTwoFactorConfirmedAt()
        ->create();

    $response = actingAs($user)->get(
        route('two-factor::confirmation.create')
    );

    $response->assertRedirect(route('two-factor::activation.show'));
});

test('users can confirm two factor authentication', function (): void {
    $tfa = app(PragmaRX\Google2FAQRCode\Google2FA::class);

    $twoFactorSecret = $tfa->generateSecretKey();
    $validOtp = $tfa->getCurrentOtp($twoFactorSecret);
    $user = User::factory()
        ->withTwoFactorSecret($twoFactorSecret)
        ->create();

    $response = actingAs($user)
        ->from(route('two-factor::confirmation.create'))
        ->post(route('two-factor::confirmation.store'), [
            'code' => $validOtp,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('two-factor::activation.show'))
        ->assertSessionHas('status', 'Two-factor authentication is enabled.');
});

test('users cannot confirm two factor authentication with invalid code', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret('JBSWY3DPEHPK3PXP')
        ->create();

    $response = actingAs($user)
        ->from(route('two-factor::confirmation.create'))
        ->post(route('two-factor::confirmation.store'), [
            'code' => 'invalid-otp',
        ]);

    $response
        ->assertRedirect(route('two-factor::confirmation.create'))
        ->assertSessionHasErrors('code', errorBag: 'confirmTwoFactorAuthentication');
});

test('two factor confirmation is ignored when two factor authentication is disabled', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->from(route('two-factor::confirmation.create'))
        ->post(route('two-factor::confirmation.store'), [
            'code' => '123456',
        ]);

    $response
        ->assertRedirect(route('two-factor::confirmation.create'))
        ->assertSessionHasErrors('code', errorBag: 'confirmTwoFactorAuthentication');
});
