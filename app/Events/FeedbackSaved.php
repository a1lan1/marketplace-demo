<?php

declare(strict_types=1);

namespace App\Events;

use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeedbackSaved implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Feedback $feedback) {}

    public function broadcastOn(): array
    {
        $type = $this->feedback->getFeedbackableSlug();

        return [
            new Channel(sprintf('feedbacks.%s.%d', $type, $this->feedback->feedbackable_id)),
        ];
    }

    public function broadcastWith(): array
    {
        $this->feedback->loadAuthorDetails();

        return [
            'feedback' => new FeedbackResource($this->feedback),
        ];
    }
}
