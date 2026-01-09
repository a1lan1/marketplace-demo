<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\MessageRepositoryInterface;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class MessageRepository implements MessageRepositoryInterface
{
    public function getForOrder(Order $order): Collection
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

    public function getPaginatedForOrder(Order $order, int $perPage = 50): LengthAwarePaginator
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

    public function createForOrder(Order $order, User $sender, string $content): Message
    {
        return Message::create([
            'order_id' => $order->id,
            'user_id' => $sender->id,
            'message' => $content,
        ]);
    }
}
