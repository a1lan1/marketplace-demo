<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
        ];
    }
}
