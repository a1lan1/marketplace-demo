<?php

declare(strict_types=1);

namespace App\DTO\Geo;

use App\Enums\Geo\ReviewSourceEnum;
use App\Enums\SentimentEnum;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ReviewData extends Data
{
    public function __construct(
        public ?int $locationId,
        public ReviewSourceEnum $source,
        public string $externalId,
        public string $authorName,
        public ?string $text,
        public int $rating,
        public ?SentimentEnum $sentiment,
        public string $publishedAt,
    ) {}
}
