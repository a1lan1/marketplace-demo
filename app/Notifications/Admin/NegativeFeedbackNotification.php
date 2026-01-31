<?php

declare(strict_types=1);

namespace App\Notifications\Admin;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NegativeFeedbackNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Feedback $feedback) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->feedback->loadMissing('author');

        return (new MailMessage)
            ->subject('Negative Feedback Detected')
            ->error()
            ->greeting('Alert!')
            ->line('A negative feedback has been detected and requires your attention.')
            ->line('Author: '.$this->feedback->author->name)
            ->line('Rating: '.$this->feedback->rating.' stars')
            ->line('Comment: "'.$this->feedback->comment.'"')
            ->action('View Feedback', route('geo.dashboard'));
    }
}
