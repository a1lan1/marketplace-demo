<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:100'],
            'currency' => ['required', 'string', 'size:3'],
            'payout_method_id' => [
                'required',
                'integer',
                Rule::exists('payout_methods', 'id')->where(function ($query): void {
                    $query->where('user_id', $this->user()->id);
                }),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
