<?php

declare(strict_types=1);

namespace App\DTO\Geo;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ReviewData extends Data
{
    public function __construct(
        public int $locationId,
        public string $source,
        public string $externalId,
        public string $authorName,
        public ?string $text,
        public int $rating,
        public ?string $sentiment,
        public string $publishedAt,
    ) {}
}
