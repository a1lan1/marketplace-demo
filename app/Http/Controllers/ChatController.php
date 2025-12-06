<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\OrderServiceInterface;
use App\Models\Order;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly OrderServiceInterface $orderService,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Order::class);

        $orders = $this->orderService->getOrdersForUser(Auth::user());

        return Inertia::render('Chat', [
            'orders' => $orders,
        ]);
    }
}
