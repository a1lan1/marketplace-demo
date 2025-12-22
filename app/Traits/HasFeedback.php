<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Feedback;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFeedback
{
    public function feedbacks(): MorphMany
    {
        return $this->morphMany(Feedback::class, 'feedbackable');
    }

    public function averageRating(): float
    {
        return (float) $this->feedbacks()->avg('rating');
    }

    public function reviewsCount(): int
    {
        return $this->feedbacks()->count();
    }
}
