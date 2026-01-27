<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use Spatie\LaravelData\Data;

class PayoutResultDTO extends Data
{
    public function __construct(
        public string $payoutId,
        public string $status,
        public int $amount,
        public string $currency,
        public ?string $arrivalDate = null,
    ) {}
}
