<?php

declare(strict_types=1);

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Order Status Has Been Updated')
            ->greeting('Hello, '.$this->order->buyer->name.'!')
            ->line('The status of your order #'.$this->order->id.' has been updated to: '.$this->order->status->value)
            ->action('View Order', route('orders.show', $this->order))
            ->line('Thank you for shopping with us!');
    }
}
