<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Payment\PaymentProviderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:100'], // Minimum 1.00$
            'currency' => ['required', 'string', 'size:3'],
            'payment_method_id' => ['required', 'string'],
            'provider' => ['required', Rule::enum(PaymentProviderEnum::class)],
            'save_card' => ['boolean'],
        ];
    }
}
