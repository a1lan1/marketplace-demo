<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use Spatie\LaravelData\Data;

class ExternalAccountDTO extends Data
{
    public function __construct(
        public string $id,
        public string $type,
        public ?string $last4 = null,
        public ?string $bankName = null,
        public ?string $brand = null,
    ) {}
}
