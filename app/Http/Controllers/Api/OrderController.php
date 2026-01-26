<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\OrderServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(private readonly OrderServiceInterface $orderService) {}

    public function getUserOrders(Request $request): AnonymousResourceCollection
    {
        $orders = $this->orderService->getOrdersForUser($request->user());

        return OrderResource::collection($orders);
    }
}
