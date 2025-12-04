<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ChatServiceInterface
{
    /**
     * Get all messages for a specific order.
     */
    public function getOrderMessages(Order $order): Collection;

    /**
     * Get paginated messages for a specific order.
     */
    public function getPaginatedMessages(Order $order, int $perPage = 50): LengthAwarePaginator;
}
