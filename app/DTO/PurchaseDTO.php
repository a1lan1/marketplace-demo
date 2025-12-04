<?php

declare(strict_types=1);

namespace App\DTO;

use App\Models\User;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class PurchaseDTO extends Data
{
    public function __construct(
        public User $buyer,
        #[DataCollectionOf(CartItemDTO::class)]
        public DataCollection $cart,
    ) {}
}
