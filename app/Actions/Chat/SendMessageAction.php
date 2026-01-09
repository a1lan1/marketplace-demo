<?php

declare(strict_types=1);

namespace App\Actions\Chat;

use App\Contracts\Repositories\MessageRepositoryInterface;
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;

class SendMessageAction
{
    public function __construct(protected MessageRepositoryInterface $messageRepository) {}

    public function execute(Order $order, User $sender, string $messageContent): Message
    {
        $message = $this->messageRepository->createForOrder($order, $sender, $messageContent);

        event(new MessageSent($message->load('user')));

        return $message;
    }
}
