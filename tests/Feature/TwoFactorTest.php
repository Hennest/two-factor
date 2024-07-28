<?php

declare(strict_types=1);

use Hennest\TwoFactor\Tests\Models\User;

use function Pest\Laravel\actingAs;

test('two factor activation can be rendered', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)->get(
        route('two-factor-activation::create')
    );

    $response
        ->assertViewIs('two-factor::activation.create')
        ->assertOk();
});

test('users are redirected to two factor confirmation page if secret key is not null', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret()
        ->create();

    $response = actingAs($user)->get(
        route('two-factor-activation::create')
    );

    $response->assertRedirect(route('two-factor-confirmation::create'));
});

test('users can enable two factor authentication', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)->post(
        route('two-factor-activation::store')
    );

    $response
        ->assertRedirect(route('two-factor-confirmation::create'))
        ->assertSessionHasNoErrors();

    $user = $user->fresh();

    expect($user->two_factor_secret)->not->toBeNull()
        ->and($user->two_factor_recovery_codes)->not->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull()
        ->and(json_decode($user->twoFactorRecoveryCodes()))->toBeArray();

    $response->assertSessionHas(
        key: 'status',
        value: 'Secret key is generated. Please scan the QR code to enable two-factor authentication.'
    );
});

test('users can enable two factor authentication when force is false', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)
        ->from(route('dashboard'))
        ->post(route('two-factor-activation::store'), [
            'force' => false,
        ]);

    $response
        ->assertRedirect(route('two-factor-confirmation::create'))
        ->assertSessionHasNoErrors();

    $oldSecret = $user->two_factor_secret;
    $user = $user->fresh();

    expect($user->two_factor_secret)->not->toBeNull()
        ->and($user->two_factor_recovery_codes)->not->toBeNull()
        ->and($oldSecret)->toBe($user->two_factor_secret)
        ->and($user->two_factor_recovery_codes)->not->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull()
        ->and(json_decode($user->twoFactorRecoveryCodes()))->toBeArray();

    $response->assertSessionHas(
        key: 'status',
        value: 'Secret key is generated. Please scan the QR code to enable two-factor authentication.'
    );
});

test('two factor authentication can be force enabled', function (): void {
    $user = User::factory()->create();

    $oldSecret = $user->two_factor_secret;

    $response = actingAs($user)->post(route('two-factor-activation::store'), [
        'force' => true,
    ]);

    $response
        ->assertRedirect(route('two-factor-confirmation::create'))
        ->assertSessionHasNoErrors();

    $user = $user->fresh();

    expect($user->two_factor_secret)->not->toBeNull()
        ->and($user->two_factor_recovery_codes)->not->toBeNull()
        ->and($oldSecret)->not->toBe($user->two_factor_secret)
        ->and($user->two_factor_recovery_codes)->not->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull()
        ->and(json_decode($user->twoFactorRecoveryCodes()))->toBeArray();

    $response->assertSessionHas(
        key: 'status',
        value: 'Secret key is generated. Please scan the QR code to enable two-factor authentication.'
    );
});

test('two factor confirmation can be rendered', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret()
        ->create();

    $response = actingAs($user)->get(
        route('two-factor-confirmation::create')
    );

    $response
        ->assertViewIs('two-factor::confirmation.create')
        ->assertViewHas([
            'secretKey',
            'qrCode',
        ])
        ->assertOk();
});

test('users are redirected to two factor activation create page if secret key is null', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)->get(
        route('two-factor-confirmation::create')
    );

    $response->assertRedirect(route('two-factor-activation::create'));
});

test('users are redirected to two factor activation show page if two factor authentication is not enabled', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret()
        ->withTwoFactorConfirmedAt()
        ->create();

    $response = actingAs($user)->get(
        route('two-factor-confirmation::create')
    );

    $response->assertRedirect(route('two-factor-activation::show'));
});

test('users can confirm two factor authentication', function (): void {
    $tfa = app(PragmaRX\Google2FAQRCode\Google2FA::class);

    $twoFactorSecret = $tfa->generateSecretKey();
    $validOtp = $tfa->getCurrentOtp($twoFactorSecret);
    $user = User::factory()
        ->withTwoFactorSecret($twoFactorSecret)
        ->create();

    $response = actingAs($user)
        ->from(route('two-factor-confirmation::create'))
        ->post(route('two-factor-confirmation::store'), [
            'code' => $validOtp,
        ]);

    $response
        ->assertRedirect(route('two-factor-activation::show'))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Two-factor authentication is enabled.');
});

test('users cannot confirm two factor authentication with invalid code', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret('JBSWY3DPEHPK3PXP')
        ->create();

    $response = actingAs($user)
        ->from(route('two-factor-confirmation::create'))
        ->post(route('two-factor-confirmation::store'), [
            'code' => 'invalid-otp',
        ]);

    $response
        ->assertRedirect(route('two-factor-confirmation::create'))
        ->assertSessionHasErrors('code', errorBag: 'confirmTwoFactorAuthentication');
});

test('two factor activation show page can be rendered', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret()
        ->withTwoFactorConfirmedAt()
        ->create();

    $response = actingAs($user)->get(
        route('two-factor-activation::show')
    );

    $response
        ->assertViewIs('two-factor::activation.show')
        ->assertOk();
});

test('two factor activation show page will not be rendered if two factor authentication is not enabled', function (): void {
    $user = User::factory()->create();

    $response = actingAs($user)->get(
        route('two-factor-activation::show')
    );

    $response->assertRedirect(route('two-factor-confirmation::create'));
});

test('users can disable two factor authentication with correct password', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret()
        ->withTwoFactorRecoveryCodes()
        ->withTwoFactorConfirmedAt()
        ->create();

    $response = actingAs($user)->delete(route('two-factor-activation::destroy'), [
        'password' => 'password',
    ]);


    $response
        ->assertRedirect(route('two-factor-activation::create'))
        ->assertSessionHasNoErrors();

    $user = $user->fresh();

    expect($user->two_factor_secret)
        ->toBeNull()
        ->and($user->two_factor_recovery_codes)
        ->toBeNull()
        ->and($user->two_factor_confirmed_at)
        ->toBeNull();

    $response->assertSessionHas(
        key: 'status',
        value: 'Two-factor authentication has been disabled.'
    );
});

test('users cannot disable two factor authentication with wrong password', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret()
        ->withTwoFactorRecoveryCodes()
        ->withTwoFactorConfirmedAt()
        ->create();

    $response = actingAs($user)
        ->from(route('two-factor-activation::create'))
        ->delete(route('two-factor-activation::destroy'), [
            'password' => 'wrong-password',
        ]);

    $response->assertRedirect(route('two-factor-activation::create'));

    $user = $user->fresh();

    expect($user->two_factor_secret)
        ->not
        ->toBeNull()
        ->and($user->two_factor_recovery_codes)
        ->not
        ->toBeNull()
        ->and($user->two_factor_confirmed_at)
        ->not
        ->toBeNull();

    $response->assertSessionHasErrors('password', errorBag: 'twoFactorDeletion');
});
