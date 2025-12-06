<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Chat\SendMessageAction;
use App\Contracts\ChatServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Models\Order;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly SendMessageAction $sendMessageAction,
        private readonly ChatServiceInterface $chatService,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function getMessages(Order $order): JsonResponse
    {
        $this->authorize('viewChat', $order);

        $messages = $this->chatService->getPaginatedMessages($order);

        return response()->json($messages);
    }

    /**
     * @throws AuthorizationException
     */
    public function store(SendMessageRequest $request, Order $order): JsonResponse
    {
        $this->authorize('sendMessage', $order);

        $this->sendMessageAction->execute(
            order: $order,
            sender: $request->user(),
            messageContent: $request->validated('message')
        );

        return response()->json(['success' => true], 201);
    }
}
