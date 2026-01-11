<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\OrderServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly OrderServiceInterface $orderService,
    ) {}

    public function getUserOrders(): AnonymousResourceCollection
    {
        $orders = $this->orderService->getOrdersForUser(Auth::user());

        return OrderResource::collection($orders);
    }
}
