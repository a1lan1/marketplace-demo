<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Feedback;
use Illuminate\Support\Facades\Cache;

class FeedbackObserver
{
    public function created(Feedback $feedback): void
    {
        $this->clearCache();
    }

    public function updated(Feedback $feedback): void
    {
        $this->clearCache();
    }

    public function deleted(Feedback $feedback): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::tags(['feedbacks'])->flush();
    }
}
