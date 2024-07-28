<?php

declare(strict_types=1);

namespace Hennest\TwoFactor\Http\Requests;

use Hennest\TwoFactor\Http\Payloads\EnablePayload;
use Illuminate\Foundation\Http\FormRequest;

final class EnableRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'force' => ['nullable', 'boolean'],
        ];
    }

    public function payload(): EnablePayload
    {
        return new EnablePayload(
            force: $this->boolean('force'),
        );
    }
}
