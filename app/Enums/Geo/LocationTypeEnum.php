<?php

declare(strict_types=1);

namespace App\Enums\Geo;

enum LocationTypeEnum: string
{
    case STORE = 'store';
    case PICKUP_POINT = 'pickup_point';
    case WAREHOUSE = 'warehouse';
    case OFFICE = 'office';
}
