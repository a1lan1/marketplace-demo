<?php

declare(strict_types=1);

namespace App\DTO\Geo;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class AddressData extends Data
{
    public function __construct(
        public ?string $country,
        public ?string $city,
        public ?string $street,
        public ?string $houseNumber,
        public ?string $postalCode,
        public string $fullAddress,
    ) {}
}
