<?php

declare(strict_types=1);

namespace App\Notifications\Payment;

use Cknow\Money\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class InsufficientFundsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Money $amount) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'attempted_amount' => $this->amount->format(),
            'message' => 'Insufficient funds to complete the purchase.',
        ]);
    }

    public function broadcastAs(): string
    {
        return 'purchase.insufficient_funds';
    }
}
