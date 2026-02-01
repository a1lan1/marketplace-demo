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
            'description' => $this->whenHas('description'),
            'price' => $this->price,
            'stock' => $this->whenHas('stock'),
            'user_id' => $this->whenHas('user_id'),
            'cover_image' => $this->cover_image,
            'created_at' => $this->whenHas('created_at'),
            'updated_at' => $this->whenHas('updated_at'),
            'seller' => UserResource::make($this->whenLoaded('seller')),

            // Pivot data for orders
            'quantity' => $this->when(isset($this->pivot), fn () => $this->pivot->quantity),
            'purchase_price' => $this->when(isset($this->pivot), fn () => $this->pivot->price),
            'line_total' => $this->when(isset($this->pivot), fn () => $this->pivot->line_total),
        ];
    }
}
