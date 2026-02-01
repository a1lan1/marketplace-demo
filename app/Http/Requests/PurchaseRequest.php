<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Payment\PaymentTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cart' => ['required', 'array'],
            'cart.*.product_id' => ['required', 'exists:products,id'],
            'cart.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_type' => ['required', new Enum(PaymentTypeEnum::class)],
            'payment_method_id' => ['nullable', 'string', 'required_if:payment_type,card'],
            'payment_provider' => ['nullable', 'string'],
            'save_card' => ['nullable', 'boolean'],
        ];
    }
}
