<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderServiceInterface
{
    public function getUserOrders(User $user, int $perPage = 10): LengthAwarePaginator;

    public function getOrdersForUser(User $user, int $perPage = 10): LengthAwarePaginator;
}
