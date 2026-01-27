<?php

declare(strict_types=1);

namespace App\Events;

use App\DTO\PurchaseDTO;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreationAttempted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public PurchaseDTO $purchaseDTO) {}
}
