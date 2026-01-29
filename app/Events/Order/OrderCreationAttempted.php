<?php

declare(strict_types=1);

namespace App\Events\Order;

use App\Contracts\LoggableEvent;
use App\DTO\PurchaseDTO;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreationAttempted implements LoggableEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public PurchaseDTO $purchaseDTO) {}

    public function getPerformedOn(): ?Model
    {
        return $this->purchaseDTO->buyer;
    }

    public function getCausedBy(): ?User
    {
        return $this->purchaseDTO->buyer;
    }

    public function getDescription(): string
    {
        return 'Attempting to create order';
    }

    public function getProperties(): array
    {
        return ['cart' => $this->purchaseDTO->cart->toArray()];
    }
}
