<?php

declare(strict_types=1);

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfigurationErrorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $errorMessage) {}

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
            ->error() // Mark as error/high priority
            ->subject('Critical: Payment Configuration Error')
            ->greeting('System Alert!')
            ->line('A critical error has occurred in the payment system configuration.')
            ->line('Error Details:')
            ->line($this->errorMessage)
            ->line('Please review the application configuration immediately.');
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => 'Critical Payment Configuration Error: ' . $this->errorMessage,
            'level' => 'error',
        ]);
    }

    public function broadcastAs(): string
    {
        return 'system.payment_error';
    }
}
