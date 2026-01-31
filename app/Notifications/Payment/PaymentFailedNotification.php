<?php

declare(strict_types=1);

namespace App\Notifications\Payment;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Payment $payment,
        public string $errorMessage
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
        return (new MailMessage)
            ->subject('Payment Failed for Order #'.$this->payment->order_id)
            ->error()
            ->greeting('Hello, '.$notifiable->name.'!')
            ->line('We are sorry to inform you that the payment for your order #'.$this->payment->order_id.' has failed.')
            ->line('Reason: '.$this->errorMessage)
            ->line('Please try again or contact our support if the problem persists.')
            ->action('View Order', route('orders.show', $this->payment->order_id));
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'order_id' => $this->payment->order_id,
            'error_message' => $this->errorMessage,
        ]);
    }

    public function broadcastAs(): string
    {
        return 'payment.failed';
    }
}
