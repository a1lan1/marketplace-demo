<?php

declare(strict_types=1);

namespace App\DTO;

readonly class SalesStatsDTO
{
    public function __construct(
        public int $count,
        public int $totalCents,
        public string $currency = 'USD'
    ) {}
}
