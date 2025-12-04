<?php

declare(strict_types=1);

namespace App\Actions\Chat;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;

class SendMessageAction
{
    public function execute(Order $order, User $sender, string $messageContent): Message
    {
        /** @var Message $message */
        $message = $order->messages()->create([
            'user_id' => $sender->id,
            'message' => $messageContent,
        ]);

        event(new MessageSent($message->load('user')));

        return $message;
    }
}
