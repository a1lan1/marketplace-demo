<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin Product
 *
 * @property-read OrderProduct|null $pivot
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price->getAmount() / 100,
            'stock' => $this->stock,
            'user_id' => $this->user_id,
            'cover_image' => $this->cover_image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'seller' => UserResource::make($this->whenLoaded('seller')),
            'quantity' => $this->when(isset($this->pivot), fn () => $this->pivot?->quantity),
            'total_price' => $this->when(isset($this->pivot), fn (): float => (float) $this->pivot?->price->getAmount() / 100),
        ];
    }
}
