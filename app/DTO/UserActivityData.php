<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enums\UserActivityType;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class UserActivityData extends Data
{
    public function __construct(
        public readonly ?int $user_id,
        #[WithCast(EnumCast::class)]
        public readonly UserActivityType $event_type,
        public readonly string $url,
        public readonly string $ts,
        public readonly array $data,
    ) {}
}
