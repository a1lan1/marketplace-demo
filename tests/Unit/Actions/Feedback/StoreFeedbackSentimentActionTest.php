<?php

use App\Actions\Feedback\StoreFeedbackSentimentAction;
use App\Contracts\Repositories\FeedbackRepositoryInterface;
use App\Enums\SentimentEnum;
use App\Events\FeedbackSaved;
use App\Models\Feedback;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;

it('updates feedback sentiment and dispatches event', function (): void {
    Event::fake();

    $feedback = Feedback::factory()->create(['sentiment' => null]);

    $feedbackRepositoryMock = $this->mock(FeedbackRepositoryInterface::class, function (MockInterface $mock) use ($feedback): void {
        $mock->shouldReceive('updateSentiment')
            ->once()
            ->with($feedback->id, SentimentEnum::POSITIVE)
            ->andReturnUsing(function (int $id, SentimentEnum $sentiment) use ($feedback) {
                $feedback->sentiment = $sentiment; // Manually update the object for the test

                return $feedback;
            });
    });

    $action = new StoreFeedbackSentimentAction($feedbackRepositoryMock);

    $action->execute($feedback->id, SentimentEnum::POSITIVE);

    expect($feedback->sentiment)->toBe(SentimentEnum::POSITIVE);
    Event::assertDispatched(FeedbackSaved::class, function ($event) use ($feedback): bool {
        return $event->feedback->id === $feedback->id;
    });
});
