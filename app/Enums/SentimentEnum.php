<?php

declare(strict_types=1);

namespace App\Enums;

enum SentimentEnum: string
{
    case POSITIVE = 'positive';
    case NEUTRAL = 'neutral';
    case NEGATIVE = 'negative';
}
