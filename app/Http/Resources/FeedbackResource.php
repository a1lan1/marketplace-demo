<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin Feedback
 */
class FeedbackResource extends JsonResource
{
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'author' => UserResource::make($this->whenLoaded('author')),
            'rating' => $this->rating,
            'comment' => $this->comment,
            'sentiment' => $this->sentiment,
            'feedbackable_id' => $this->feedbackable_id,
            'feedbackable_type' => $this->feedbackable_type,
            'is_verified_purchase' => $this->is_verified_purchase,
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
