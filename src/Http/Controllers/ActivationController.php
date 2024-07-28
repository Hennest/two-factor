<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Http\Controllers;

use Hennest\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Hennest\TwoFactor\Contracts\TwoFactorServiceInterface;
use Hennest\TwoFactor\Http\Requests\EnableRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;

final readonly class ActivationController
{
    public function __construct(
        private TwoFactorServiceInterface $twoFactorService,
    ) {
    }

    public function create(Request $request): View|RedirectResponse
    {
        /** @var TwoFactorAuthenticatable $user */
        $user = $request->user();

        if ($user->twoFactorSecret()) {
            return redirect()->route('two-factor-confirmation::create');
        }

        return view('two-factor::activation.create');
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function store(EnableRequest $request): RedirectResponse
    {
        $this->twoFactorService->enableTwoFactor(
            user: $request->user()
        );

        return redirect()
            ->route('two-factor-confirmation::create')
            ->with('status', trans('two-factor::messages.activation.secret-generated'));
    }

    public function show(Request $request): View|RedirectResponse
    {
        /** @var TwoFactorAuthenticatable $user */
        $user = $request->user();

        if ( ! $user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('two-factor-confirmation::create');
        }

        return view('two-factor::activation.show');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('twoFactorDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $this->twoFactorService->disableTwoFactor(
            user: $request->user()
        );

        return redirect()
            ->route('two-factor-activation::create')
            ->with('status', trans('two-factor::messages.activation.disabled'));
    }
}
