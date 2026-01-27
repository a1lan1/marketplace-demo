<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use Spatie\LaravelData\Data;

class GatewaySetupIntentResultDTO extends Data
{
    public function __construct(
        public string $clientSecret,
        public string $id,
    ) {}
}
