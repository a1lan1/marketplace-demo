<?php

declare(strict_types=1);

namespace App\DTO\Geo;

use App\Enums\Geo\ReviewSourceEnum;
use App\Enums\SentimentEnum;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class ReviewFilterData extends Data
{
    public function __construct(
        public ?int $locationId = null,
        public ?ReviewSourceEnum $source = null,
        public ?SentimentEnum $sentiment = null,
    ) {}
}
