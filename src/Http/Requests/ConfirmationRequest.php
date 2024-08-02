<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Http\Requests;

use Hennest\TwoFactor\Http\Payloads\ConfirmationPayload;
use Illuminate\Foundation\Http\FormRequest;

final class ConfirmationRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
        ];
    }

    public function payload(): ConfirmationPayload
    {
        return new ConfirmationPayload(
            code: $this->string('code')->value(),
        );
    }
}
