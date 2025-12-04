<?php

declare(strict_types=1);

namespace App\DTO;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class CartItemDTO extends Data
{
    public function __construct(
        #[MapInputName('product_id')]
        public int $productId,
        public int $quantity,
    ) {}
}
