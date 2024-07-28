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
        ->toContain('one');
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
