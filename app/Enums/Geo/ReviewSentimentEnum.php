<?php

declare(strict_types=1);

namespace App\Enums\Geo;

enum ReviewSentimentEnum: string
{
    case POSITIVE = 'positive';
    case NEUTRAL = 'neutral';
    case NEGATIVE = 'negative';
}
