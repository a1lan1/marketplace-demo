<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Payment\PaymentProviderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SetupIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', Rule::enum(PaymentProviderEnum::class)],
        ];
    }
}
