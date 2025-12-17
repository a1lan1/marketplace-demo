<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin OrderProduct
 */
class OrderProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'quantity' => $this->quantity,
            'price' => $this->price->getAmount(),
        ];
    }
}
