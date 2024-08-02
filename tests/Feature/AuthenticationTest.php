<?php

declare(strict_types=1);

use Hennest\TwoFactor\Tests\Models\User;
use Illuminate\Support\Facades\Session;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\from;
use function Pest\Laravel\get;

test('two factor login page is rendered', function (): void {
    $user = User::factory()->create();

    Session::put('2fa:user:id', $user->id);

    $response = get(
        route('two-factor::authentication.create')
    );

    assertGuest();

    $response->assertRedirect();
});

test('two factor login page is not rendered if session does not have valid key', function (): void {
    $response = from(route('login'))->get(
        route('two-factor::authentication.create')
    );

    expect(Session::get('2fa:user:id'))->toBeNull();

    assertGuest();

    $response->assertRedirect(route('login'));
});

test('users can authenticate using the correct two factor authentication code', function (): void {
    $twoFactorEngine = app(PragmaRX\Google2FAQRCode\Google2FA::class);

    $twoFactorSecret = $twoFactorEngine->generateSecretKey();
    $validOtp = $twoFactorEngine->getCurrentOtp($twoFactorSecret);

    $user = User::factory()
        ->withTwoFactorSecret($twoFactorSecret)
        ->withTwoFactorRecoveryCodes()
        ->create();

    Session::put('2fa:user:id', $user->id);

    $response = from(route('two-factor::authentication.create'))->post(route('two-factor::authentication.store', [
        'code' => $validOtp,
    ]));

    $response->assertSessionHasNoErrors();

    assertAuthenticated();

    expect(Session::get('2fa:user:id'))->toBeNull();

    $response->assertRedirect(route('dashboard'));
});

test('users cannot authenticate using wrong two factor authentication code', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret('JBSWY3DPEHPK3PXP')
        ->create();

    Session::put('2fa:user:id', $user->id);

    $response = from(route('login'))->post(route('two-factor::authentication.store', [
        'code' => 'invalid-otp',
    ]));

    $response->assertSessionHasErrors(['code']);

    $response->assertRedirect(route('login'));
});

test('two factor authentication fails for old otp', function (): void {
    app('config')->set('two-factor.auth.forbid_old_otp', true);

    $twoFactorEngine = app(PragmaRX\Google2FAQRCode\Google2FA::class);

    $twoFactorSecret = $twoFactorEngine->generateSecretKey();
    $currentTs = $twoFactorEngine->getTimestamp();
    $previousOtp = $twoFactorEngine->oathTotp($twoFactorSecret, $currentTs - 1);

    $user = User::factory()
        ->withTwoFactorSecret($twoFactorSecret)
        ->withTwoFactorConfirmedAt()
        ->create();

    Session::put('2fa:user:id', $user->id);

    $response = from(route('two-factor::authentication.create'))->post(route('two-factor::authentication.store', [
        'code' => $previousOtp,
    ]));

    $response
        ->assertSessionHasErrors(['code'])
        ->assertSessionHas('2fa:user:id', $user->id)
        ->assertRedirect(route('two-factor::authentication.create'));
});

test('two factor authentication fails for old otp regardless of what is set for window', function (): void {
    app('config')->set('two-factor.auth.window', 1);
    app('config')->set('two-factor.auth.forbid_old_otp', true);

    $twoFactorEngine = app(PragmaRX\Google2FAQRCode\Google2FA::class);

    $twoFactorSecret = $twoFactorEngine->generateSecretKey();
    $currentTs = $twoFactorEngine->getTimestamp();
    $previousOtp = $twoFactorEngine->oathTotp($twoFactorSecret, $currentTs - 1);

    $user = User::factory()
        ->withTwoFactorSecret($twoFactorSecret)
        ->withTwoFactorConfirmedAt()
        ->create();

    Session::put('2fa:user:id', $user->id);

    $response = from(route('two-factor::authentication.create'))->post(route('two-factor::authentication.store', [
        'code' => $previousOtp,
    ]));

    $response
        ->assertSessionHasErrors(['code'])
        ->assertSessionHas('2fa:user:id', $user->id)
        ->assertRedirect(route('two-factor::authentication.create'));
});

test('two factor authentication fails for zero window', function (): void {
    app('config')->set('two-factor.auth.window', 0);

    $twoFactorEngine = app(PragmaRX\Google2FAQRCode\Google2FA::class);

    $twoFactorSecret = $twoFactorEngine->generateSecretKey();
    $currentTs = $twoFactorEngine->getTimestamp();
    $previousOtp = $twoFactorEngine->oathTotp($twoFactorSecret, $currentTs - 1);

    $user = User::factory()
        ->withTwoFactorSecret($twoFactorSecret)
        ->withTwoFactorConfirmedAt()
        ->create();

    Session::put('2fa:user:id', $user->id);

    $response = from(route('two-factor::authentication.create'))->post(route('two-factor::authentication.store', [
        'code' => $previousOtp,
    ]));

    $response
        ->assertSessionHasErrors(['code'])
        ->assertSessionHas('2fa:user:id', $user->id)
        ->assertRedirect(route('two-factor::authentication.create'));
});

test('ensure two factor authentication attempts are throttled', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret('JBSWY3DPEHPK3PXP')
        ->create();

    Session::put('2fa:user:id', $user->id);

    for ($i = 0; $i < 7; $i++) {
        $response = from(route('two-factor::authentication.create'))->post(route('two-factor::authentication.store'), [
            'code' => 'invalid-otp',
        ]);
    }

    $response
        ->assertTooManyRequests()
        ->assertSessionHas('2fa:user:id', $user->id);
});
