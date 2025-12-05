<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class PurchaseRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'cart.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function messages(): array
    {
        return [
            'cart.required' => 'The cart cannot be empty.',
            'cart.array' => 'The cart must be an array.',
            'cart.min' => 'The cart must contain at least one item.',
            'cart.*.product_id.required' => 'Each item in the cart must have a product ID.',
            'cart.*.product_id.integer' => 'The product ID must be an integer.',
            'cart.*.product_id.exists' => 'One or more products in the cart do not exist.',
            'cart.*.quantity.required' => 'Each item in the cart must have a quantity.',
            'cart.*.quantity.integer' => 'The quantity must be an integer.',
            'cart.*.quantity.min' => 'The quantity for each product must be at least 1.',
        ];
    }
}
