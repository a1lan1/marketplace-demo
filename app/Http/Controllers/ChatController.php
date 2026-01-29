<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\OrderServiceInterface;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function __construct(private readonly OrderServiceInterface $orderService) {}

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Order::class);

        $orders = $this->orderService->getOrdersForUser($request->user());

        return Inertia::render('Chat', [
            'orders' => OrderResource::collection($orders),
        ]);
    }
}
