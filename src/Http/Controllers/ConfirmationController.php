<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Http\Controllers;

use Hennest\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Hennest\TwoFactor\Contracts\TwoFactorManagerInterface;
use Hennest\TwoFactor\Http\Requests\ConfirmationRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use Psr\SimpleCache\InvalidArgumentException;

final readonly class ConfirmationController
{
    public function __construct(
        private TwoFactorManagerInterface $twoFactorService,
    ) {
    }

    public function create(Request $request): View|RedirectResponse
    {
        /** @var TwoFactorAuthenticatable $user */
        $user = $request->user();

        if ( ! $user->twoFactorSecret()) {
            return redirect()->route('two-factor::activation.create');
        }

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('two-factor::activation.show');
        }

        /** @var string $qrCode */
        $qrCode = $user->twoFactorQrCode();

        return view('two-factor::confirmation.create', [
            'secretKey' => $user->twoFactorSecret(),
            'qrCode' => new HtmlString($qrCode),
        ]);
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     * @throws InvalidArgumentException
     */
    public function store(ConfirmationRequest $request): RedirectResponse
    {
        /** @var TwoFactorAuthenticatable $user */
        $user = $request->user();

        if ( ! $this->twoFactorService->confirmTwoFactor($user, $request->payload()->code)) {
            throw ValidationException::withMessages([
                'code' => trans('two-factor::messages.authentication.failed'),
            ])->errorBag('confirmTwoFactorAuthentication');
        }

        return redirect()
            ->route('two-factor::activation.show')
            ->with('status', trans('two-factor::messages.activation.enabled'));
    }
}
