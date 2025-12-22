<?php

declare(strict_types=1);

namespace App\DTO;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class FeedbackData extends Data
{
    public function __construct(
        public string $feedbackableType,
        public int $feedbackableId,
        public int $rating,
        public ?string $comment,
    ) {}
}
