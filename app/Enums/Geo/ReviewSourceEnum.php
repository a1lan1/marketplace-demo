<?php

declare(strict_types=1);

namespace App\Enums\Geo;

enum ReviewSourceEnum: string
{
    case YELP = 'yelp';
    case GOOGLE = 'google';
    case AIRBNB = 'airbnb';
    case AMAZON = 'amazon';
    case BOOKING = 'booking';
    case TRUSTPILOT = 'trustpilot';
    case TRIPADVISOR = 'tripadvisor';
}
