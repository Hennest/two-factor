<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Http\Controllers;

use Hennest\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Hennest\TwoFactor\Contracts\TwoFactorServiceInterface;
use Hennest\TwoFactor\Http\Requests\LoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use Psr\SimpleCache\InvalidArgumentException;

final readonly class AuthenticationController
{
    public function __construct(
        private TwoFactorServiceInterface $twoFactorService,
    ) {
    }

    public function create(Request $request): View|RedirectResponse
    {
        $user = $this->user()::query()->find(
            id: $request->session()->get('2fa:user:id')
        );

        if ( ! $user instanceof TwoFactorAuthenticatable) {
            return redirect()->route('login');
        }

        return view('two-factor::authentication.login');
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     * @throws InvalidArgumentException
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $user = $this->user()::query()->find($request->payload()->userId);

        if ( ! $user instanceof TwoFactorAuthenticatable || ! $user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('login');
        }

        if ($this->twoFactorService->validateRecoveryCode($user, $recoveryCode = $request->payload()->recoveryCode)) {
            $user->replaceRecoveryCode($recoveryCode);
        } elseif ( ! $this->twoFactorService->validateSecretKey($user, $request->payload()->twoFactorCode)) {
            throw ValidationException::withMessages([
                'code' => trans('two-factor::messages.authentication.failed'),
            ]);
        }

        Auth::guard(config('two-factor.auth.guard'))->login(
            user: $user,
            remember: $request->payload()->remember
        );

        $request->session()->forget(['2fa:user:id', '2fa:auth:remember']);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function user(): Model
    {
        return app(config('two-factor.auth.model'));
    }
}
