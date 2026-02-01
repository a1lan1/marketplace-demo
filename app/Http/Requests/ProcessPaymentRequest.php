<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Payment\PaymentProviderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:50'], // Minimum 50 cents
            'currency' => ['required', 'string', 'size:3'],
            'payment_method_id' => ['required', 'string'], // Can be existing ID or new token
            'save_card' => ['boolean'],
            'provider' => ['required', 'string', Rule::enum(PaymentProviderEnum::class)],
        ];
    }
}
