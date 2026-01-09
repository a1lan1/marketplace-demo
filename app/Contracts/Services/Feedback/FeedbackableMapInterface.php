<?php

declare(strict_types=1);

namespace App\Contracts\Services\Feedback;

interface FeedbackableMapInterface
{
    public function get(string $type): string;
}
