<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\FeedbackData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'feedbackable_type' => ['required', 'string', Rule::in(['product', 'seller'])],
            'feedbackable_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function toDto(): FeedbackData
    {
        return FeedbackData::from($this->validated());
    }
}
