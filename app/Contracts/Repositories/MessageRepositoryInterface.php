<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface MessageRepositoryInterface
{
    public function getForOrder(Order $order): Collection;

    public function getPaginatedForOrder(Order $order, int $perPage = 50): LengthAwarePaginator;

    public function createForOrder(Order $order, User $sender, string $content): Message;
}
