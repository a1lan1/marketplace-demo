<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Feedback;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NegativeFeedbackReceived extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Feedback $feedback) {}

    public function envelope(): Envelope
    {
        /** @var Product|User $feedbackable */
        $feedbackable = $this->feedback->feedbackable;

        return new Envelope(
            subject: 'New Negative Feedback Received for '.$feedbackable->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.feedback.negative-feedback-received',
            with: [
                'feedback' => $this->feedback,
                'item' => $this->feedback->feedbackable,
                'recipient' => $this->feedback->getRecipient(),
            ],
        );
    }
}
