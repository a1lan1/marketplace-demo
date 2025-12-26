<?php

declare(strict_types=1);

namespace App\Http\Resources\Geo;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin Review
 */
class ReviewResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'location_id' => $this->location_id,
            'source' => $this->source,
            'author_name' => $this->author_name,
            'text' => $this->text,
            'rating' => $this->rating,
            'sentiment' => $this->sentiment,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
        ];
    }
}
