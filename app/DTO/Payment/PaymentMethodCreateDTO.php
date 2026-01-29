<?php

declare(strict_types=1);

namespace App\DTO\Payment;

use App\Enums\Payment\PaymentProviderEnum;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class PaymentMethodCreateDTO extends Data
{
    public function __construct(
        public int $userId,
        public string $type,
        public PaymentProviderEnum $provider,
        public string $providerId,
        public ?string $lastFour = null,
        public ?string $brand = null,
        public ?CarbonImmutable $expiresAt = null,
        public bool $isDefault = false,
    ) {}
}
