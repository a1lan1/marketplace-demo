<?php

declare(strict_types=1);

namespace App\DTO\Geo;

use App\Enums\Geo\LocationTypeEnum;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class LocationData extends Data
{
    /**
     * @param  array<string, mixed>|null  $externalIds
     */
    public function __construct(
        public ?int $id,
        public int $sellerId,
        public string $name,
        public LocationTypeEnum $type,
        public AddressData $address,
        public float $latitude,
        public float $longitude,
        public ?array $externalIds = null,
    ) {}
}
