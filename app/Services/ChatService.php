<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ChatServiceInterface;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class ChatService implements ChatServiceInterface
{
    public function getOrderMessages(Order $order): Collection
    {
        return $order->messages()
            ->select(['id', 'order_id', 'user_id', 'message', 'created_at'])
            ->with([
                'user' => function (Relation $query): void {
                    $query->select('id', 'name')->with('media');
                },
            ])
            ->get();
    }

    public function getPaginatedMessages(Order $order, int $perPage = 50): LengthAwarePaginator
    {
        return $order->messages()
            ->select(['id', 'order_id', 'user_id', 'message', 'created_at'])
            ->with([
                'user' => function (Relation $query): void {
                    $query->select('id', 'name')->with('media');
                },
            ])
            ->latest()
            ->paginate($perPage);
    }
}
