<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin User
 */
class SellerResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'created_at' => $this->created_at->format('F Y'),
            'average_rating' => round($this->averageRating(), 1),
            'reviews_count' => $this->reviewsCount(),
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
