<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use Spatie\LaravelData\Data;

class GatewayChargeResultDTO extends Data
{
    public function __construct(
        public string $transactionId,
        public string $status,
        public int $amount,
        public string $currency,
        public ?string $clientSecret = null,
    ) {}
}
