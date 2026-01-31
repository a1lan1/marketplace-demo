<?php

declare(strict_types=1);

namespace App\Notifications\Order;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public User $customer
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->loadMissing(['payment', 'transaction', 'products.seller']);

        return (new MailMessage)
            ->subject('Order Confirmation #'.$this->order->id)
            ->markdown('emails.order.confirmation', [
                'order' => $this->order,
                'customer' => $this->customer,
            ]);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'order_id' => $this->order->id,
            'total_amount' => $this->order->total_amount->format(),
        ]);
    }

    public function broadcastAs(): string
    {
        return 'order.confirmation';
    }
}
