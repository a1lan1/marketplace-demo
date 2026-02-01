<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin Transaction
 */
class TransactionResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount->getAmount(),
            'formatted_amount' => $this->amount->format(),
            'currency' => $this->amount->getCurrency()->getCode(),
            'type' => $this->type->value,
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
