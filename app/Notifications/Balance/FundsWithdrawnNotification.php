<?php

declare(strict_types=1);

namespace App\Notifications\Balance;

use Cknow\Money\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FundsWithdrawnNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Money $amount,
        public string $payoutId
    ) {}

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
            ->subject('Your Withdrawal Request is Being Processed')
            ->greeting('Hello, '.$notifiable->name.'!')
            ->line('Your request to withdraw '.$this->amount->format().' has been received and is now being processed.')
            ->line('You will receive another notification once the funds have been sent.')
            ->line('Payout ID: '.$this->payoutId);
    }
}
