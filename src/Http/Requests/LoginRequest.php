<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Http\Requests;

use Hennest\TwoFactor\Http\Payloads\LoginPayload;
use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'recovery_code' => ['nullable', 'string'],
            'code' => ['nullable', 'string'],
        ];
    }

    public function remember(): bool|null
    {
        return once(fn () => $this->session()->pull('login.remember', false));
    }

    public function userId(): null|string
    {
        return once(fn () => $this->session()->get('2fa:user:id'));
    }

    public function payload(): LoginPayload
    {
        return new LoginPayload(
            twoFactorCode: $this->string('code')->value(),
            userId: $this->userId(),
            remember: $this->remember(),
            recoveryCode: $this->string('recovery_code')->value(),
        );
    }
}
