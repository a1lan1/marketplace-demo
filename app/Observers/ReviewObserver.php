<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Review;
use Illuminate\Support\Facades\Cache;

class ReviewObserver
{
    public function created(Review $review): void
    {
        $this->clearCache();
    }

    public function updated(Review $review): void
    {
        $this->clearCache();
    }

    public function deleted(Review $review): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::tags(['reviews', 'locations'])->flush();
    }
}
