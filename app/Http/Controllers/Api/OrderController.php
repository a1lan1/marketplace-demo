<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\OrderServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly OrderServiceInterface $orderService,
    ) {}

    public function getUserOrders(): JsonResponse
    {
        $orders = $this->orderService->getOrdersForUser(Auth::user());

        return response()->json($orders);
    }
}
