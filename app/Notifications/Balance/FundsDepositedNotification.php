<?php

declare(strict_types=1);

namespace App\Notifications\Balance;

use Cknow\Money\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FundsDepositedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Money $amount) {}

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
            ->subject('Your Balance Has Been Topped Up')
            ->greeting('Hello, '.$notifiable->name.'!')
            ->line('Your balance has been successfully topped up by '.$this->amount->format().'.')
            ->line('Thank you for using our platform!');
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'amount' => $this->amount->format(),
            'message' => 'Your balance has been successfully topped up by '.$this->amount->format().'.',
        ]);
    }

    public function broadcastAs(): string
    {
        return 'balance.deposited';
    }
}
