<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Http\Middleware;

use Closure;
use Hennest\TwoFactor\Contracts\TwoFactorAuthenticatable;
use Hennest\TwoFactor\Traits\HasTwoFactorAuthentication;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class RedirectIfTwoFactorAuthenticatable
{
    public function handle(Request $request, Closure $next): mixed
    {
        /** @var Authenticatable&HasTwoFactorAuthentication $user */
        $user = $this->user()::query()
            ->where('email', $request->string('email')->value())
            ->first();

        if ( ! Auth::guard()->validate([
            'email' => $request->string('email')->value(),
            'password' => $request->string('password')->value(),
        ])) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if ($user instanceof TwoFactorAuthenticatable && $user->hasEnabledTwoFactorAuthentication()) {
            $request->session()->put([
                '2fa:user:id' => $user->getKey(),
                '2fa:user:remember' => $request->boolean('remember'),
            ]);

            return redirect()->route('two-factor-authentication::create');
        }

        return $next($request);
    }

    private function user(): TwoFactorAuthenticatable
    {
        return app(config('two-factor.auth.model'));
    }
}
