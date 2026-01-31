<?php

declare(strict_types=1);

namespace App\Notifications\Seller;

use App\Models\Feedback;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFeedbackNotification extends Notification implements ShouldQueue
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
        $feedbackable = $this->feedback->feedbackable;

        if (! $feedbackable instanceof Product) {
            return (new MailMessage)->subject('New Feedback Received');
        }

        return (new MailMessage)
            ->subject('You have received new feedback!')
            ->greeting('Hello, '.$notifiable->name.'!')
            ->line('You have received a new '.$this->feedback->rating.'-star review for your product: '.$feedbackable->name)
            ->line('Review: "'.$this->feedback->comment.'"')
            ->action('View Product', route('products.show', $feedbackable));
    }
}
