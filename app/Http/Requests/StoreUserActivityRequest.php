<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserActivityType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'page' => ['required', 'string', 'max:2048'],
            'event_type' => ['required', Rule::enum(UserActivityType::class)],
            'props' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
