<?php

declare(strict_types=1);

namespace App\Http\Requests\Geo;

use App\DTO\Geo\ResponseTemplateData;
use Illuminate\Foundation\Http\FormRequest;

class ResponseTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }

    public function toDto(): ResponseTemplateData
    {
        if ($template = $this->route('response_template')) {
            return ResponseTemplateData::from([
                ...$template->toArray(),
                ...$this->validated(),
                'id' => $template->id,
                'seller_id' => $template->seller_id,
            ]);
        }

        return ResponseTemplateData::from([
            ...$this->validated(),
            'seller_id' => $this->user()->id,
        ]);
    }
}
