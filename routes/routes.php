<?php

declare(strict_types=1);

use Hennest\TwoFactor\Http\Controllers\ActivationController;
use Hennest\TwoFactor\Http\Controllers\AuthenticationController;
use Hennest\TwoFactor\Http\Controllers\ConfirmationController;
use Illuminate\Support\Facades\Route;

/** @var array{
 *     guest: array{
 *         middleware: string|null,
 *         guard: string|null,
 *     },
 *     auth: array{
 *          middleware: string|null,
 *          guard: string|null,
 *     },
 * } $twoFactor
 */
$twoFactor = config('two-factor');

// TODO: Refactor route name to be two-factor::authentications.create
Route::middleware([
    'web',
    sprintf(
        "%s:%s",
        $twoFactor['guest']['middleware'] ?? 'guest',
        $twoFactor['guest']['guard'] ?? 'web'
    ),
])->group(function () use ($twoFactor): void {
    Route::get('two-factor/auth', [AuthenticationController::class, 'create'])->name('two-factor-authentication::create');

    /** @var array{
     *      auth: array{
     *          throttle: array{
     *              attempts: int|null,
     *              decay: int|null,
     *          },
     *      },
     * } $twoFactor
     */
    Route::post('two-factor/auth', [AuthenticationController::class, 'store'])
        ->middleware([
            sprintf(
                "throttle:%s,%s",
                $twoFactor['auth']['throttle']['attempts'] ?? 6,
                $twoFactor['auth']['throttle']['decay'] ?? 1
            ),
        ])
        ->name('two-factor-authentication::store');
});

Route::middleware([
    'web',
    sprintf(
        "%s:%s",
        $twoFactor['auth']['middleware'] ?? 'auth',
        $twoFactor['auth']['guard'] ?? 'web'
    ),
])->group(function (): void {
    Route::get('/two-factor/create', [ActivationController::class, 'create'])->name('two-factor-activation::create');
    Route::get('/two-factor/show', [ActivationController::class, 'show'])->name('two-factor-activation::show');
    Route::post('two-factor', [ActivationController::class, 'store'])->name('two-factor-activation::store');
    Route::delete('two-factor', [ActivationController::class, 'destroy'])->name('two-factor-activation::destroy');

    Route::get('two-factor/confirm/create', [ConfirmationController::class, 'create'])->name('two-factor-confirmation::create');
    Route::post('two-factor/confirm', [ConfirmationController::class, 'store'])->name('two-factor-confirmation::store');
});
