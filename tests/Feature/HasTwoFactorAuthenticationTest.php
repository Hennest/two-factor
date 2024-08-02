<?php

declare(strict_types=1);

use Hennest\TwoFactor\Tests\Models\User;

test('two factor authentication secret key can be retrieved', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret('secret')
        ->create();

    expect($user->twoFactorSecret())->toBe('secret');
});

test('two factor authentication recovery codes can be retrieved', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret('secret')
        ->withTwoFactorRecoveryCodes()
        ->create();

    expect(json_decode($user->twoFactorRecoveryCodes()))
        ->toBeArray()
        ->toHaveCount(5)
        ->toBe([
            'one',
            'two',
            'three',
            'four',
            'five',
        ]);
});

test('two factor authentication recovery codes can be replaced', function (): void {
    $user = User::factory()
        ->withTwoFactorRecoveryCodes()
        ->create();

    $user->replaceRecoveryCode('one');

    expect($user->fresh()->twoFactorRecoveryCodes())->not->toContain('one');
});

test('replacing two_factor_recovery_codes is ignored when not set', function (): void {
    $user = User::factory()->create();

    $user->replaceRecoveryCode('one');

    expect($user->fresh()->twoFactorRecoveryCodes())->toBeNull();
});

test('two factor qr code can be generated', function (): void {
    $user = User::factory()
        ->withTwoFactorSecret("two-factor-secret")
        ->create();

    expect($user->twoFactorQrCode())->toContain(
        'svg',
        'width="192"',
        'height="192"',
        'transform="translate(4,4)"'
    );
});

test('generating two factor qr code returns null when two_factor_secret is not set', function (): void {
    $user = User::factory()->create();

    expect($user->twoFactorQrCode())->toBeNull();
});

test('two factor authentication qr code uri can be retrieved', function (): void {
    $secret = "two-factor-secret";

    $user = User::factory()
        ->withTwoFactorSecret($secret)
        ->create();

    $appName = parse_url(config('app.url'), PHP_URL_HOST);

    expect($user->twoFactorQrCodeUri())->toBe(sprintf(
        "otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30",
        $appName,
        urlencode($user->email),
        $secret,
        $appName
    ));
});

test('retrieving qr code uri returns null when two_factor_secret is not set', function (): void {
    $user = User::factory()->create();

    expect($user->twoFactorQrCodeUri())->toBeNull();
});
