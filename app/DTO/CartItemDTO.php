<?php

declare(strict_types=1);

namespace App\DTO;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class CartItemDTO extends Data
{
    public function __construct(
        #[MapName('product_id')]
        public int $productId,
        public int $quantity,
    ) {}
}
