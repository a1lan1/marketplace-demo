<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ChatServiceInterface;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ChatService implements ChatServiceInterface
{
    public function getOrderMessages(Order $order): Collection
    {
        return $order->messages()
            ->with('user')
            ->get();
    }

    public function getPaginatedMessages(Order $order, int $perPage = 50): LengthAwarePaginator
    {
        return $order->messages()
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }
}
