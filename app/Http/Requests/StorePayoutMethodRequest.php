<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Payment\PaymentProviderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePayoutMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', Rule::enum(PaymentProviderEnum::class)],
            'token' => ['required', 'string'], // Stripe token
            'type' => ['required', 'string', Rule::in(['bank_account', 'card'])],
        ];
    }
}
