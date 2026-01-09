<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ChatServiceInterface;
use App\Contracts\Repositories\MessageRepositoryInterface;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ChatService implements ChatServiceInterface
{
    public function __construct(protected MessageRepositoryInterface $messageRepository) {}

    public function getOrderMessages(Order $order): Collection
    {
        return $this->messageRepository->getForOrder($order);
    }

    public function getPaginatedMessages(Order $order, int $perPage = 50): LengthAwarePaginator
    {
        return $this->messageRepository->getPaginatedForOrder($order, $perPage);
    }
}
