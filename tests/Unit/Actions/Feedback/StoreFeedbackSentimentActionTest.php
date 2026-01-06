<?php

use App\Actions\Feedback\StoreFeedbackSentimentAction;
use App\Enums\SentimentEnum;
use App\Events\FeedbackSaved;
use App\Models\Feedback;
use Illuminate\Support\Facades\Event;

it('updates feedback sentiment and dispatches event', function (): void {
    Event::fake();

    $feedback = Feedback::factory()->create(['sentiment' => null]);
    $action = new StoreFeedbackSentimentAction;

    $action->execute($feedback->id, SentimentEnum::POSITIVE);

    $feedback->refresh();

    expect($feedback->sentiment)->toBe(SentimentEnum::POSITIVE);
    Event::assertDispatched(FeedbackSaved::class, function ($event) use ($feedback): bool {
        return $event->feedback->id === $feedback->id;
    });
});
